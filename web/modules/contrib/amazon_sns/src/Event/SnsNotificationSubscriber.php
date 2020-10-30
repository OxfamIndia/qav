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
    //$data = json_decode($message,  true);
    if ($log_notifications) {
      //$this->logger->info('Notification %message-id received for topic %topic.', [
      //'%message-id' => $message['MessageId'],
      //'%topic' => $message['TopicArn'],
      //]);
      $data = json_decode($message['Message'], true);
      if ($message['Type'] == 'Notification') {
        $this->logger->info('Message received is %message. %message2', [
          '%message' => $data['mobile'],
          '%message2' => $data['additional_data'],
        ]);

        $additionalData = json_decode($data['additional_data'], true);
        foreach ($additionalData as $key => $value) {
//          $this->logger->info('Key received is %message.', [
//            '%message' => $key,
//          ]);
          foreach ($value as $key2 => $value2) {
//            $this->logger->info('Key2 received is %message. %message2', [
//              '%message' => $key2,
//              '%message2' => $value2,
//            ]);
            if($key2 == 'itemmeta') {
              foreach ($value2 as $key3 => $value3) {
//                $this->logger->info('Key3 received is %message. %message2', [
//                  '%message' => $key3,
//                  '%message2' => $value3,
//                ]);
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
                if($key3 == 'City') {
                  $city = $value3;
                }
                if($key3 == 'Address') {
                  $address = $value3;
                }
                if($key3 == 'Pin Code') {
                  $zip_code = $value3;
                }
                /*
                  challenge_type
                  challenge_slot
                  nationality
                  terms_of_service
                  term_condtions_2
                  term_condtions_3
                  order_id
                  tracking_id
                  bank_ref_no
                  payment_mode
                  card_name
                  currency
                  billing_name
                  manual_amount
                  manual_transaction_date
                  salesforce_status
                  amount
                  transaction_date
                */
              }
              //$this->createUser($message['Message']);
              // Create user object.
              $user = User::create();
              //Mandatory settings
              $user->setPassword("password");
              $user->enforceIsNew();
              $user->setEmail($emailAddress);
              $user->setUsername($emailAddress); // TO DO Check Username
              $user->activate();
              $user->set("field_first_name", $firstName);
              $user->set("field_last_name", $lastName);
              $user->set("field_corporate_name", 'https://www.eventjini.com?corporate=EventJini');
              $user->set("field_mobile_number", $mobileNumber);
              //$eventjiniUser = $user->save();

              //$violations = $user->validate();
              if (isset($violations) && count($violations)) {
                $this->logger->info('Violations are %message', [
                  '%message' => $violations,
                ]);
              } else {
                $user->save();
                $eventjiniUser = $user->id();
//                $this->logger->info('User received is %message', [
//                  '%message' => $eventjiniUser,
//                ]);
                $user = User::load($eventjiniUser);
                $webformSubmissionId = $user->get('field_webform')->value;
                $this->logger->info('webform received is %message', [
                  '%message' => $webformSubmissionId,
                ]);
                $webform_submission = WebformSubmission::load($webformSubmissionId);
                $data = $webform_submission->getData();
                if(isset($data['institution']) && $data['institution'] == "EventJini") {
                  $webform_submission->setElementData('verified', 'Yes');
                  $webform_submission->setElementData('mailer', '');

                  $webform_submission->setElementData('payment_status', 'Success');
                  $webform_submission->setElementData('gender', $gender);
                  $webform_submission->setElementData('date_of_birth', $dob);

                  $webform_submission->setElementData('city', $city);
                  $webform_submission->setElementData('address', $address);
                  $webform_submission->setElementData('pan_card_number', $zip_code);
                  /*
                  challenge_type
                  challenge_slot
                  nationality
                  terms_of_service
                  term_condtions_2
                  term_condtions_3
                  order_id
                  tracking_id
                  bank_ref_no
                  payment_mode
                  card_name
                  currency
                  billing_name
                  manual_amount
                  manual_transaction_date
                  salesforce_status
                  amount
                  transaction_date
*/
                  $webform_submission->save();
                }
              }
            }
          }
        }
/*
        if(!empty($data->additional_data)) {
          $additional = json_decode($message['Message']['additional_data']);
          $this->logger->info('Additional Data Message received is %additional.', [
            '%additional' => $additional,
          ]);
          $this->createUser($message['Message']);
        }
*/
      }
    }
  }

  /**
   * function to create user
   */
  public function createUser($data)
  {
    if (!empty($data)) {
      $this->logger->info('From Create %message-id received for topic %topic.', [
        '%message-id' => $data['MessageId'],
        '%topic' => $data['TopicArn'],
      ]);

      // Create user object.
      $user = User::create();
      //Mandatory settings
      $user->setPassword("password");
      $user->enforceIsNew();
      $user->setEmail("devenderdagar+1000@gmail.com");
      $user->setUsername("devenderdagar1000"); // TO DO Check Username
      $user->addRole('authenticated');
      $user - save();

      //$this->createWebform($data);
    }
  }

  /**
   * function to create user webform
   */
  public function createWebform($data)
  {
    if (!empty($data)) {
      $this->logger->info('From Webform %message-id received for topic %topic.', [
        '%message-id' => $data['MessageId'],
        '%topic' => $data['TopicArn'],
      ]);
      $this->sendToSalesForce($data);
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
