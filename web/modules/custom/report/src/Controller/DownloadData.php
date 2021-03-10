<?php
namespace Drupal\report_download\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception; 
use \Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;
//require 'vendor/autoload.php'; 

/**
 * Defines Download Data class.
 */
class DownloadData extends ControllerBase {
  /**
   * Download portfolio search data
   */
  public function download_data() {
     $start_date = '';
    $last_date = '';
    $html = '';
    $sd2 = '';
    $ed2 ='';
    if(isset($_GET['start_date'])){
      $start_date = $_GET['start_date'];
      $last_date =$_GET['end_date'];
    }
    if($start_date!='' && $start_date!=''){
      $sd = date('d-m-Y',$start_date);
      $sd2 = date('Y-m-d',$start_date);
      $ed = date('d-m-Y',$last_date);
      $ed2 = date('Y-m-d',$last_date);
      $user_id= \Drupal::currentUser()->id();
      
      $database = \Drupal::database();
      $query = $database->select('users_field_data', 'u');
 
      // Add extra detail to this query object: a condition, fields and a range.
      // get all users with respected to start date and end date
      $query->condition('u.uid', 0, '<>');
      $query->condition('created', array($start_date,  $last_date), 'BETWEEN');
      $query->condition('status', 1, '=');
      $query->LeftJoin('user__field_first_name', 'ufname', 'u.uid = ufname.entity_id');
      $query->LeftJoin('user__field_last_name', 'ulname', 'u.uid = ulname.entity_id');
      $query->LeftJoin('user__field_mobile_number', 'umob', 'u.uid = umob.entity_id');
      $query->LeftJoin('user__field_webform', 'uwebform', 'u.uid = uwebform.entity_id');
      $query->fields('u', ['uid', 'name', 'mail','status', 'created']);
      $query->fields('ufname', ['field_first_name_value']);
      $query->fields('ulname', ['field_last_name_value']);
      $query->fields('umob', ['field_mobile_number_value']);
      $query->fields('uwebform', ['field_webform_value']);
      $result = $query->execute()->fetchAll();

      $consu_data =array(); // define a blank array that saves the complete data
      if($result){
        foreach ($result as $key => $value) {
          if($value->status ==1){
            $consu_data[$key]['user_id'] = $value->uid;
            $consu_data[$key]['user_name'] = $value->name;
            $consu_data[$key]['mail_id'] = $value->mail;
            $consu_data[$key]['status'] = $value->status;
            $consu_data[$key]['regist_date'] = date('d/m/Y', $value->created);
            $consu_data[$key]['user_fname']= $value->field_first_name_value;
            $consu_data[$key]['user_lname']= $value->field_last_name_value;
            $consu_data[$key]['user_mobile_number']= $value->field_mobile_number_value;
            $consu_data[$key]['user_first_webform_id']= $value->field_webform_value;
            // get all other data which saves in webform and content type with related to all activity and submissio related data
            $consu_data= GetFirstWebformData($consu_data, $key, $value->uid);
          }
        }
      }

    }
        
    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=consumer-report.csv");
    if (!empty($consu_data)) {
      
      /* Write data in file: START */
      $file = fopen("php://output", "w");

      fputcsv( $file,  ['User Id','First Name', 'Last Name', 'User Name', 'Email Id', 'Status', 'Registration Date', 'Insitution', 'Address', 'City', 'State', 'Country', 'DOB', 'Pin Code', 'Nationality', 'Employee Id', 'Mobile Number', 'Submission ID1', 'Event1 Name', 'Event1 Type', 'Day1 Distance', 'Day1 Pic', 'Day2 Distance', 'Day2 Pic', 'Day3 Distance', 'Day3 Pic', 'Day4 Distance', 'Day4 Pic', 'Day5 Distance', 'Day5 Pic', 'Day6 Distance', 'Day6 Pic', 'Day7 Distance', 'Day7 Pic', 'Day8 Distance', 'Day8 Pic', 'Day9 Distance', 'Day9 Pic', 'Day10 Distance', 'Day10 Pic', 'Total Event1 Distance', 'Event1 Payment',  'Submission ID2','Event2 Name', 'Event2 Type', 'Day1 Distance', 'Day1 Pic', 'Day2 Distance', 'Day2 Pic', 'Day3 Distance', 'Day3 Pic', 'Day4 Distance', 'Day4 Pic', 'Day5 Distance', 'Day5 Pic', 'Day6 Distance', 'Day6 Pic', 'Day7 Distance', 'Day7 Pic', 'Day8 Distance', 'Day8 Pic', 'Day9 Distance', 'Day9 Pic', 'Day10 Distance', 'Day10 Pic', 'Total Event2 Distance', 'Event2 Payment']);
      foreach ($consu_data as $key => $value) {
       // $line_data = [$value[5], $value[2], $value[4], $value[0], $value[6], $value[1],$value['created']];
        if($value['user_empid'] == ''){
        $line_data = [$value['user_id'], $value['user_fname'], $value['user_lname'], $value['user_name'], $value['mail_id'], $value['status'], $value['regist_date'], $value['institution'], $value['user_address'], $value['user_city'], $value['user_state'], $value['user_country'], $value['user_dob'], $value['user_pincode'], $value['user_nationality'], $value['user_empid'], $value['user_mobile_number']];

         if(isset($value['user_activity'])){
          
         foreach ($value['user_activity'] as $activitykey => $value['user_activity']) {
          if(!isset($value['user_activity']['user_day1_dist'])){
            $value['user_activity']['user_day1_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day2_dist'])){
            $value['user_activity']['user_day2_dist'] = '';
          }

          if(!isset($value['user_activity']['user_day3_dist'])){
            $value['user_activity']['user_day3_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day4_dist'])){
            $value['user_activity']['user_day4_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day5_dist'])){
            $value['user_activity']['user_day5_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day6_dist'])){
            $value['user_activity']['user_day6_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day7_dist'])){
            $value['user_activity']['user_day7_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day8_dist'])){
            $value['user_activity']['user_day8_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day9_dist'])){
            $value['user_activity']['user_day9_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day10_dist'])){
            $value['user_activity']['user_day10_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day1_pic'])){
            $value['user_activity']['user_day1_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day2_pic'])){
            $value['user_activity']['user_day2_pic'] = '';
          }

          if(!isset($value['user_activity']['user_day3_pic'])){
            $value['user_activity']['user_day3_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day4_pic'])){
            $value['user_activity']['user_day4_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day5_pic'])){
            $value['user_activity']['user_day5_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day6_pic'])){
            $value['user_activity']['user_day6_pic'] = '';
          }

          if(!isset($value['user_activity']['user_day7_pic'])){
            $value['user_activity']['user_day7_pic'] = '';
          }if(!isset($value['user_activity']['user_day8_pic'])){
            $value['user_activity']['user_day8_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day9_pic'])){
            $value['user_activity']['user_day9_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day10_pic'])){
            $value['user_activity']['user_day10_pic'] = '';
          }
          if(!isset($value['user_activity']['user_payment_status'])){
            $value['user_activity']['user_payment_status'] = '';
          }

            $line_data2 = [$value['user_activity']['second_submission_id'],$value['user_activity']['user_event_name'],$value['user_activity']['user_challenge_type'], $value['user_activity']['user_day1_dist'], $value['user_activity']['user_day1_pic'], $value['user_activity']['user_day2_dist'], $value['user_activity']['user_day2_pic'], $value['user_activity']['user_day3_dist'], $value['user_activity']['user_day3_pic'], $value['user_activity']['user_day4_dist'], $value['user_activity']['user_day4_pic'], $value['user_activity']['user_day5_dist'], $value['user_activity']['user_day5_pic'], $value['user_activity']['user_day6_dist'], $value['user_activity']['user_day6_pic'], $value['user_activity']['user_day7_dist'], $value['user_activity']['user_day7_pic'], $value['user_activity']['user_day8_dist'], $value['user_activity']['user_day8_pic'], $value['user_activity']['user_day9_dist'], $value['user_activity']['user_day9_pic'], $value['user_activity']['user_day10_dist'], $value['user_activity']['user_day10_pic'], $value['user_activity']['user_total_dist'], $value['user_activity']['user_payment_status']];
            $line_data = array_merge($line_data,$line_data2);
           
         }
       }
        
      fputcsv( $file, $line_data);
      }
        
      }
      fclose($file);
      /* Write data in file: END */
    }
     exit;
  }



