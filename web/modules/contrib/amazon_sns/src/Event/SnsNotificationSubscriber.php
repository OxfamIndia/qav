<?php

namespace Drupal\amazon_sns\Event;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Drupal\user\Entity\User;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Class SnsNotificationSubscriber.
 *
 * Subscribe to SNS notification events.  These are in response to events
 * dispatched by NotificationController::receive()
 *
 * @package Drupal\amazon_sns\Event
 */
class SnsNotificationSubscriber implements ContainerInjectionInterface, EventSubscriberInterface
{

  /**
   * Logger interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    return [
      SnsEvents::NOTIFICATION => 'logNotification',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container)
  {
    /** @var \Psr\Log\LoggerInterface $logger */
    $logger = $container->get('logger.channel.amazon_sns');
    return new static(
      $logger,
      $container->get('config.factory')
    );
  }

  /**
   * SnsNotificationSubscriber constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory.
   */
  public function __construct(LoggerInterface $logger, ConfigFactoryInterface $config_factory)
  {
    $this->logger = $logger;
    $this->config = $config_factory;
  }

  /**
   * Log received notifications, if logging is turned on.
   *
   * This will log any notifications received from SNS, regardless of type.  The
   * intention here is to help with tracking down SNS problems.
   *
   * @param \Drupal\amazon_sns\Event\SnsMessageEvent $event
   *   Publish message received from Amazon SNS.
   */
  public function logNotification(SnsMessageEvent $event)
  {
    $message = $event->getMessage();
    $log_notifications = $this->config->get('amazon_sns.settings')->get('log_notifications');
    if ($log_notifications) {
      $this->logger->info('Full Message %message-id received for topic %topic.', [
      '%message-id' => $message['Message'],
      '%topic' => $message['TopicArn'],
      ]);
      $data = json_decode($message['Message'], true);
      if ($message['Type'] == 'Notification') {
        $this->logger->info('Additional Message received is %message', [
          '%message' => $data['additional_data'],
        ]);
        $order_id = isset($data['transaction_ref_number']) ? $data['transaction_ref_number'] : '';
        $tracking_id = isset($data['payment_ref_id']) ? $data['payment_ref_id'] : '';
        $bank_ref_no = isset($data['payment_ref_id']) ? $data['payment_ref_id'] : '';
        $payment_mode = isset($data['payment_gateway']) ? $data['payment_gateway'] : '';
        $card_name = isset($data['payment_mode']) ? $data['payment_mode'] : '';

        $currency = isset($data['currency']) ? $data['currency'] : '';
        $billing_name = isset($data['currency']) ? $data['currency'] : '';
        $manual_amount = isset($data['transaction_amount']) ? $data['transaction_amount'] : '';
        $manual_transaction_date = isset($data['transaction_date']) ? $data['transaction_date'] : '';
        $amount = isset($data['transaction_amount']) ? $data['transaction_amount'] : '';
        $transaction_date = isset($data['transaction_date']) ? $data['transaction_date'] : '';

        $additionalData = json_decode($data['additional_data'], true);
        foreach ($additionalData as $key => $value) {
          foreach ($value as $key2 => $value2) {
            if($key2 == 'itemmeta') {
              foreach ($value2 as $key3 => $value3) {
                if($key3 == 'Participant First Name') {
                  $firstName = $value3;
                }
                if($key3 == 'Participant Last Name') {
                  $lastName = $value3;
                }
                if($key3 == 'Mobile Number') {
                  $mobileNumber = $value3;
                }
                if($key3 == 'Participant Email') {
                  $emailAddress = $value3;
                }
                if($key3 == 'Gender') {
                  $gender = $value3;
                }
                if($key3 == 'Date of Birth') {
                  $dob = $value3;
                }
                if($key3 == 'Country') {
                  $country = $value3;
                }
                if($key3 == 'State') {
                  $state = $value3;
                }
                if($key3 == 'City') {
                  $city = $value3;
                }
                if($key3 == 'Address') {
                  $address = $value3;
                }
                if($key3 == 'Pin Code') {
                  $zip_code = $value3;
                }
                if($key3 == 'Pan Card Number (Optional)') {
                  $pan = $value3;
                }
                if($key3 == 'SELECT CHALLENGE TYPE') {
                  $challenge_type = $value3;
                }
                if($key3 == 'NOVEMBER CHALLENGE SLOT') {
                  $challenge_slot = $value3;
                }
                if($key3 == 'SELECT NATIONALITY') {
                  $nationality = $value3;
                }
              }
              $user = User::create();
              $user->setPassword("password");
              $user->enforceIsNew();
              $user->setEmail($emailAddress);
              $user->setUsername($emailAddress); // TO DO Check Username
              $user->activate();
              $user->set("field_first_name", $firstName);
              $user->set("field_last_name", $lastName);
              $user->set("field_corporate_name", 'https://www.eventjini.com?corporate=EventJini');
              $user->set("field_mobile_number", $mobileNumber);

              //$violations = $user->validate();
              if (isset($violations) && count($violations)) {
                $this->logger->info('Violations are %message', [
                  '%message' => $violations,
                ]);
              } else {
                $user->save();
                $eventjiniUser = $user->id();
                $user = User::load($eventjiniUser);
                $webformSubmissionId = $user->get('field_webform')->value;
                $webform_submission = WebformSubmission::load($webformSubmissionId);
                $data = $webform_submission->getData();
                if(isset($data['institution']) && $data['institution'] == "EventJini") {
                  $webform_submission->setElementData('verified', 'Yes');
                  $webform_submission->setElementData('mailer', '');
                  $webform_submission->setElementData('payment_status', 'Success');
                  $webform_submission->setElementData('gender', $gender);
                  $webform_submission->setElementData('date_of_birth', $dob);

                  $webform_submission->setElementData('country', $country .' '. $state);
                  $webform_submission->setElementData('city', $city);
                  $webform_submission->setElementData('address', $address);
                  $webform_submission->setElementData('zip_code', $zip_code);
                  $webform_submission->setElementData('pan_card_number', $pan);
                  $webform_submission->setElementData('challenge_type', $challenge_type);
                  if($challenge_slot == '20-29 November') {
                    $challenge_slot = 1;
                  }
                  //$webform_submission->setElementData('challenge_slot', $challenge_slot);
                  $webform_submission->set('challenge_slot', ['target_id' => $challenge_slot]);

                  $webform_submission->setElementData('nationality', $nationality);
                  $webform_submission->setElementData('order_id', $order_id);
                  $webform_submission->setElementData('tracking_id', $tracking_id);
                  $webform_submission->setElementData('bank_ref_no', $bank_ref_no);
                  $webform_submission->setElementData('payment_mode', $payment_mode);
                  $webform_submission->setElementData('card_name', $card_name);
                  $webform_submission->setElementData('currency', $currency);
                  $webform_submission->setElementData('billing_name', $billing_name);
                  $webform_submission->setElementData('manual_amount', $manual_amount);
                  $webform_submission->setElementData('manual_transaction_date', $manual_transaction_date);
                  $webform_submission->setElementData('amount', $amount);
                  $webform_submission->setElementData('transaction_date', $transaction_date);
                  $webform_submission->setElementData('terms_of_service', true);
                  $webform_submission->setElementData('term_condtions_2', true);
                  $webform_submission->setElementData('term_condtions_3', true);
                  $webform_submission->setElementData('registration_url', "https://www.eventjini.com");

                  $webform_submission->save();
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * function to send user data to SF
   */
  public function sendToSalesForce($data)
  {
    if (!empty($data)) {
      $this->logger->info('From SalesForce %message-id received for topic %topic.', [
        '%message-id' => $data['MessageId'],
        '%topic' => $data['TopicArn'],
      ]);
    }
  }

}
