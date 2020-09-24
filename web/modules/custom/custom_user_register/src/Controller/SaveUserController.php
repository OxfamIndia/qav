<?php
/**
 * @file
 * Contains \Drupal\custom_user_register\Controller\SaveUserController.
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

class SaveUserController extends ControllerBase {

	public function SaveUser(){
    	$today_start_ts = strtotime(date("Y-m-d H:i:s",time()));
        $nids = \Drupal::entityQuery('node')
        ->condition('type','events')
        ->condition('field_end_date',$today_start_ts,'<')
        ->execute();
        $database = \Drupal::database();
        $query = $database->select('user__field_event_name', 'u');
        $query->condition('u.field_event_name_target_id',$nids,'IN');
        $query->fields('u', ['entity_id']);
        $result = $query->execute()->fetchAll();

        foreach ($result as $key => $value) {
       $user_id = $value->entity_id;
       $account = User::load($user_id);
       GetUserData($account);
    }
        
	}


	
}
function GetUserData($account){
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
        $query ->insert('user_pre_registered_data')
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
