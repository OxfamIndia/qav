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
//require 'vendor/autoload.php'; 

/**
 * Defines Company Download Data class.
 */
class CompanyDownloadData extends ControllerBase {
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
      $query->condition('u.uid', 0, '<>');
      $query->fields('u', ['uid', 'name', 'mail','status', 'created']);
      $result = $query->execute()->fetchAll();
      $consu_data =array();
      if($result){
        foreach ($result as $key => $value) {
          if($key>0){
            if( $value->status ==1){
         $consu_data[$key]['user_id'] = $value->uid;
         $consu_data[$key]['user_name'] = $value->name;
         $consu_data[$key]['mail_id'] = $value->mail;
         $consu_data[$key]['status'] = $value->status;
         $consu_data[$key]['regist_date'] = date('d/m/Y', $value->created);
         $consu_data= CompanyGetUserData($consu_data, $key, $value->uid);

       }
       }


        }
      }

    }
        
    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=consumer-company-report.csv");
    if (!empty($consu_data)) {
      
      /* Write data in file: START */
      $file = fopen("php://output", "w");

      fputcsv( $file,  ['User Id', 'User Name', 'Email Id', 'Status', 'Registration Date', 'Address', 'City', 'Company', 'Country', 'State', 'DOB', 'Event Type', 'Event Name', 'Designation', 'First Name', 'Last Name', 'Pin Code', 'Nationality', 'Employee Id', 'Mobile Number', 'Day1 Ditance', 'Day1 Pic', 'Day2 Ditance', 'Day2 Pic', 'Day3 Ditance', 'Day3 Pic', 'Day4 Ditance', 'Day4 Pic', 'Day5 Ditance', 'Day5 Pic', 'Day6 Ditance', 'Day6 Pic', 'Day7 Ditance', 'Day7 Pic', 'Day8 Ditance', 'Day8 Pic', 'Day9 Ditance', 'Day9 Pic', 'Day10 Ditance', 'Day10 Pic','Total Walk Distance', 'Payment Mode', 'Payment Status']);
      foreach ($consu_data as $value) {
       // $line_data = [$value[5], $value[2], $value[4], $value[0], $value[6], $value[1],$value['created']];
        if($value['user_empid'] != 'NULL'){
        $line_data = [$value['user_id'], $value['user_name'], $value['mail_id'], $value['status'], $value['regist_date'], $value['user_address'], $value['user_city'], $value['user_company'], $value['user_country'], $value['user_state'], $value['user_dob'], $value['user_event_type'], $value['user_event_name'], $value['user_desig'], $value['user_fname'], $value['user_lname'], $value['user_pincode'], $value['user_nationality'], $value['user_empid'], $value['user_mobile_number'], $value['user_day1_dist'], $value['user_day1_pic'], $value['user_day2_dist'], $value['user_day2_pic'], $value['user_day3_dist'], $value['user_day3_pic'], $value['user_day4_dist'], $value['user_day4_pic'], $value['user_day5_dist'], $value['user_day5_pic'], $value['user_day6_dist'], $value['user_day6_pic'], $value['user_day7_dist'], $value['user_day7_pic'], $value['user_day8_dist'], $value['user_day8_pic'], $value['user_day9_dist'], $value['user_day9_pic'], $value['user_day10_dist'], $value['user_day10_pic'], $value['user_total_walk'], $value['user_payment_mode'], $value['user_payment_status']];
        fputcsv( $file, $line_data);
      }
        
      }
      fclose($file);
      /* Write data in file: END */
    }
     exit;
  }

}
function CompanyGetUserData($consu_data, $key, $uid){
  $account = User::load($uid);
  $consu_data[$key]['user_address']= $account->field_address->value;
  $consu_data[$key]['user_city']= $account->field_city->value;
  $consu_data[$key]['user_company']= $account->field_company_name->value;
  $consu_data[$key]['user_country']= $account->get('field_country')->getValue()[0]['country_code'];
  
  $consu_data[$key]['user_empid']= 'NULL';

  $consu_data[$key]['user_state']= $account->get('field_country')->getValue()[0]['administrative_area'];
  $date_of_birth = $account->field_date_of_birth->value;
  $newDobDate = date("d-m-Y", strtotime($date_of_birth));  
  $consu_data[$key]['user_dob']= $newDobDate;
  $consu_data[$key]['user_event_type']= $account->field_event_type->value;
  $event_id= $account->field_event_name->target_id;
  $event_details = Node::load($event_id);
  $consu_data[$key]['user_event_name'] = $event_details->title->value;
  $consu_data[$key]['user_desig']= $account->field_designation->value;
  $consu_data[$key]['user_fname']= $account->field_first_name->value;
  $consu_data[$key]['user_lname']= $account->field_last_name->value;
  $consu_data[$key]['user_pincode']= $account->field_pincode->value;
  
  $consu_data[$key]['user_nationality']= $account->field_nationality->value;
  if(empty($consu_data[$key]['user_nationality'])){
    $consu_data[$key]['user_nationality'] ='NULL';
  }
  $consu_data[$key]['user_empid']= $account->field_employee_id->value;
  if(empty($consu_data[$key]['user_empid'])){
    $consu_data[$key]['user_empid'] ='NULL';
  }
  $consu_data[$key]['user_mobile_number']= $account->field_mobile_number->value;

  $consu_data[$key]['user_day1_dist']= 'NULL';
  $consu_data[$key]['user_day1_pic']= 'NULL';

  $consu_data[$key]['user_day2_dist']= 'NULL';
  $consu_data[$key]['user_day2_pic']= 'NULL';

  $consu_data[$key]['user_day3_dist']= 'NULL';
  $consu_data[$key]['user_day3_pic']= 'NULL';

  $consu_data[$key]['user_day4_dist']= 'NULL';
  $consu_data[$key]['user_day4_pic']= 'NULL';

  $consu_data[$key]['user_day5_dist']= 'NULL';
  $consu_data[$key]['user_day5_pic']= 'NULL';

  $consu_data[$key]['user_day6_dist']= 'NULL';
  $consu_data[$key]['user_day6_pic']= 'NULL';

  $consu_data[$key]['user_day7_dist']= 'NULL';
  $consu_data[$key]['user_day7_pic']= 'NULL';

  $consu_data[$key]['user_day8_dist']= 'NULL';
  $consu_data[$key]['user_day8_pic']= 'NULL';

  $consu_data[$key]['user_day9_dist']= 'NULL';
  $consu_data[$key]['user_day9_pic']= 'NULL';

  $consu_data[$key]['user_day10_dist']= 'NULL';
  $consu_data[$key]['user_day10_pic']= 'NULL';

  $nids = \Drupal::entityQuery('node')
    ->condition('type','virtual_trail')
    ->condition('uid',$uid)
    ->execute();
  foreach ($nids as $nid) {
    $node = \Drupal\node\Entity\Node::load($nid);
    $walker_image1 =$node->get('field_day1_pic')->getValue();
    if(!empty($walker_image1)){
      $walker_image1 =$walker_image1[0]['target_id'];
      $file1 = File::load($walker_image1);
      $image_uri1 = $file1->getFileUri();
      $walker_image_url1 = file_create_url($image_uri1);
      $consu_data[$key]['user_day1_dist']= $node->field_day1_distance->value;
      $consu_data[$key]['user_day1_pic']= $walker_image_url1;
    }
    $walker_image2 =$node->get('field_day2_pic')->getValue();
    if(!empty($walker_image2)){
      $walker_image2 =$walker_image2[0]['target_id'];
      $file2 = File::load($walker_image2);
      $image_uri2 = $file2->getFileUri();
      $walker_image_url2 = file_create_url($image_uri2);
      $consu_data[$key]['user_day2_dist']= $node->field_day2_distance->value;
      $consu_data[$key]['user_day2_pic']= $walker_image_url2;
    }
    $walker_image3 =$node->get('field_day3_pic')->getValue();
    if(!empty($walker_image3)){
      $walker_image3 =$walker_image3[0]['target_id'];
      $file3 = File::load($walker_image3);
      $image_uri3 = $file3->getFileUri();
      $walker_image_url3 = file_create_url($image_uri3);
      $consu_data[$key]['user_day3_dist']= $node->field_day3_distance->value;
      $consu_data[$key]['user_day3_pic']= $walker_image_url3;
    }
    $walker_image4 =$node->get('field_day4_pic')->getValue();
    if(!empty($walker_image4)){
      $walker_image4 =$walker_image4[0]['target_id'];
      $file4 = File::load($walker_image4);
      $image_uri4 = $file4->getFileUri();
      $walker_image_url4 = file_create_url($image_uri4);
      $consu_data[$key]['user_day4_dist']= $node->field_day4_distance->value;
      $consu_data[$key]['user_day4_pic']= $walker_image_url4;
    }
    $walker_image5 =$node->get('field_day5_pic')->getValue();
    if(!empty($walker_image5)){
      $walker_image5 =$walker_image5[0]['target_id'];
      $file5 = File::load($walker_image5);
      $image_uri5 = $file5->getFileUri();
      $walker_image_url5 = file_create_url($image_uri5);
      $consu_data[$key]['user_day5_dist']= $node->field_day5_distance->value;
      $consu_data[$key]['user_day5_pic']= $walker_image_url5;
    }
    $walker_image6 =$node->get('field_day6_pic')->getValue();
    if(!empty($walker_image6)){
      $walker_image6 =$walker_image6[0]['target_id'];
      $file6 = File::load($walker_image6);
      $image_uri6 = $file6->getFileUri();
      $walker_image_url6 = file_create_url($image_uri6);
      $consu_data[$key]['user_day6_dist']= $node->field_day6_distance->value;
      $consu_data[$key]['user_day6_pic']= $walker_image_url6;
    }
    $walker_image7 =$node->get('field_day7_pic')->getValue();
    if(!empty($walker_image7)){
      $walker_image7 =$walker_image7[0]['target_id'];
      $file7 = File::load($walker_image7);
      $image_uri7 = $file7->getFileUri();
      $walker_image_url7 = file_create_url($image_uri7);
      $consu_data[$key]['user_day7_dist']= $node->field_day7_distance->value;
      $consu_data[$key]['user_day7_pic']= $walker_image_url7;
    }
    $walker_image8 =$node->get('field_day8_pic')->getValue();
    if(!empty($walker_image8)){
      $walker_image8 =$walker_image8[0]['target_id'];
      $file8 = File::load($walker_image8);
      $image_uri8 = $file8->getFileUri();
      $walker_image_url8 = file_create_url($image_uri8);
      $consu_data[$key]['user_day8_dist']= $node->field_day8_distance->value;
      $consu_data[$key]['user_day8_pic']= $walker_image_url8;
    }
    $walker_image9 =$node->get('field_day9_pic')->getValue();
    if(!empty($walker_image9)){
      $walker_image9 =$walker_image9[0]['target_id'];
      $file9 = File::load($walker_image9);
      $image_uri9 = $file9->getFileUri();
      $walker_image_url9 = file_create_url($image_uri9);
      $consu_data[$key]['user_day9_dist']= $node->field_day9_distance->value;
      $consu_data[$key]['user_day9_pic']= $walker_image_url9;
    }
    $walker_image10 =$node->get('field_day10_pic')->getValue();
    if(!empty($walker_image10)){
      $walker_image10 =$walker_image10[0]['target_id'];
      $file10 = File::load($walker_image10);
      $image_uri10 = $file10->getFileUri();
      $walker_image_url10 = file_create_url($image_uri10);
      $consu_data[$key]['user_day10_dist']= $node->field_day10_distance->value;
      $consu_data[$key]['user_day10_pic']= $walker_image_url10;
    }
    $consu_data[$key]['user_total_walk']=$node->field_day1_distance->value+$node->field_day2_distance->value+$node->field_day3_distance->value+$node->field_day4_distance->value+$node->field_day5_distance->value+$node->field_day6_distance->value+$node->field_day7_distance->value+$node->field_day8_distance->value+$node->field_day9_distance->value+$node->field_day10_distance->value;
  }
  
    $consu_data[$key]['user_payment_status'] ='Company Payment';
    $consu_data[$key]['user_payment_mode'] ='Company Payment';
  
  return $consu_data;
}