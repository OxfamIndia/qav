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
use Drupal\node\Entity\Node;

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
                  if(!empty($pan)) {
                    $webform_submission->setElementData('pan_card_number', $pan);
                  }
                  $webform_submission->setElementData('challenge_type', $challenge_type);
                  if($challenge_slot == '20-29 November') {
                    $challenge_slot = 1;
                  }
                  //$webform_submission->setElementData('challenge_slot', $challenge_slot);
                  //$webform_submission->setElementData('challenge_slot', ['target_id' => $challenge_slot]);

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
                  $nodeData = [
            'type' => 'virtual_trail',
            'title' => 'Dashboard'.' ('.$emailAddress.'-'.$eventjiniUser.')',
            'uid' => $eventjiniUser,
            'field_user_name_id'=>$eventjiniUser,
            'field_day1_distance'=>0,
            'field_day2_distance'=>0,
            'field_day3_distance'=>0,
            'field_day4_distance'=>0,
            'field_day5_distance'=>0,
            'field_day6_distance'=>0,
            'field_day7_distance'=>0,
            'field_day8_distance'=>0,
            'field_day9_distance'=>0,
            'field_day10_distance'=>0,
            'status' => 0,
        ];

        $entity = Node::create($nodeData);
        $entity->save();

        $data = array();
        $data['user_id']=$eventjiniUser;
        $data['amount'] = $amount;
        $data['first_name'] = $firstName;
        $data['last_name']= $lastName;
        $data['email_address']= $emailAddress;
        $data['date_of_birth']= $dob;
        $data['gender']= $gender;
        $data['address']= $address;
        $data['city']= $city;
        $data['country']['administrative_area']= $state;
        $data['country']['country_code'] = 'IN';
        $data['nationality'] =$nationality;
        $data['mobile_number'] = $mobileNumber;
        $data['zip_code']= $zip_code;
        $data['institution'] = 'EventJini';
        $data['payment_status'] = 'Success';
        $data['payment_mode']=$payment_mode;
        $data['registration_url'] = "https://www.eventjini.com";
        $data['pan_card_number'] = $pan;
      //  $data['challenge_slot'] = $challenge_slot;
        $data['challenge_slot'] = 1;




        /************************ Start Salsesforce data capture *************************/
          $curl = curl_init();
          curl_setopt_array($curl, array(
          //CURLOPT_PORT => "8443",
          CURLOPT_URL => "https://login.salesforce.com/services/oauth2/token?",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 100,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>"grant_type=password&client_id=3MVG9ZL0ppGP5UrAnDoDW3hqXg_ipjDKSijhdORrja6kLzssSK6QQg5dSYACBU12x.GP6MFTX_Q4iw7TEh_4k&client_secret=89E2293FEA44330BA6E1EFCCE718C28990451A2966F571570ABD1E52187F9ED6&username=websiteintegrationsf@oxfamindia.org&password=OxfamIndia@1234",
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded"
            ),
          ));
          $product_type = 'OT';
          $response = curl_exec($curl);
          curl_close($curl);
          $character = json_decode($response);

            $token = $character->access_token;

			 $this->logger->info('Get  token %message received for --- --- %messages ---- topic .', [
      '%message' =>  $data['mobile_number'],
      '%messages' => $token
          ]);
          $status = 'Promise';
          if($data['payment_status'] == 'Success')
          {
            $status = 'Successful';
            $domestic = 'Foreign Passport';
          }



          $nationalitys = 'Foreign Passport';
          $domestic = 'International';
            if($data['nationality'] == 'Indian')
          {
            $nationalitys = 'Indian Passport';
            $domestic = 'domestic';
          }


          $nationalitys = '';
          $domestic = '';
          $today_timestamp = time();
          $order_id = 'VTWdonate'.$data['user_id'].'-'.$today_timestamp;
$this->logger->info('Get  token 13 %message received for --- --- %messages ---- topic .', [
      '%message' =>  $data['challenge_slot'],
      '%messages' => $token
          ]);

         // $node = Node::load($data['challenge_slot']);
          $node = Node::load($data['challenge_slot']);
          $eventname = "VTM-".$data['challenge_type'].' '.$node->get('title')->value ;


