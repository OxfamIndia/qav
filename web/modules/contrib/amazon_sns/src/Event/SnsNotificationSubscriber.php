<?php

namespace Drupal\amazon_sns\Event;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Drupal\user\Entity\User;


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

        //$data = json_decode($data['additional_data'], true);
        $data = json_decode($data['additional_data'], true);
        foreach ($data as $value) {
            //$dataJson = json_decode($jsons);
            $this->logger->info('Key received is %message.', [
              '%message' => $data['itemmeta'],
            ]);
          $value = json_decode($data['items'], true);
          $this->logger->info('Value received is %message.', [
            '%message' => $value,
          ]);
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

      $this->createWebform($data);
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