/**
   * Download Activity search data
   */
  public function activity_download_data() {
     $start_date = '';
    $last_date = '';
    $html = '';
    $sd2 = '';
    $ed2 ='';
    if(isset($_GET['start_date'])){
      $start_date = $_GET['start_date'];
      $last_date =$_GET['end_date'];
    }
    if($start_date!='' && $start_date!=''){
      $sd = date('d-m-Y',$start_date);
      $sd2 = date('Y-m-d',$start_date);
      $ed = date('d-m-Y',$last_date);
      $ed2 = date('Y-m-d',$last_date);
      $user_id= \Drupal::currentUser()->id();
      
      $database = \Drupal::database();
      $query = $database->select('users_field_data', 'u');
 
      // Add extra detail to this query object: a condition, fields and a range.
      // get all users with respected to start date and end date
      $query->condition('u.uid', 0, '<>');
      $query->condition('created', array($start_date,  $last_date), 'BETWEEN');
      $query->condition('status', 1, '=');
      $query->LeftJoin('user__field_first_name', 'ufname', 'u.uid = ufname.entity_id');
      $query->LeftJoin('user__field_last_name', 'ulname', 'u.uid = ulname.entity_id');
      $query->LeftJoin('user__field_mobile_number', 'umob', 'u.uid = umob.entity_id');
      $query->LeftJoin('user__field_webform', 'uwebform', 'u.uid = uwebform.entity_id');
      $query->fields('u', ['uid', 'name', 'mail','status', 'created']);
      $query->fields('ufname', ['field_first_name_value']);
      $query->fields('ulname', ['field_last_name_value']);
      $query->fields('umob', ['field_mobile_number_value']);
      $query->fields('uwebform', ['field_webform_value']);
      $result = $query->execute()->fetchAll();

      $consu_data =array(); // define a blank array that saves the complete data
      if($result){
        foreach ($result as $key => $value) {
          if($value->status ==1){
            $consu_data[$key]['user_id'] = $value->uid;
            $consu_data[$key]['user_name'] = $value->name;
            $consu_data[$key]['mail_id'] = $value->mail;
            $consu_data[$key]['status'] = $value->status;
            $consu_data[$key]['regist_date'] = date('d/m/Y', $value->created);
            $consu_data[$key]['user_fname']= $value->field_first_name_value;
            $consu_data[$key]['user_lname']= $value->field_last_name_value;
            $consu_data[$key]['user_mobile_number']= $value->field_mobile_number_value;
            $consu_data[$key]['user_first_webform_id']= $value->field_webform_value;
            // get all other data which saves in webform and content type with related to all activity and submissio related data
            $consu_data= GetFirstWebformDataActivity($consu_data, $key, $value->uid);
          }
        }
      }

    }
        
    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=consumer-report.csv");
    if (!empty($consu_data)) {
      
      /* Write data in file: START */
      $file = fopen("php://output", "w");
      fputcsv( $file,  ['User Id','First Name', 'Last Name', 'User Name', 'Email Id', 'Status', 'Registration Date', 'Mobile Number', 'Submission ID', 'Address' , 'City' , 'Institution' , 'Country' , 'State' , 'Nationality' , 'Empployee ID' , 'DOB' , 'Pin Code' , 'Gender' , 'Challenge Slot ID', 'Payment Status', 'Challenge Type', 'Donation Money', 'Amount', 'Total Response', 'Day1 Distance', 'Day1 Pic', 'Day2 Distance', 'Day2 Pic', 'Day3 Distance', 'Day3 Pic', 'Day4 Distance', 'Day4 Pic', 'Day5 Distance', 'Day5 Pic', 'Day6 Distance', 'Day6 Pic', 'Day7 Distance', 'Day7 Pic', 'Day8 Distance', 'Day8 Pic', 'Day9 Distance', 'Day9 Pic', 'Day10 Distance', 'Day10 Pic']);
      foreach ($consu_data as $key => $value) {
        $line_data = [$value['user_id'], $value['user_fname'], $value['user_lname'], $value['user_name'], $value['mail_id'], $value['status'], $value['regist_date'], $value['user_mobile_number'], $value['user_first_webform_id'], $value['user_address'], $value['user_city'], $value['institution'], $value['user_country'], $value['user_state'], $value['user_nationality'], $value['user_empid'], $value['user_dob'], $value['user_pincode'], $value['user_gender'], $value['user_challenge_slot'], $value['payment_status'], $value['challenge_type'], $value['donation_money'], $value['amount'], $value['total_response']];
         if(isset($value['user_activity'])){
          if(!isset($value['user_activity']['user_day1_dist'])){
            $value['user_activity']['user_day1_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day2_dist'])){
            $value['user_activity']['user_day2_dist'] = '';
          }

          if(!isset($value['user_activity']['user_day3_dist'])){
            $value['user_activity']['user_day3_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day4_dist'])){
            $value['user_activity']['user_day4_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day5_dist'])){
            $value['user_activity']['user_day5_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day6_dist'])){
            $value['user_activity']['user_day6_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day7_dist'])){
            $value['user_activity']['user_day7_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day8_dist'])){
            $value['user_activity']['user_day8_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day9_dist'])){
            $value['user_activity']['user_day9_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day10_dist'])){
            $value['user_activity']['user_day10_dist'] = '';
          }
          if(!isset($value['user_activity']['user_day1_pic'])){
            $value['user_activity']['user_day1_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day2_pic'])){
            $value['user_activity']['user_day2_pic'] = '';
          }

          if(!isset($value['user_activity']['user_day3_pic'])){
            $value['user_activity']['user_day3_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day4_pic'])){
            $value['user_activity']['user_day4_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day5_pic'])){
            $value['user_activity']['user_day5_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day6_pic'])){
            $value['user_activity']['user_day6_pic'] = '';
          }

          if(!isset($value['user_activity']['user_day7_pic'])){
            $value['user_activity']['user_day7_pic'] = '';
          }if(!isset($value['user_activity']['user_day8_pic'])){
            $value['user_activity']['user_day8_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day9_pic'])){
            $value['user_activity']['user_day9_pic'] = '';
          }
          if(!isset($value['user_activity']['user_day10_pic'])){
            $value['user_activity']['user_day10_pic'] = '';
          }
          if(!isset($value['user_activity']['user_payment_status'])){
            $value['user_activity']['user_payment_status'] = '';
          }

          $line_data2 = [$value['user_activity']['user_day1_dist'], $value['user_activity']['user_day1_pic'], $value['user_activity']['user_day2_dist'], $value['user_activity']['user_day2_pic'], $value['user_activity']['user_day3_dist'], $value['user_activity']['user_day3_pic'], $value['user_activity']['user_day4_dist'], $value['user_activity']['user_day4_pic'], $value['user_activity']['user_day5_dist'], $value['user_activity']['user_day5_pic'], $value['user_activity']['user_day6_dist'], $value['user_activity']['user_day6_pic'], $value['user_activity']['user_day7_dist'], $value['user_activity']['user_day7_pic'], $value['user_activity']['user_day8_dist'], $value['user_activity']['user_day8_pic'], $value['user_activity']['user_day9_dist'], $value['user_activity']['user_day9_pic'], $value['user_activity']['user_day10_dist']];
          $line_data = array_merge($line_data,$line_data2);  
        }
        fputcsv( $file, $line_data);  
      }
      fclose($file);
      /* Write data in file: END */
    }
     exit;
  }


}
function GetFirstWebformDataActivity($consu_data, $key, $uid){
  $first_webform_sub_id= $consu_data[$key]['user_first_webform_id'];
  $webform_submission = WebformSubmission::load($first_webform_sub_id);
  if(!empty($webform_submission)){
    $first_webform_data = $webform_submission->getData();
  }
  /*kint($first_webform_data);
  die();*/
  $consu_data[$key]['user_address'] = '';
  $consu_data[$key]['user_city'] = '';
  $consu_data[$key]['institution'] = '';
  $consu_data[$key]['user_country'] = '';
  $consu_data[$key]['user_state'] = '';
  $consu_data[$key]['user_nationality'] ='';
  $consu_data[$key]['user_empid']= '';
  $consu_data[$key]['user_dob'] = '';
  $consu_data[$key]['user_pincode'] = '';
  $consu_data[$key]['user_gender'] = '';
  $consu_data[$key]['user_challenge_slot'] = '';
  $consu_data[$key]['payment_status'] = '';
  $consu_data[$key]['challenge_type'] = '';
  $consu_data[$key]['donation_money'] = '';
  $consu_data[$key]['amount'] = '';
  $consu_data[$key]['total_response'] = '';
  if(isset($first_webform_data['address'])){
    $consu_data[$key]['user_address']= $first_webform_data['address'];
  }

  if(isset($first_webform_data['city'])){
    $consu_data[$key]['user_city']= $first_webform_data['city'];
  }
  if(isset($first_webform_data['institution'])){
    $consu_data[$key]['institution']= $first_webform_data['institution'];
  }
  
  if(isset($first_webform_data['country'])){
  $consu_data[$key]['user_country']= $first_webform_data['country']['country_code'];
  $consu_data[$key]['user_state']= $first_webform_data['country']['administrative_area'];
  }
    if($consu_data[$key]['user_country'] ==''){
      $consu_data[$key]['user_country'] = 'IN';
    }
    $consu_data[$key]['user_country'] = \Drupal::service('country_manager')->getList()[$consu_data[$key]['user_country']]->__toString();
    if(isset($first_webform_data['nationality'])){
     $consu_data[$key]['user_nationality'] =$first_webform_data['nationality'];
    }
    if(isset($first_webform_data['employee_number'])){
    $consu_data[$key]['user_empid']= $first_webform_data['employee_number'];
  }

  if(isset($first_webform_data['date_of_birth'])){
    $date_of_birth = $first_webform_data['date_of_birth'];
    $newDobDate = date("d-m-Y", strtotime($date_of_birth));  
    $consu_data[$key]['user_dob']= $newDobDate;
  }
  if(isset($first_webform_data['zip_code'])){
    $consu_data[$key]['user_pincode']= $first_webform_data['zip_code'];
  }
    
    if(isset($first_webform_data['gender'])){
    $consu_data[$key]['user_gender']= $first_webform_data['gender'];
  }
   if(isset($first_webform_data['challenge_slot'])){
    $consu_data[$key]['user_challenge_slot']= $first_webform_data['challenge_slot'];
  }
  if(isset($first_webform_data['payment_status'])){
  $consu_data[$key]['payment_status']= $first_webform_data['payment_status'];
}
if(isset($first_webform_data['challenge_type'])){
  $consu_data[$key]['challenge_type']= $first_webform_data['challenge_type'];
}
if(isset($first_webform_data['donation_money'])){
  $consu_data[$key]['donation_money']= $first_webform_data['donation_money'];
}
if(isset($first_webform_data['amount'])){
  $consu_data[$key]['amount']= $first_webform_data['amount'];
}
if(isset($first_webform_data['total_response'])){
  $consu_data[$key]['total_response']= $first_webform_data['total_response'];
}
  $nids = \Drupal::entityQuery('node')
  ->condition('type','daily_activity')
  ->condition('uid',$uid)
  ->condition('created',1615161599, '>')
  ->execute();
  $activity_count = 1;
  foreach ($nids as $nkey => $nvalue) {
    $node = \Drupal\node\Entity\Node::load($nvalue);
    $walker_image1 =$node->get('field_distance_screenshot')->getValue();
    if(!empty($walker_image1)){
      $walker_image1 =$walker_image1[0]['target_id'];
      $file1 = File::load($walker_image1);
      $image_uri1 = $file1->getFileUri();
      $walker_image_url1 = file_create_url($image_uri1);
      $consu_data[$key]['user_activity']['user_day'.$activity_count.'_dist']=$node->field_distance->value;
      $consu_data[$key]['user_activity']['user_day'.$activity_count.'_pic']= $walker_image_url1.'?nid='.$nvalue;
      $activity_count = $activity_count+1;
    }
  }
  return $consu_data;
}
function GetFirstWebformData($consu_data, $key, $uid){
  //die('hee');
  $webform_submission = WebformSubmission::load($consu_data[$key]['user_first_webform_id']);
  if(!empty($webform_submission)){
    $first_webform_data = $webform_submission->getData();
  }
  $consu_data[$key]['user_address'] = '';
  $consu_data[$key]['user_city'] = '';
  $consu_data[$key]['institution'] = '';
  $consu_data[$key]['user_country'] = '';
  $consu_data[$key]['user_state'] = '';
  $consu_data[$key]['user_nationality'] ='';
  $consu_data[$key]['user_empid']= '';
  $consu_data[$key]['user_dob'] = '';
  $consu_data[$key]['user_pincode'] = '';
  $consu_data[$key]['user_gender'] = '';

  if(isset($first_webform_data['address'])){
    $consu_data[$key]['user_address']= $first_webform_data['address'];
  }

  if(isset($first_webform_data['user_city'])){
    $consu_data[$key]['user_city']= $first_webform_data['user_city'];
  }
  if(isset($first_webform_data['institution'])){
    $consu_data[$key]['institution']= $first_webform_data['institution'];
  }
  //$consu_data[$key]['user_institution']= $first_webform_data['institution'];
  
  if(isset($first_webform_data['country'])){
  $consu_data[$key]['user_country']= $first_webform_data['country']['country_code'];
  $consu_data[$key]['user_state']= $first_webform_data['country']['administrative_area'];
}
  if($consu_data[$key]['user_country'] ==''){
    $consu_data[$key]['user_country'] = 'IN';
  }
  $consu_data[$key]['user_country'] = \Drupal::service('country_manager')->getList()[$consu_data[$key]['user_country']]->__toString();
  if(isset($first_webform_data['nationality'])){
   $consu_data[$key]['user_nationality'] =$first_webform_data['nationality'];
  }
  if(isset($first_webform_data['employee_number'])){
  $consu_data[$key]['user_empid']= $first_webform_data['employee_number'];
}

if(isset($first_webform_data['date_of_birth'])){
  $date_of_birth = $first_webform_data['date_of_birth'];
  $newDobDate = date("d-m-Y", strtotime($date_of_birth));  
  $consu_data[$key]['user_dob']= $newDobDate;
}
if(isset($first_webform_data['zip_code'])){
  $consu_data[$key]['user_pincode']= $first_webform_data['zip_code'];
}
  
  if(isset($first_webform_data['gender'])){
  $consu_data[$key]['user_gender']= $first_webform_data['gender'];
}
  $account = User::load($uid);
  $db = \Drupal::database();
  $query = $db->select('webform_submission', 'wf'); 
  $query->fields('wf', ['sid']);
  $query->condition('wf.webform_id', 'subscribers');
  $query->condition('wf.uid', $uid);
  $result = $query->execute()->fetchAll();
  foreach ($result as $secondkey => $secondvalue) {
    $second_webform_id = $secondvalue->sid;
    $second_webform_submission = WebformSubmission::load($second_webform_id);
    $second_webform_data = $second_webform_submission->getData();
    $event_id= $second_webform_data['challenge_slot'];
    $event_details = Node::load($event_id);
    $consu_data[$key]['user_activity'][$event_id]['user_event_name'] = $event_details->title->value;
    $consu_data[$key]['user_activity'][$event_id]['user_challenge_type']=$second_webform_data['challenge_type'];
    $consu_data[$key]['user_activity'][$event_id]['user_total_dist']=$second_webform_data['completed_distance'];
    $consu_data[$key]['user_activity'][$event_id]['user_payment_status']=$second_webform_data['payment_status'];
    $consu_data[$key]['user_activity'][$event_id]['second_submission_id'] = $second_webform_id;
    $nids = \Drupal::entityQuery('node')
    ->condition('type','daily_activity')
    ->condition('uid',$uid)
    ->condition('field_slot',$second_webform_id)
    ->execute();
    $activity_count = 1;
    foreach ($nids as $nkey => $nvalue) {
      $node = \Drupal\node\Entity\Node::load($nvalue);
      $walker_image1 =$node->get('field_distance_screenshot')->getValue();
      if(!empty($walker_image1)){
        $walker_image1 =$walker_image1[0]['target_id'];
        $file1 = File::load($walker_image1);
        $image_uri1 = $file1->getFileUri();
        $walker_image_url1 = file_create_url($image_uri1);
        $consu_data[$key]['user_activity'][$event_id]['user_day'.$activity_count.'_dist']=$node->field_distance->value;
        $consu_data[$key]['user_activity'][$event_id]['user_day'.$activity_count.'_pic']= $walker_image_url1;
        $activity_count = $activity_count+1;
      }
    }
  }
  return $consu_data;
}