$this->logger->info('Get  token 14 %message received for --- --- %messages ------%messagess---- topic .', [
      '%message' =>  $data['mobile_number'],
      '%messages' =>  $node->get('title')->value,
      '%messagess' =>  $eventname


          ]);


          $mobileno =   explode(' ', $data['mobile_number']);
          $ext = $mobileno[0];
          array_shift($mobileno);
          $mos = implode("",$mobileno);

          /*  $user_country_name = \Drupal::service('country_manager')->getList()[$data['country']['country_code']]->__toString(); */
           $user_country_name = 'India';
          $this->logger->info('Full Message token %message received for --- --- %messages ---- topic .', [
      '%message' => $mos,
      '%messages' => $token
          ]);

           $post_fields = array(

              "transList" => array(
              "0" => array(

                "Name" => $data['user_id'],
                "Donation_contribution_amount__c" => $data['amount'],
                "Donation_bgtxnid__c" => $data['user_id'],
                "Payment_transaction_id__c" => $order_id,
                "Payment_contribution_date__c" => date('Y-m-d H:i:s'),
                "Donor_First_Name__c" => $data['first_name'],
                "Donor_Last_Name__c" => $data['last_name'],
                "Donor_Email_ID__c" => $data['email_address'],
                  "Donor_DOB__c" => $data['date_of_birth'],
                // "Donor_DOB__c" => $dob_dummy,
                 "Product_Type__c" => $product_type,
                "Donor_Gender__c" => $data['gender'],
                "Billing_Address__c" => $data['address'],
                "City__c" => $data['city'],
                "State__c" => $data['country']['administrative_area'],
                "Country__c" => $user_country_name,
                "Nationality__c" => $nationalitys,
                "Pincode__c" => $data['zip_code'],
                "Donor_Mobile_No__c" => $mos,
                "Donor_Emergency_Contact_No__c" => $ext,
                "Donor_Organisation__c" => $data['institution'],
                "Payment_update_time__c" => '',
                "Payment_payment_status__c" => $status,
                "Payment_other_values__c" => '',
                "Payment_pg_txn_id__c" => $order_id,
                "Payment_pg_transaction_ref_no__c" => '',
                "Spouse_Gift_Message__c" => '',
                "Payment_payment_type__c" => 'offline',
                "Payment_payment_for__c" => 'Registration',
                "Payment_gateway_type__c" => 'CCAvenue',
                "Payment_payment_type_mode__c" => $domestic,

                "Payment_payment_mode__c" => $data['payment_mode'],

                "Donation_tenure__c" => '',
                "Payment_refund__c" => '',
                "Payment_cheque_no__c" => '',
                "Payment_cheque_due_date__c" => '',

                "Addcertname__c" => '',
                "Sharewithteam__c" => '',
                "Donation_contri_for__c" => 'General',
                "Donation_campaign_id__c" => 'Virtual trailwalker',
                 "Donation_hmn_campaign_id__c" =>  $data['registration_url'],
                "Donor_Passport_Number__c" => '',
                "Donor_PAN_Number__c" => $data['pan_card_number'],
                "Donation_donate_campaign_type__c" => '',
                "Donation_page_url__c" => 'https://virtualtrailwalker.oxfamindia.org/user/register',

                 "Donation_contribution_date_unix__c" => date('Y-m-d H:i:s'),
                "Donation_flag__c" => '',
                "Donation_disclaimer__c" => '',
                "Address_2__c" => '',
                "Address_3__c" => '',
                "Spouse__c" => '',
                "Spouse_Mobile_No__c" => '',
                "Spouse_Gift_Message__c" => '',
                "Donation_how_did_you_hear_about__c" => ' ',
                "Donation_name_of_the_fundraiser__c" => ' ',
                "Testimonial__c" => ' ',
                "Payment_transaction_id__c" => $order_id,
                "Donation_team_id__c" => '',
                  "Event_Name__c" => trim($eventname),
                  "Event_Location__c" => 'Virtual Trailwalker',
                  "Donor_T_Shirt_Size__c" => '',
                "Team_ID__c" => '',
                "Team_Registration__c" => 'Corporate',
                "Registration_Type__c" => 'Offline',
                "Team_Registration_Date__c" => date('Y-m-d H:i:s'),
                "Team_Name__c" => '',
                )
                )
              );

              $post_fields = (object) $post_fields;

           $post_fields = json_encode($post_fields,true);

            $header = array(
              "Authorization: Bearer $token",
              "Content-Type: application/json"
            );
			$this->logger->info('Full Message Jain %message received for --- --- %messages ---- topic .', [
      '%message' => $post_fields,
      '%messages' => $token
          ]);
           $curl = curl_init();
            $params = array(
            CURLOPT_URL => "https://oxfam.my.salesforce.com/services/apexrest/TransactionEntry/",
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
              CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => $header
            );

            curl_setopt_array($curl, $params);
            $response = curl_exec($curl);
            $err_no = curl_errno( $curl );
            $err = curl_error($curl);
            curl_close($curl);
            $result = json_decode($response,true);
          $x = $result[0]['Status'];

		  $this->logger->info('Full Message rohit jha ========= %messageid  ========= received for topic .', [
      '%messageid' => $x
          ]);




          $webform_submission = WebformSubmission::load($data['submission_id']);

          $datas = $webform_submission->getData();
          $datas['salesforce_status'] = $result[0]['Status'];
          $datas['payment_status'] = "Success";
          //$datas['mailer'] = $status;
          $datas['mailer'] = 'EventJini';

          // Set submission data.
          $webform_submission->setData($datas);

          // Save submission.
          $webform_submission->save();


                  $webform_submission->setElementData('mailer', '');
                  $webform_submission->save();

/************************ End Salsesforce data capture *************************/



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
