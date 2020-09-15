<?php
/**
 * @file
 * Contains \Drupal\custom_user_register\Controller\DonationController.
 */

namespace Drupal\custom_user_register\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\paytm\ConfigPaytm;
use Drupal\paytm\EncdecPaytm;
use Drupal\taxonomy\Entity\Term;
use Drupal\twloginblock\Controller\OtpLoginController;
use \Drupal\user\Entity\User;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;

class DonationController extends ControllerBase {

	public function SalesforceResponse($data){
					
					
					
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
					CURLOPT_POSTFIELDS => "grant_type=password&client_id=3MVG9ZL0ppGP5UrAnDoDW3hqXg_ipjDKSijhdORrja6kLzssSK6QQg5dSYACBU12x.GP6MFTX_Q4iw7TEh_4k&client_secret=89E2293FEA44330BA6E1EFCCE718C28990451A2966F571570ABD1E52187F9ED6&username=websiteintegrationsf@oxfamindia.org&password=OxfamIndia@1234",
					CURLOPT_HTTPHEADER => array(
						"Content-Type: application/x-www-form-urlencoded"
						),
					));
					$product_type = 'OT';
					$response = curl_exec($curl);
					curl_close($curl);
					$character = json_decode($response);					
					    echo '<pre>'; print_r($character); echo '</pre>';  
					    echo '<pre>'; print_r($data); echo '</pre>';  exit;
					  $token = $character->access_token;
					$status = 'Unsuccessful';
					if($data['payment_status'] == 'Success')
					{
						$status = 'Successful';
						$domestic = 'international';
					}if($data['nationality'] == 'indian')
					{
						$domestic = 'domestic';
					}
					
					 $post_fields = array(
						  
						  "transList" => array(
						  "0" => array(
								"Name" => $data['user_id'],
								"Donation_contribution_amount__c" => '1000',
								"Donation_bgtxnid__c" => $data['user_id'],
								"Payment_transaction_id__c" => $data['order_id'],
								"Payment_contribution_date__c" => date('m-d-Y H:i:s', $data['created']),
								"Donor_First_Name__c" => $data['first_name'],
								"Donor_Last_Name__c" => $data['last_name'],
								"Donor_Email_ID__c" => $data['email_address'],
								  "Donor_DOB__c" => $data['date_of_birth'],  
								// "Donor_DOB__c" => $dob_dummy,								
								 "Product_Type__c" => $product_type,								 
								"Donor_Gender__c" => $data['gender'],
								"Billing_Address__c" => $data['address'],
								"City__c" => $data['city'],
								"State__c" => $data['state'],
								"Country__c" => $data['country'],
								"Nationality__c" => $data['nationality'],
								"Pincode__c" => $data['zip_code'],
								"Donor_Mobile_No__c" => $data['mobile_number'],
								"Donor_Organisation__c" => $data['institution'],
								"Payment_update_time__c" => '',
								"Payment_payment_status__c" => $status,
								"Payment_other_values__c" => '',
								"Payment_pg_txn_id__c" => $data['order_id'],
								"Payment_pg_transaction_ref_no__c" => '',
								"Spouse_Gift_Message__c" => '',
								"Payment_payment_type__c" => 'online',
								"Payment_payment_for__c" => 'Registration',
								"Payment_gateway_type__c" => 'CCAvenue',  
								"Payment_payment_type_mode__c" => 'CCAvenue', 
								"Payment_gateway_mode__c" => $domestic,
								"Payment_payment_mode__c" => $data['payment_mode'],
							//	"Payment_gateway_response__c" => $data['gateway_response'],
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
							 	"Donation_page_url__c" => 'https://virtualtrailwalker.oxfamindia.org/user/register'							
								 "Donation_contribution_date_unix__c" => date('Y-m-d H:i:s', $data['created']),
								//"Donation_contribution_date_unix__c" => '2020-07-12 01:02:01',
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
								"Payment_transaction_id__c" => $data['order_id'],
								"Donation_team_id__c" => '',
								  "Event_Name__c" => trim($data['event_name']),  
								  "Event_Location__c" => 'Virtual Trailwalker', 
								  "Donor_T_Shirt_Size__c" => '',
								"Team_ID__c" => '',
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
					$update=array();
					$update['salesforce_status'] = $result[0]['Status'];


					$webform_submission = WebformSubmission::load($data['user_id']);
					// Set submission data.
					$webform_submission->setData($update);

					// Save submission.
					$webform_submission->save();  
					
					
	}


	public function ccAveenuePaymentRespons(){

		require_once DRUPAL_ROOT . '/modules/custom/custom_user_register/src/Form/Crypto.php';				
				$ccavenue_config = \Drupal::config('custom_user_register.ccavenue_config');
				
				$nationality = $_GET['n'];
				if($nationality == 'Indian'){
					$workingKey = $ccavenue_config->get('working_key');
				}else{
					$workingKey = $ccavenue_config->get('international_working_key');
				}
				
				$encResponse = $_POST["encResp"];         //This is the response sent by the CCAvenue Server
				$rcvdString = decrypt($encResponse,$workingKey); 
				$order_status="";
				$order_id = "";
				$tracking_id = "";
				$bank_ref_no = "";
				$payment_mode = "";
				$card_name = "";
				$currency = "";
				$user_id = "";
				$billing_name = "";
				$total_response = $rcvdString;
	$decryptValues=explode('&', $rcvdString);
	$dataSize=sizeof($decryptValues);

	for($i = 0; $i < $dataSize; $i++) 
	{
		$information=explode('=',$decryptValues[$i]);
		//kint($information);
	
		if($i==0)	$order_id=$information[1];
		if($i==1)	$tracking_id=$information[1];
		if($i==2)	$bank_ref_no=$information[1];
		if($i==3)	$order_status=$information[1];
		if($i==5)	$payment_mode=$information[1];
		if($i==6)	$card_name=$information[1];
		if($i==9)	$currency=$information[1];
		if($i==11)	$billing_name=$information[1];
		if($i==26)	$user_id=$information[1];
	}
	$webform_submission = WebformSubmission::load($user_id);
		// Get submission data.
$data = $webform_submission->getData();

// Change submission data.
$data['payment_status'] = $order_status;
$data['order_id'] = $order_id;
$data['tracking_id'] = $tracking_id;
$data['bank_ref_no'] = $bank_ref_no;
$data['payment_mode'] = $payment_mode;
$data['card_name'] = $card_name;
$data['currency'] = $currency;
$data['billing_name'] = $billing_name;
$data['total_response'] = $total_response;
$data['user_id'] = $user_id;

// Set submission data.
$webform_submission->setData($data);

// Save submission.
$webform_submission->save();
	$this->SalesforceResponse($data);
if($order_status==="Success")
	{
		/*$account = User::load($user_id);
		$account->activate();
		$account->save();
		 $walker_total_distance = $account->get('field_event_type')->getValue()[0]['value'];
		 $event_id = $account->field_event_name->getValue()[0]['target_id'];
    $event_data = Node::load($event_id);
   $event_name = $event_data->getTitle();
$walker_name =$account->getUsername();
	$mailManager = \Drupal::service('plugin.manager.mail');
 $module = 'walk';
 $key = 'register_mail';
 $to = $account->getEmail();
 //$to = \Drupal::currentUser()->getEmail();
 //$to = 'garglalit0@gmail.com';
 $params['message'] = $walker_name.'&'.$walker_total_distance.'&'.$event_name;
 $params['mail_title'] = 'Registration';
 $langcode = \Drupal::currentUser()->getPreferredLangcode();
 $send = true;
 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);*/
 $user = user_load_by_name($billing_name);
$uid = $user->id();
$nodeData = [
            'type' => 'virtual_trail',
            'title' => 'Dashboard'.' ('.$billing_name.'-'.$uid.')',
            'uid' => $uid,
            'field_user_name_id'=>$uid,
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
    $response = new RedirectResponse('/success');
$response->send();
	
exit();
		
	}
	else{

		 $response = new RedirectResponse('/failure');
$response->send();
exit();
	}
	}


	public function GenericDonationResponse(){

		try {
			$values = \Drupal::state()->get('donation_submission_data');//session data to paas in other function			
			if (!empty($values)) {				
				$gateway_type = trim($values['gateway_type']);			
			$order_id = trim(strip_tags($values['order_id']));
			$donation_id = $values['donation_id'];
			$values['teamname'] = "";			
				if($donation_id != "" && !empty($donation_id)){					
					$donationdetails = node::load($donation_id);
					if($donationdetails->get('field_donate_to_type')->value == "teams"){
						$teamid = $donationdetails->get('field_donation_to_team')->target_id;
						if($teamid != "" && !empty($teamid)){
							$teamdata = node::load($teamid);
							$values['teamname'] = "Team Name: ".$teamdata->get('title')->value;
						}
					}elseif($donationdetails->get('field_donate_to_type')->value == "user"){						
						$memberid = $donationdetails->get('field_donation_to_user')->target_id;
						if($memberid != "" && !empty($memberid)){
							$memberdata = user::load($memberid);							
							$values['teamname'] = "Walker Name: ".$memberdata->get('field_first_name')->value." ".$memberdata->get('field_last_name')->value;
						}
					}
				}												
			$success_message = '';
			$failure_message = '';
			if ($gateway_type == 'paytm') {
				header("Pragma: no-cache");
				header("Cache-Control: no-cache");
				header("Expires: 0");				
				$paytm = new ConfigPaytm();
				$paytmenc = new EncdecPaytm();
				$ORDER_ID = "";
				$requestParamList = array();
				$responseParamList = array();
				if (isset($order_id) && $order_id != "") {	
					// In Test Page, we are taking parameters from POST request. In actual implementation these can be collected from session or DB.
					$ORDER_ID = $order_id;	
					// Create an array having all required parameters for status query.
					$requestParamList = array("MID" => $paytm->PAYTM_MERCHANT_MID , "ORDERID" => $ORDER_ID);	
					$StatusCheckSum = $paytmenc->getChecksumFromArray($requestParamList,$paytm->PAYTM_MERCHANT_KEY);	
					$requestParamList['CHECKSUMHASH'] = $StatusCheckSum;						
					$responseParamList = $paytmenc->getTxnStatusNew($requestParamList);
					
				}
			} else if ($gateway_type == 'CCAvenue') {
				require_once DRUPAL_ROOT . '/modules/custom/donations/src/Form/Crypto.php';				
				$ccavenue_config = \Drupal::config('donations.ccavenue_config');
				$workingKey = $ccavenue_config->get('working_key');				
				$encResponse = $_POST["encResp"];         //This is the response sent by the CCAvenue Server
				$rcvdString = decrypt($encResponse,$workingKey);      //Crypto Decryption used as per the specified working key.				
				$order_status = "";
				$decryptValues = explode('&', $rcvdString);
				$dataSize = sizeof($decryptValues);								
				for($i = 0; $i < $dataSize; $i++)
				{
					$information = explode('=',$decryptValues[$i]);
					if($i == 3) {
						$order_status = $information[1];
					}
					if($information[0]=='order_id') {
						
						$donation_id = str_replace('twdonate','',$information[1]);
					}										
				}				
				if ($order_status === "Success") {
					$success_message = "Thank you for your donation.";
					$payment_status = 1;
					$messageData = 'Thank you for your donation. Your payment has been successfully completed';
				} else if($order_status === "Aborted") {
					$failure_message = "Transaction Aborted";
					$payment_status = 0;
					$messageData = 'Your transaction has been aborted. Please try again later.';
				} else if($order_status === "Failure") {
					$failure_message = "Transaction declined.";
					$payment_status = 0;
					$messageData = 'Your transaction has been declined. Please try again later.';
				} else {
					$failure_message = "Security Error. Illegal access detected";
					$payment_status = 0;
					$messageData = 'Your transaction has been declined. Please try again later.';
				}				 				
				for($i = 0; $i < $dataSize; $i++)
				{
					$information = explode('=',$decryptValues[$i]);					
				}					
			} else if ($gateway_type == 'CCAvenue International') {
				require_once DRUPAL_ROOT . '/modules/custom/donations/src/Form/Crypto.php';
				
				$ccavenue_config = \Drupal::config('donations.ccavenue_config');
				$workingKey = $ccavenue_config->get('international_working_key');
				
				$encResponse = $_POST["encResp"];         //This is the response sent by the CCAvenue Server
				$rcvdString = decrypt($encResponse,$workingKey);      //Crypto Decryption used as per the specified working key.
				
				$order_status = "";
				$decryptValues = explode('&', $rcvdString);
				$dataSize = sizeof($decryptValues);					
				for($i = 0; $i < $dataSize; $i++)
				{
					$information = explode('=',$decryptValues[$i]);
					if($i == 3) {
						$order_status = $information[1];
					}
					if($information[0]=='order_id') {
						
						$donation_id = str_replace('twdonate','',$information[1]);
					}
				}	
				if ($order_status === "Success") {
					$success_message = "Thank you for your donation.";
					$payment_status = 1;
					$messageData = 'Thank you for your donation. Your payment has been successfully completed';
				} else if($order_status === "Aborted") {
					$failure_message = "Transaction Aborted";
					$payment_status = 0;
					$messageData = 'Your transaction has been aborted. Please try again later.';
				} else if($order_status === "Failure") {
					$failure_message = "Transaction declined.";
					$payment_status = 0;
					$messageData = 'Your transaction has been declined. Please try again later.';
				} else {
					$failure_message = "Security Error. Illegal access detected";
					$payment_status = 0;
					$messageData = 'Your transaction has been declined. Please try again later.';
				}					
				for($i = 0; $i < $dataSize; $i++)
				{
					$information = explode('=',$decryptValues[$i]);
					
				}					
			}			 			 							
				if (isset($donation_id) && $donation_id > 0){
					$node = Node::load($donation_id);
				} else {
					$node = Node::create();
				}
				if ($gateway_type == 'paytm') {
					if (isset($responseParamList) && count($responseParamList) >0 ) {
						$node->set('field_tracking_id', $responseParamList['TXNID']);
						$node->set('field_bank_ref_no', $responseParamList['BANKTXNID']);
						if ($responseParamList['STATUS'] == 'TXN_SUCCESS') {
							$node->set('field_order_status', 'Success');
							$success_message = "Thank you for your donation.";
							$payment_status = 1;
							$messageData = 'Thank you for your donation. Your payment has been successfully completed';
						} else {
							$node->set('field_order_status', 'Failed');
							$failure_message = "Transaction declined.";
							$payment_status = 0;
							$messageData = 'Your transaction has been declined. Please try again later.';
						}
						$order_status = $responseParamList['STATUS'];						
	
						$node->set('field_order_id', $responseParamList['ORDERID']);
						$node->set('field_donation_amount', $responseParamList['TXNAMOUNT']);
						$node->set('field_mer_amount_', $responseParamList['TXNAMOUNT']);
						$node->set('field_gateway_name', $responseParamList['GATEWAYNAME']);
						$node->set('field_response_code', $responseParamList['RESPCODE']);
						$node->set('field_payment_mode', $responseParamList['PAYMENTMODE']);
						$node->set('field_transaction_type', $responseParamList['TXNTYPE']);
						$node->set('field_trans_date', date("Y-m-d\TH:i:s", strtotime($responseParamList['TXNDATE'])));
						$node->set('field_gateway_return_response', serialize($responseParamList));						
					}
				} else if ($gateway_type == 'CCAvenue') {
					for($i = 0; $i < $dataSize; $i++)
					{
						$information = explode('=',$decryptValues[$i]);
						
						if (trim($information[0]) != '') {
							$key = 'field_'.$information[0];
							$res_val = $information[1];
							if ($key == 'field_amount') {
								$key = 'field_donation_amount';
							}
							if ($key == 'field_mer_amount') {
								$key = 'field_mer_amount_';
							}
							if ($key == 'field_trans_date') {
								
								$res_val = str_replace('/','-',$res_val);
								$res_val = date("Y-m-d\TH:i:s", strtotime($res_val));
							}
							$return_info[$key] = urldecode($res_val);
							if($key != 'field_merchant_param3' && $key != 'field_merchant_param5')
								$node->set($key,urldecode($res_val));
						}
					}
					$node->set('field_gateway_return_response', serialize($return_info));					
					//custom transaction table end
				} else if ($gateway_type == 'CCAvenue International') {
					for($i = 0; $i < $dataSize; $i++)
					{
						$information = explode('=',$decryptValues[$i]);
						
						if (trim($information[0]) != '') {
							$key = 'field_'.$information[0];
							$res_val = $information[1];
							if ($key == 'field_amount') {
								$key = 'field_donation_amount';
							}
							if ($key == 'field_mer_amount') {
								$key = 'field_mer_amount_';
							}
							if ($key == 'field_trans_date') {
								
								$res_val = str_replace('/','-',$res_val);
								$res_val = date("Y-m-d\TH:i:s", strtotime($res_val));
							}
							$return_info[$key] = urldecode($res_val);
							if($key != 'field_merchant_param3' && $key != 'field_merchant_param5')
								$node->set($key,urldecode($res_val));
						}
					}
					$node->set('field_gateway_return_response', serialize($return_info));					
					//custom transaction table end
				} else {
					$order_status = 'In progress';
					
					$success_message = "Thank you for your donation.";
				}
				$node->set('type','donations');								
				$node->save();				
				\Drupal::state()->delete('donation_submission_data');
				// sending donations email and sms 
				$this->sendDonationEmail($values, $order_status);
				$this->sendDonorEmails($values, $order_status);
				$this->sendDonationAdminEmail($values, $order_status);				
				$this->donationPostCreate($values, $order_status);				
				$number = trim($values['mobile']);
				$sms = new OtpLoginController();
				$sms->sendDonationSMS($number, $messageData);
				// END
			} else {
				$success_message = '';
				$failure_message = "Please try again later.";
			}
		} catch (\Exception $e) {
			$values = \Drupal::state()->get('donation_submission_data');
			$payment_status = "Failed";
			$this->sendDonationExceptionEmail($values, $payment_status);
			$success_message = '';
			$failure_message = "Please try again later.";
			\Drupal::state()->delete('donation_submission_data');
		}		
        return array(
        	'#attached' => ['library' => ['donations/genericdonationresponse-script']],
        	'#theme' => 'generic_donation_response',
        	'#success' => $success_message,
        	'#failure' => $failure_message
        );
		}
		public function Paytmdonationresponse(){
			try {
				$donation_id = str_replace('twdonate','',$_REQUEST['ORDERID']);				
				$conn = Database::getConnection();
				$request_json = json_encode($_REQUEST);
				$last_id = $conn->insert('donationresponse_log')->fields(array(
					'donation_id'=> $donation_id,     
					'request_data'=> $request_json, 
					'payment_type'=> 'paytm',                							                       
				))->execute(); 
				if (!empty($_REQUEST)) {	
					$values['donation_id'] = $donation_id = (int) $donation_id;
					$donationdetails = node::load($donation_id);							
					$gateway_type = trim($donationdetails->get('field_gateway_type')->value);			
					
					$order_id = trim(strip_tags($_REQUEST['ORDERID']));					
					$values['teamname'] = "";			
					if($donation_id != "" && !empty($donation_id)){
						if($donationdetails->get('field_donate_to_type')->value == "teams"){
							$teamid = $donationdetails->get('field_donation_to_team')->target_id;
							if($teamid != "" && !empty($teamid)){
								$teamdata = node::load($teamid);
								$values['teamname'] = "Team Name: ".$teamdata->get('title')->value;
								$donated_to_name = $teamdata->getTitle();
								$values['donated_to'] = 'Team - '.$donated_to_name;					
								$values['donated_to_type'] = 'Team';
								$values['donated_to_id'] = $teamid;
							}
						}elseif($donationdetails->get('field_donate_to_type')->value == "user"){						
							$memberid = $donationdetails->get('field_donation_to_user')->target_id;
							if($memberid != "" && !empty($memberid)){
								$memberdata = user::load($memberid);							
								$values['teamname'] = "Walker Name: ".$memberdata->get('field_first_name')->value." ".$memberdata->get('field_last_name')->value;
								$values['donated_to'] = 'Member - '. $memberdata->get('field_first_name')->value." ".$memberdata->get('field_last_name')->value;          		
								/*used to create donation post timeline start*/
								$values['donated_to_type'] = 'User';
								$values['donated_to_id'] = $memberid;
							}
						}
					}												
					$success_message = '';
					$failure_message = '';

					header("Pragma: no-cache");
					header("Cache-Control: no-cache");
					header("Expires: 0");				
					$paytm = new ConfigPaytm();
					$paytmenc = new EncdecPaytm();
					$ORDER_ID = "";
					$requestParamList = array();
					$responseParamList = array();
					if (isset($order_id) && $order_id != "") {	
						// In Test Page, we are taking parameters from POST request. In actual implementation these can be collected from session or DB.
						$ORDER_ID = $order_id;	
						// Create an array having all required parameters for status query.
						$requestParamList = array("MID" => $paytm->PAYTM_MERCHANT_MID , "ORDERID" => $ORDER_ID);	
						$StatusCheckSum = $paytmenc->getChecksumFromArray($requestParamList,$paytm->PAYTM_MERCHANT_KEY);	
						$requestParamList['CHECKSUMHASH'] = $StatusCheckSum;						
						$responseParamList = $paytmenc->getTxnStatusNew($requestParamList);
						$conn->update('donationresponse_log')->fields(['response' => serialize($responseParamList)])
						->condition('id', $last_id, '=')->execute();
					}
		 			 							
					if (isset($donation_id) && $donation_id > 0){
						$node = $donationdetails;
					} else {
						$node = Node::create(['type'=>'donations','title'=>'wrong donation']);
					}

					if (isset($responseParamList) && count($responseParamList) >0 ) {
						$node->set('field_tracking_id', $responseParamList['TXNID']);
						$node->set('field_bank_ref_no', $responseParamList['BANKTXNID']);
						if ($responseParamList['STATUS'] == 'TXN_SUCCESS') {
							$node->set('field_order_status', 'Success');
							if($donationdetails->get('field_payment_for')->value == 'registration'){
								$success_message = "Thank you for registration.";
							}else{
								$success_message = "Thank you for your donation.";
							}
							$payment_status = 1;
							$messageData = 'Thank you for your donation. Your payment has been successfully completed';
						} else {
							$node->set('field_order_status', 'Failed');
							$failure_message = "Transaction declined.";
							$payment_status = 0;
							$messageData = 'Your transaction has been declined. Please try again later.';
						}
						$order_status = $responseParamList['STATUS'];								
						$node->set('field_order_id', $responseParamList['ORDERID']);
						$node->set('field_donation_amount', $responseParamList['TXNAMOUNT']);
						$node->set('field_mer_amount_', $responseParamList['TXNAMOUNT']);
						$node->set('field_gateway_name', $responseParamList['GATEWAYNAME']);
						$node->set('field_response_code', $responseParamList['RESPCODE']);
						$node->set('field_payment_mode', $responseParamList['PAYMENTMODE']);
						$node->set('field_transaction_type', $responseParamList['TXNTYPE']);
						$node->set('field_trans_date', date("Y-m-d\TH:i:s", strtotime($responseParamList['TXNDATE'])));
						$node->set('field_gateway_return_response', serialize($responseParamList));						
					}
											
					$node->save();				
					//
					// sending donations email and sms 
					
					$values['select_city'] = $donationdetails->get('field_trailwalker_cities')->target_id; 
					$donor_id = $donationdetails->get('field_donor_details_id')->target_id;
					$donor_details = node::load($donor_id);
					$values['emails_to'] = $values['e_mail'] = $donor_details->get('field_donor_e_mail')->value;
					$values['first_name'] = $donor_details->get('field_donor_first_name')->value;
					$values['last_name'] = $donor_details->get('field_donor_last_name')->value;
					$values['price'] = round($donationdetails->get('field_mer_amount_')->value);
					$values['order_id'] = $order_id;
					if($donationdetails->get('field_payment_for')->value == 'registration'){
						$otplogin_controller = new OtpLoginController();
						$sendDonationEmail = $otplogin_controller->sendDonationEmail($values, $order_status);												
						$this->sendDonationAdminEmail($values, $order_status);
					}else{
						$this->sendDonationEmail($values, $order_status);
						$this->sendDonorEmails($values, $order_status);
						$this->sendDonationAdminEmail($values, $order_status);				
						$this->donationPostCreate($values, $order_status);
					}					
					$number = trim($donor_details->get('field_donor_mobile_number')->value);
					$sms = new OtpLoginController();
					$sms->sendDonationSMS($number, $messageData);
					// END
				} else {
					$success_message = '';
					$failure_message = "Please try again later.";
				}
			} catch (\Exception $e) {
				$values = \Drupal::state()->get('donation_submission_data');
				$payment_status = "Failed";
				$this->sendDonationExceptionEmail($values, $payment_status);
				$success_message = '';
				$failure_message = "Please try again later.";		
			}	
			\Drupal::state()->delete('donation_submission_data');	
			return array(
				'#attached' => ['library' => ['donations/genericdonationresponse-script']],
				'#theme' => 'generic_donation_response',
				'#success' => $success_message,
				'#failure' => $failure_message
			);
		}
		public function CcavenueDonationResponse(){									
			try {
				require_once DRUPAL_ROOT . '/modules/custom/donations/src/Form/Crypto.php';				
				$ccavenue_config = \Drupal::config('donations.ccavenue_config');
				$workingKey = $ccavenue_config->get('working_key');				
				$encResponse = $_POST["encResp"];         //This is the response sent by the CCAvenue Server
				$conn = Database::getConnection();
				$last_id = $conn->insert('donationresponse_log')->fields(array(					    
					'request_data'=> serialize($encResponse),
					'payment_type'=> 'ccavenue',                							                       
				))->execute();
				$rcvdString = decrypt($encResponse,$workingKey);      //Crypto Decryption used as per the specified working key.
				if(strpos($rcvdString,'order_status')===false){
					$workingKey = $ccavenue_config->get('international_working_key');
					$rcvdString = decrypt($encResponse,$workingKey); 
				}				
				$order_status = "";

				if(array_key_exists('rbt_mode',$_GET) && $_GET['rbt_mode']=='test'){
					$rcvdString='order_id=twdonate294373&tracking_id=108664923239&bank_ref_no=844286&order_status=Success&failure_message=&payment_mode=Debit Card&card_name=Visa Debit Card&status_code=null&status_message=Y:844286:7868840430:PPX :925508974711&currency=INR&amount=2000.00&billing_name=Dhanya&billing_address=&billing_city=Bangalore&billing_state=Karnataka&billing_zip=560043&billing_country=India&billing_tel=9886755484&billing_email=dhanya.a.sukumaran@gmail.com&delivery_name=Dhanya&delivery_address=&delivery_city=Bangalore&delivery_state=Karnataka&delivery_zip=560043&delivery_country=India&delivery_tel=9886755484&merchant_param1=&merchant_param2=&merchant_param3=&merchant_param4=&merchant_param5=&vault=N&offer_type=null&offer_code=null&discount_value=0.0&mer_amount=2000.00&eci_value=null&retry=N&response_code=0&billing_notes=&trans_date=2019-09-12T14:04:30&bin_country=INDIA';
				}	
				//			
				parse_str($rcvdString, $decryptValues);
				$donation_id = str_replace('twdonate','',$decryptValues['order_id']);
				$order_id = $decryptValues['order_id'];
				$conn->update('donationresponse_log')->fields(['donation_id' => $donation_id,'response' => serialize($decryptValues)])
						->condition('id', $last_id, '=')->execute();
				$dataSize = sizeof($decryptValues);	
				if($dataSize>0 && $decryptValues['order_id']!=''){
					$order_status = $decryptValues['order_status'];
					$donation_id = str_replace('twdonate','',$decryptValues['order_id']);
					$donationdetails = '';
					// get donation details from DB
					if (isset($donation_id) && $donation_id > 0){
						$donationdetails = $node = Node::load($donation_id);
					} else {
						$node = Node::create(['type'=>'donations','title' => 'wrong donation']);
					}
					if(empty($donationdetails)){
						$node = Node::create(['type'=>'donations','title' => 'wrong donation']);
					}
					
					foreach($decryptValues as $datakey=>$res_val){
						$key = 'field_'.$datakey;
						if ($key == 'field_amount') {
							$key = 'field_donation_amount';
						}
						if ($key == 'field_mer_amount') {
							$key = 'field_mer_amount_';
						}
						if ($key == 'field_trans_date') {
							$res_val = str_replace('/','-',$res_val);
							$res_val = date("Y-m-d\TH:i:s", strtotime($res_val));
						}

						if($key != 'field_merchant_param3' && $key != 'field_merchant_param5'){
							$node->set($key,urldecode($res_val));
						}
						
						$return_info[$key] = urldecode($res_val);
					}

					$node->set('field_gateway_return_response', serialize($decryptValues));			
					$node->save();

					$success_message = '';
				    $failure_message = '';
										
					if ($order_status === "Success") {
						if($donationdetails->get('field_payment_for')->value == 'registration'){
							$success_message = "Thank you for registration.";
						}else{
							$success_message = "Thank you for your donation.";
						}
						$payment_status = 1;
						$messageData = 'Thank you for your donation. Your payment has been successfully completed';
					} else if($order_status === "Aborted") {
						$failure_message = "Transaction Aborted";
						$payment_status = 0;
						$messageData = 'Your transaction has been aborted. Please try again later.';
					} else if($order_status === "Failure") {
						$failure_message = "Transaction declined.";
						$payment_status = 0;
						$messageData = 'Your transaction has been declined. Please try again later.';
					} else {
						$failure_message = "Security Error. Illegal access detected";
						$payment_status = 0;
						$messageData = 'Your transaction has been declined. Please try again later.';
					}	
					if(!empty($donationdetails)){
						$values['teamname'] ='';
						if($donationdetails->get('field_donate_to_type')->value == "teams"){
							$teamid = $donationdetails->get('field_donation_to_team')->target_id;
							if($teamid != "" && !empty($teamid)){
								$teamdata = node::load($teamid);
								$values['teamname'] = "Team Name: ".$teamdata->get('title')->value;
								$donated_to_name = $teamdata->getTitle();
								$values['donated_to'] = 'Team - '.$donated_to_name;					
								$values['donated_to_type'] = 'Team';
								$values['donated_to_id'] = $teamid;
							}
						}elseif($donationdetails->get('field_donate_to_type')->value == "user"){						
							$memberid = $donationdetails->get('field_donation_to_user')->target_id;
							if($memberid != "" && !empty($memberid)){
								$memberdata = user::load($memberid);							
								$values['teamname'] = "Walker Name: ".$memberdata->get('field_first_name')->value." ".$memberdata->get('field_last_name')->value;
								$values['donated_to'] = 'Member - '. $memberdata->get('field_first_name')->value." ".$memberdata->get('field_last_name')->value;          		
								/*used to create donation post timeline start*/
								$values['donated_to_type'] = 'User';
								$values['donated_to_id'] = $memberid;
							}
						}

						// sending donations email and sms 
						$values['select_city'] = $donationdetails->get('field_trailwalker_cities')->target_id; 
						$donor_id = $donationdetails->get('field_donor_details_id')->target_id;
						$donor_details = node::load($donor_id);
						$values['emails_to'] = $values['e_mail'] = $donor_details->get('field_donor_e_mail')->value;
						$values['first_name'] = $donor_details->get('field_donor_first_name')->value;
						$values['last_name'] = $donor_details->get('field_donor_last_name')->value;
						$values['price'] = round($donationdetails->get('field_mer_amount_')->value);
						$values['order_id'] = $order_id;
						$values['donation_id'] = $donation_id;
						$values['donor_name'] = $donor_details->get('field_donor_first_name')->value." ".$donor_details->get('field_donor_last_name')->value;
						$values['donated_to_city'] = $city_id;

						if($donationdetails->get('field_payment_for')->value == 'registration'){
							$otplogin_controller = new OtpLoginController();
							$sendDonationEmail = $otplogin_controller->sendDonationEmail($values, $order_status);												
							$this->sendDonationAdminEmail($values, $order_status);
						}else{
							$this->sendDonationEmail($values, $order_status);
							$this->sendDonorEmails($values, $order_status);
							$this->sendDonationAdminEmail($values, $order_status);				
							$this->donationPostCreate($values, $order_status);	
						}				
						$number = trim($donor_details->get('field_donor_mobile_number')->value);
						$sms = new OtpLoginController();
						$sms->sendDonationSMS($number, $messageData);
				    }
					// END
				} else {
					$success_message = '';
					$failure_message = "Please try again later.";
				}

			} catch (\Exception $e) {
				$values = \Drupal::state()->get('donation_submission_data');
				$payment_status = "Failed";
				$this->sendDonationExceptionEmail($values, $payment_status);
				$success_message = '';
				$failure_message = "Please try again later.";

			}	
			\Drupal::state()->delete('donation_submission_data');	
			return array(
				'#attached' => ['library' => ['donations/genericdonationresponse-script']],
				'#theme' => 'generic_donation_response',
				'#success' => $success_message,
				'#failure' => $failure_message
			);
		}        
        public function sendDonationEmail($values, $payment_status) {
		
			$citydata = term::load($values['select_city']);
			$cityname = $citydata->get('name')->value;			
        	$system_site_config = \Drupal::config('system.site');
        	$site_email = $system_site_config->get('mail');
        	$teamname = $values['teamname'];
        	if ($payment_status === "Success") {
        		$message = "success";
        	} else if($payment_status === "Aborted") {
        		$message = "failed";
        	} else if($payment_status === "Failure") {
        		$message = "failed";
        	} else if($payment_status === "TXN_SUCCESS") {
        		$message = "success";
        	} else if($payment_status === "Failed") {
        		$message = "failed";
        	} else if($payment_status === "In progress") {
        		$message = "In progress";
        	} else if($payment_status === "PENDING") {
        		$message = "In pending";
        	}
        	$email = trim(strip_tags($values['e_mail']));
        	$username = trim(strip_tags($values['first_name']));
        	if ($message == 'success') {
				$donation_message = 'Transaction success. Thank you for your donation.';
				$query = \Drupal::entityQuery('node')
                    ->condition('type', 'email_templates')
                    ->condition('field_pet_id', '28');
                    $templateid = reset($query->execute());
                    $templatedata = node::load($templateid);			
                    if($cityname == 'mumbai' || $cityname == 'Mumbai'){
						$image = explode('/',$templatedata->field_header_image->entity->getFileUri());
						$image_path = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/'.$image[3];
						$mailtoken = 'trailwalkermumbai@oxfamindia.org';
						$second_image = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/image3.jpg';
					}elseif($cityname == 'bengaluru' || $cityname == 'Bengaluru'){
						$image = explode('/',$templatedata->field_banglore_image->entity->getFileUri());
						$image_path = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/'.$image[3];
						$mailtoken = 'trailwalkerbengaluru@oxfamindia.org';
						$second_image = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/image3payments.jpg';
					}                    
                    ob_start();
                    $pet = pet_load('28');                     
                    $body = $templatedata->get('body')->value;
                    $body = str_replace('image_path',$image_path,$body);
                    $body = str_replace('donor_name', trim(strip_tags($values['first_name'])), $body);
					$body = str_replace('mailtoken', $mailtoken, $body);
					$body = str_replace('second_image', $second_image, $body);
					$body = str_replace('teamname', $teamname, $body);
					$body = str_replace('donation_amount', trim($values['price']), $body);
                    $body = str_replace('transaction_id', trim(strip_tags($values['order_id'])), $body);                    
                    

                    $pet->set('mail_body',$body);
                    $smtp_setting = \Drupal::config('smtp.settings');
                    $mail_from = $smtp_setting->get('smtp_from');
                    $params = array( 
                        'pet_from' => $mail_from, 
                        'pet_to' => $email
                    ); 
                    pet_send_one_mail($pet, $params);
                    ob_end_clean();
        	} else  {
				
				$donation_message = 'Transaction failed';
				$donation_message = 'Transaction success. Thank you for your donation.';
				$query = \Drupal::entityQuery('node')
                    ->condition('type', 'email_templates')
                    ->condition('field_pet_id', '29');
                    $templateid = reset($query->execute());
                    $templatedata = node::load($templateid);			
					if($cityname == 'mumbai' || $cityname == 'Mumbai'){
						$image = explode('/',$templatedata->field_header_image->entity->getFileUri());
						$image_path = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/'.$image[3];
						$mailtoken = 'trailwalkermumbai@oxfamindia.org';
						$second_image = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/image3.jpg';
					}elseif($cityname == 'bengaluru' || $cityname == 'Bengaluru'){
						$image = explode('/',$templatedata->field_banglore_image->entity->getFileUri());
						$image_path = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/'.$image[3];
						$mailtoken = 'trailwalkerbengaluru@oxfamindia.org';
						$second_image = 'http://'.\Drupal::request()->getHost().'/sites/default/files/emailtemplates/image3payments.jpg';
					}
                    
                    ob_start();
                    $pet = pet_load('29'); 
                   
                    $body = $templatedata->get('body')->value;
                    $body = str_replace('image_path',$image_path,$body);
                    $body = str_replace('donor_name', trim(strip_tags($values['first_name'])), $body);
					$body = str_replace('mailtoken', $mailtoken, $body);
					$body = str_replace('second_image', $second_image, $body);
					$body = str_replace('donation_amount', trim($values['price']), $body);
                    $body = str_replace('transaction_id', trim(strip_tags($values['order_id'])), $body);                    
                    
					
                    $pet->set('mail_body',$body);
                    $smtp_setting = \Drupal::config('smtp.settings');
                    $mail_from = $smtp_setting->get('smtp_from');
                   
                    $params = array( 
                        'pet_from' => $mail_from, 
                        'pet_to' => $email
                    ); 
                    
                    pet_send_one_mail($pet, $params);
                    ob_end_clean();
        	} 
        	
        	$email = trim(strip_tags($values['e_mail']));
        	$username = trim(strip_tags($values['first_name']));
        	
        	$donation_amount = 0;
        	$donation_amount = trim($values['price']);
            
            $donation_amount = trim($donation_amount);
        	
        
        	return true;
        }
        
        public function sendDonorEmails($values, $payment_status) {
       	           		        		        	        		        	
	        	if ($payment_status === "Success" || $payment_status === "TXN_SUCCESS") {
	        		$donation_id = $values['donation_id'];
	        		$donation_data = node::load($donation_id);
	        		$donor_message = $donation_data->get('field_donor_message')->value;
	        		$smtp_setting = \Drupal::config('smtp.settings');
		        	$mail_from = $smtp_setting->get('smtp_from');
		        	$donorname = $values['first_name'].' '.$values['last_name'];   
                	$donoremail = $values['e_mail'];
	        		if($values['donated_to_type'] == 'Team' || $values['donated_to_type'] == 'team'){
	        			$teamid = $values['donated_to_id'];
	        			$sql = 'SELECT user_id FROM `tw_team_members` where team_id = '.$teamid.' GROUP BY user_id';
	        			$query = db_query($sql);
	                	$records = $query->fetchAll(); 
	                	$teamname = str_replace('Team Name: ','',$values['teamname']); 
	                	$pet = pet_load('17');			        	
				        $body = $pet->get('mail_body')->getvalue()[0]['value'];	  
				        $body = str_replace('{team_name_data}', $teamname, $body);
				        $body1 = str_replace('message_data', $donor_message, $body);	        
	                	foreach ($records as $name => $e_mail) {
	                		$body = $body1;
	                		$email = '';
	                		$firstname = '';
	                		$user_id = $e_mail->user_id;
	                		$usersql = 'SELECT username,first_name,lastname,mail FROM `tw_users` where user_id = '.$user_id;
	                		$userquery = db_query($usersql);
	                		$userrecords = $userquery->fetch();
	                		$email = $userrecords->mail;      
				        	$firstname = $userrecords->first_name;

				        	ob_start();
				        	
				        	$body = str_replace('first_name', $firstname, $body);
				        	$body = str_replace('donor_name', $donorname, $body);
				        	
				        	$body = str_replace('amount', $values['price'], $body);
				        	

				        	$body = str_replace('donor_email', $donoremail, $body);	
				        				        			        	
				        	$pet->set('mail_body',$body);
				        	
				        	$params = array(
				        			'pet_from' => $mail_from,
				        			'pet_to' => $email
				        	);				        				        				        	
				        	pet_send_one_mail($pet, $params);				        
				        	ob_end_clean();
			        	}			        	
                	
        		}elseif($values['donated_to_type'] == 'User' || $values['donated_to_type'] == 'user'){        			
        			$user_id = $values['donated_to_id'];
        			$usersql = 'SELECT username,first_name,lastname,mail FROM `tw_users` where user_id = '.$user_id;
            		$userquery = db_query($usersql);
            		$userrecords = $userquery->fetch();            		
            		$email = $userrecords->mail;
            		ob_start();
		        	$pet = pet_load('7');			        	
		        	$body = $pet->get('mail_body')->getvalue()[0]['value'];
		        	$body = str_replace('first_name', $userrecords->first_name, $body);
		        	$body = str_replace('donor_name', $donorname, $body);
		        	$body = str_replace('donor_email', $donoremail, $body);	
		        	$body = str_replace('amount', $values['price'], $body);
		        	$body = str_replace('message_data', $donor_message, $body);		        	        			       
		        	$pet->set('mail_body',$body);
		        	
		        	$params = array(
		        			'pet_from' => $mail_from,
		        			'pet_to' => $email
		        	);			        			        				        				        
		        	pet_send_one_mail($pet, $params);				        
		        	ob_end_clean();            			
        		}        				        			        			        	
	        	}
	        	
        	//}
        	return true;
		}
		
		public function sendDonationExceptionEmail($values, $payment_status) {
        	$system_site_config = \Drupal::config('system.site');
        	$site_email = $system_site_config->get('mail');
        	if ($payment_status === "Success") {
        		$message = "success";
        	} else if($payment_status === "Aborted") {
        		$message = "failed";
        	} else if($payment_status === "Failure") {
        		$message = "failed";
        	} else if($payment_status === "TXN_SUCCESS") {
        		$message = "success";
        	} else if($payment_status === "Failed") {
        		$message = "failed";
        	} else if($payment_status === "In progress") {
        		$message = "In progress";
        	}
        	if ($message == 'success') {
        		$donation_message = 'Transaction success. Thank you for your donation.';
        	} else if ($message == 'failed') {
        		$donation_message = 'Transaction failed';
        	} else if ($message == 'In progress') {
        		$donation_message = 'Transaction In progress. Please wait for admin approval.';
        	}
        	$email = trim(strip_tags($values['e_mail']));
        	$username = trim(strip_tags($values['first_name']));
        	
        	$donation_amount = 0;
        	$donation_amount = trim($values['price']);
           
            $donation_amount = trim($donation_amount);
        	ob_start();
        	$pet = pet_load(19);
        
        	$body = $pet->get('mail_body')->getvalue()[0]['value'];
        	$body = str_replace('first_name', $username, $body);
        	$body = str_replace('e_mail', $email, $body);
        	$body = str_replace('donated_to', $values['donated_to'], $body);
        	$body = str_replace('donation_amount', $donation_amount, $body);
        	$body = str_replace('donation_message', $donation_message, $body);
        
        	$pet->set('mail_body',$body);
        	$smtp_setting = \Drupal::config('smtp.settings');
        	$mail_from = $smtp_setting->get('smtp_from');
        	$params = array(
        			'pet_from' => $mail_from,
        			'pet_to' => $email
        	);
        
        	//send to user
        	pet_send_one_mail($pet, $params);
        
        	ob_end_clean();
        
        	return true;
        }
        
        public function donationPostCreate($values, $payment_status) {
        	if ($payment_status === "Success") {
        		$message = "success";
        	} else if($payment_status === "Aborted") {
        		$message = "failed";
        	} else if($payment_status === "Failure") {
        		$message = "failed";
        	} else if($payment_status === "TXN_SUCCESS") {
        		$message = "success";
        	} else {
        		$message = "failed";
        	}
        	if ($message == 'success') {
        		$donor_name = trim(strip_tags($values['donor_name']));
        		$donated_to = trim(strip_tags($values['donated_to']));
        		$donation_amount = 0;
        		$donation_amount = trim($values['price']);
        		$message_content = "Donor Name : $donor_name <br>
        		Donated to : $donated_to <br>
        		Amount : $donation_amount";
        		$current_user_id = \Drupal::currentUser()->id();
        		$memberId = $values['donated_to_id'];
        		$city_id = $values['donated_to_city'];
        		$donated_to_type = $values['donated_to_type'];
        		if ($donated_to_type == 'User') {
        			$Data = db_insert('tbl_post')
        			->fields(
        					array(
        							'type' => 'user_post',
        							'user_id' => $memberId,
        							'creator_id' => $current_user_id,
        							'post_type' => 'normal',
        							'message' => $message_content,
        							'created' =>  date('Y-m-d H:i:s'),
        							'city_id' => $city_id
        					)
        			)->execute();
        		} else {
        			$teamid = $values['donated_to_id'];
        			$Data = db_insert('tbl_post')
        			->fields(
        					array(
        							'type' => 'team_post',
        							'user_id' => 0,
        							'creator_id' => $current_user_id,
        							'team_id' => $teamid,
        							'city_id' => $city_id,
        							'post_type' => 'normal',
        							'message' => $message_content,
        							'created' =>  date('Y-m-d H:i:s')
        					)
        			)->execute();
        		}
        	} else {
        		
        	}
        	return true;
        }
        public function sendDonationAdminEmail($values, $payment_status){	
						
				$donationid = $values['donation_id'];
				$donationdata = node::load($donationid);				
				$paymentfor = $donationdata->get('field_payment_for')->value;
				
				$donorname = $values['first_name']." ".$values['last_name'];
				$donationto = $donationdata->get('field_donate_to_type')->value;	
				$teamname = '';					
				if($donationdata->get('field_donate_to_type')->value == 'teams' || $donationdata->get('field_donate_to_type')->value == 'team'){										
					$teamid = $donationdata->get('field_donation_to_team')->target_id;					
					$teamdata = node::load($teamid);	
					if(!empty($teamdata) && $teamdata !=''){				
						$teamname = $teamdata->get('title')->value;
					}					
					$type = 'Team Name';
				}else{
					$userid = $donationdata->get('field_donation_to_user')->target_id;
					$userdata = user::load($userid);
					$username = $userdata->get('name')->value;
					$teamname = $username;
					$type = 'Walker Name';
				}	
																																	
				$system_site_config = \Drupal::config('system.site');
				$site_email = $system_site_config->get('mail');
				if ($payment_status === "Success") {
					$message = "success";
				} else if($payment_status === "Aborted") {
					$message = "failed";
				} else if($payment_status === "Failure" || $payment_status === "TXN_FAILURE") {
					$message = "failed";
				} else if($payment_status === "TXN_SUCCESS") {
					$message = "success";
				} else {
					$message = "failed";
				}
				$paymemt_status = $message;
				$ccavenue_config = \Drupal::config('donations.adminmails');
				$emailids = explode(',',$ccavenue_config->get('mails'));						
				
				// get  mail template 
				$query = \Drupal::entityQuery('node')
						->condition('type', 'email_templates')
						->condition('field_pet_id', '33');
				$templateid = reset($query->execute());
				$templatedata = node::load($templateid);
				foreach($emailids as $emails){
					ob_start();
					$pet = pet_load(33);			
					$body = $templatedata->get('body')->value;			
					$body = str_replace('donorname', $donorname, $body);
					$body = str_replace('donationto', $donationto, $body);
					$body = str_replace('team_name', $teamname, $body);
					$body = str_replace('teamname', $type, $body);
					$body = str_replace('paymentfor', $paymentfor, $body);
					$body = str_replace('paymentstatus', $paymemt_status, $body);
					$body = str_replace('transactionid', $values['order_id'], $body);  
					$body = str_replace('amount', $donationdata->get('field_mer_amount_')->value, $body);
					//print_r($body);exit; 									      									
					$pet->set('mail_body',$body);
					$smtp_setting = \Drupal::config('smtp.settings');
					$mail_from = $smtp_setting->get('smtp_from');
					$params = array(
							'pet_from' => $mail_from,
							'pet_to' => $emails
					);			
					//send to user
					pet_send_one_mail($pet, $params);				
					ob_end_clean();
				}
				return true;				
		}
		public function getstates($country_id){
            $records = array();
            if($country_id !='' && $country_id > 0){
                $sql  = 'SELECT id,name FROM countries_states where parent='.$country_id;
                $query = db_query($sql);
                $records = $query->fetchAll();
            }   

			echo "<option value=''>Select State</option>";
			if (count($records)>0) {
				foreach($records as $state){
					echo "<option value='".$state->id."'>".$state->NAME."</option>";
				}
			}
			echo "</select>";
			exit;
    	}
    }
    function SaveDeletedUserData($account){
	$user_uid = $account->id();
		$user_name = $account->getUsername();
		$user_email = $account->getEmail();
		$user_created=$account->getCreatedTime();
		$user_phone = $account->field_mobile_number->getValue()[0]['value'];
		$order_id = 'TWVirtualdonate'.$user_uid;
		$user_address= $account->field_address->value;
		$user_event_type= $account->field_event_type->value;

		$event_id= $account->field_event_name->target_id;
  $event_details = Node::load($event_id);
  $user_event_name = $event_details->title->value;


  $user_city= $account->field_city->value;

  $user_company= $account->field_company_name->value;
$date_of_birth = $account->field_date_of_birth->value;
  $user_dob = date("d-m-Y", strtotime($date_of_birth)); 
$user_desig= $account->field_designation->value;
$user_fname= $account->field_first_name->value;
  $user_lname= $account->field_last_name->value;


  $user_country= $account->get('field_country')->getValue()[0]['country_code'];

  $user_state= $account->get('field_country')->getValue()[0]['administrative_area'];
   $user_gender= $account->field_gender->value;
$user_nationality= $account->field_nationality->value;
  
  $user_pan= $account->field_pan_card->value;
  
  
  $user_pincode= $account->field_pincode->value;
  
  $user_utm= $account->field_source_utm_code->value;
$user_t_c= $account->field_t_c->value;
$user_gdp= $account->field_gdp->value;
$user_gdpr= $account->field_gdpr->value;
		$query = \Drupal::database();
        $query ->insert('user_deleted_data')
        ->fields(array(
        	'uid'=>$user_uid,
        	'username'=>$user_name,
        	'email_id'=>$user_email,
        	'mobile_no'=>$user_phone,
        	'transaction_id'=>$order_id,
        	'address'=>$user_address,
        	'event_type'=>$user_event_type,
        	'event_name'=>$user_event_name,
        	'city'=>$user_city,
        	'company_name'=>$user_company,
        	'country'=>$user_country,
        	'date_of_birth'=>$user_dob,
        	'designation'=>$user_desig,
        	'first_name'=>$user_fname,
        	'last_name'=>$user_lname,
        	'gender'=>$user_gender,
        	'nationality'=>$user_nationality,
        	'pan_card'=>$user_pan,
        	'pin_code'=>$user_pincode,
        	'utm_code'=>$user_utm,
        	't_c'=>$user_t_c,
        	'gdp'=>$user_gdp,
        	'gdpr'=>$user_gdpr,
        	'state' => $user_state,
        	'creation_date'=> $user_created
    ))
        ->execute();
        return(0);
}
?>
