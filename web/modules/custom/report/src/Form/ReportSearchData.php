<?php
/**
 * @file
 * Contains \Drupal\report_download\Form\ReportSearchData.
 */
namespace Drupal\report_download\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
class ReportSearchData extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'report_search_data';
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {
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
      $query->condition('created', array($start_date,  $last_date), 'BETWEEN');
      $query->condition('status', 1, '=');
      $query->LeftJoin('user__field_address', 'uadd', 'u.uid = uadd.entity_id');
      $query->LeftJoin('user__field_city', 'ucity', 'u.uid = ucity.entity_id');
      $query->LeftJoin('user__field_company_name', 'ucompany', 'u.uid = ucompany.entity_id');
      $query->LeftJoin('user__field_country', 'ucontry', 'u.uid = ucontry.entity_id');
      $query->LeftJoin('user__field_date_of_birth', 'udob', 'u.uid = udob.entity_id');
      $query->LeftJoin('user__field_designation', 'udesg', 'u.uid = udesg.entity_id');
      $query->LeftJoin('user__field_first_name', 'ufname', 'u.uid = ufname.entity_id');
      $query->LeftJoin('user__field_last_name', 'ulname', 'u.uid = ulname.entity_id');

      $query->LeftJoin('user__field_pincode', 'upin', 'u.uid = upin.entity_id');
      $query->LeftJoin('user__field_nationality', 'unation', 'u.uid = unation.entity_id');
      $query->LeftJoin('user__field_mobile_number', 'umob', 'u.uid = umob.entity_id');
      $query->LeftJoin('user__field_event_type', 'ueventyp', 'u.uid = ueventyp.entity_id');
      /*$query->LeftJoin('user__field_city', 'ucity', 'u.uid = ucity.entity_id');
      $query->LeftJoin('user__field_city', 'ucity', 'u.uid = ucity.entity_id');
      $query->LeftJoin('user__field_city', 'ucity', 'u.uid = ucity.entity_id');
      $query->LeftJoin('user__field_city', 'ucity', 'u.uid = ucity.entity_id');
      $query->LeftJoin('user__field_city', 'ucity', 'u.uid = ucity.entity_id');*/



      $query->fields('u', ['uid', 'name', 'mail','status', 'created']);
      $query->fields('uadd', ['field_address_value']);
      $query->fields('ucity', ['field_city_value']);
      $query->fields('ucompany', ['field_company_name_value']);
      $query->fields('ucontry', ['field_country_country_code','field_country_administrative_area']);
      $query->fields('udob', ['field_date_of_birth_value']);
      $query->fields('udesg', ['field_designation_value']);
      $query->fields('ufname', ['field_first_name_value']);
      $query->fields('ulname', ['field_last_name_value']);

      $query->fields('upin', ['field_pincode_value']);
      $query->fields('unation', ['field_nationality_value']);
      $query->fields('umob', ['field_mobile_number_value']);
      
      $query->fields('ueventyp', ['field_event_type_value']);
      /*$query->fields('ucompany', ['field_company_name_value']);
      $query->fields('ucompany', ['field_company_name_value']);
      $query->fields('ucompany', ['field_company_name_value']);*/
      

      



      $result = $query->execute()->fetchAll();
      
      $consu_data =array();
      if($result){

        $form['export'] = [
      '#type' => 'markup',
      '#weight' => 1999,
      '#prefix' => '<div id="export-data"><a href="/report-download?start_date='.$start_date.'&end_date='.$last_date.'">Export Data</a></div>',
    ];

        foreach ($result as $key => $value) {
          if($key>0){
            if($value->status ==1){
         $consu_data[$key]['user_id'] = $value->uid;
         $consu_data[$key]['user_name'] = $value->name;
         $consu_data[$key]['mail_id'] = $value->mail;
         $consu_data[$key]['status'] = $value->status;
         $consu_data[$key]['regist_date'] = date('d/m/Y', $value->created);
         $consu_data[$key]['user_address']= $value->field_address_value;
          $consu_data[$key]['user_city']= $value->field_city_value;
          $consu_data[$key]['user_company']= $value->field_company_name_value;
          $consu_data[$key]['user_country']= $value->field_country_country_code;
          $consu_data[$key]['user_state']= $value->field_country_administrative_area;
           $consu_data[$key]['user_nationality'] =$value->field_nationality_value;
          $consu_data[$key]['user_empid']= 'NULL';
          $date_of_birth = $value->field_date_of_birth_value;
          $newDobDate = date("d-m-Y", strtotime($date_of_birth));  
          $consu_data[$key]['user_dob']= $newDobDate;
          $consu_data[$key]['user_desig']= $value->field_designation_value;
  $consu_data[$key]['user_fname']= $value->field_first_name_value;
  $consu_data[$key]['user_lname']= $value->field_last_name_value;
  $consu_data[$key]['user_pincode']= $value->field_pincode_value;
  $consu_data[$key]['user_mobile_number']= $value->field_mobile_number_value;
  $consu_data[$key]['user_event_type']= $value->field_event_type_value;
         $consu_data= GetUserData($consu_data, $key, $value->uid);
       }
       }


        }
        
        $html = '';
        $html ='<div class="table-responsive"><table class="report-data table"><tr><th>User Id</th><th>User Name</th><th>Email Id</th><th>Status</th><th>Registration Date</th><th>Address</th><th>City</th><th>Company</th><th>Country</th><th>State</th><th>DOB</th><th>Event Type</th><th>Event Name</th><th>Designation</th><th>First Name</th><th>Last Name</th><th>Pin Code</th><th>Nationality</th><th>Employee Id</th><th>Mobile Number</th><th>Day1 Ditance</th><th>Day1 Pic</th><th>Day2 Ditance</th><th>Day2 Pic</th><th>Day3 Ditance</th><th>Day3 Pic</th><th>Day4 Ditance</th><th>Day4 Pic</th><th>Day5 Ditance</th><th>Day5 Pic</th><th>Day6 Ditance</th><th>Day6 Pic</th><th>Day7 Ditance</th><th>Day7 Pic</th><th>Day8 Ditance</th><th>Day8 Pic</th><th>Day9 Ditance</th><th>Day9 Pic</th><th>Day10 Ditance</th><th>Day10 Pic</th><th>Total Walk Distance</th><th>Payment Mode</th><th>Payment Status</th></tr>';
        foreach ($consu_data as $key => $value) {
          if($value['user_empid'] == 'NULL'){
         $html .='<tr><td>'.$value['user_id'].'</td><td>'.$value['user_name'].'</td><td>'.$value['mail_id'].'</td><td>'.$value['status'].'</td><td>'.$value['regist_date'].'</td><td>'.$value['user_address'].'</td><td>'.$value['user_city'].'</td><td>'.$value['user_company'].'</td><td>'.$value['user_country'].'</td><td>'.$value['user_state'].'</td><td>'.$value['user_dob'].'</td><td>'.$value['user_event_type'].'</td><td>'.$value['user_event_name'].'</td><td>'.$value['user_desig'].'</td><td>'.$value['user_fname'].'</td><td>'.$value['user_lname'].'</td><td>'.$value['user_pincode'].'</td><td>'.$value['user_nationality'].'</td><td>'.$value['user_empid'].'</td><td>'.$value['user_mobile_number'].'</td><td>'.$value['user_day1_dist'].'</td><td><a target="_blank" href='.$value['user_day1_pic'].'>Day1 Image</a></td><td>'.$value['user_day2_dist'].'</td><td><a target="_blank" href='.$value['user_day2_pic'].'>Day2 Image</a></td><td>'.$value['user_day3_dist'].'</td><td><a target="_blank" href='.$value['user_day3_pic'].'>Day3 Image</a></td><td>'.$value['user_day4_dist'].'</td><td><a target="_blank" href='.$value['user_day4_pic'].'>Day4 Image</a></td><td>'.$value['user_day5_dist'].'</td><td><a target="_blank" href='.$value['user_day5_pic'].'>Day5 Image</a></td><td>'.$value['user_day6_dist'].'</td><td><a target="_blank" href='.$value['user_day6_pic'].'>Day6 Image</a></td><td>'.$value['user_day7_dist'].'</td><td><a target="_blank" href='.$value['user_day7_pic'].'>Day7 Image</a></td><td>'.$value['user_day8_dist'].'</td><td><a target="_blank" href='.$value['user_day8_pic'].'>Day8 Image</a></td><td>'.$value['user_day9_dist'].'</td><td><a target="_blank" href='.$value['user_day9_pic'].'>Day9 Image</a></td><td>'.$value['user_day10_dist'].'</td><td><a target="_blank" href='.$value['user_day10_pic'].'>Day10 Image</a></td><td>'.$value['user_total_walk'].'</td><td>'.$value['user_payment_mode'].'</td><td>'.$value['user_payment_status'].'</td>';
         
         $html .='</tr>';
       }

        }
        $html .= '</table></div>';
      }else{
        $html .= 'No Data found';
      }
    }else{
      $html .= '';
    }
    
    $form['start_date'] = array(
      '#type' => 'date',
      '#title' => t('Start Date'),
      '#default_value' => $sd2,
      '#prefix' => '<div class="report-data-container">',
      '#required' => TRUE,
    );
    $form['end_date'] = array(
      '#type' => 'date',
      '#title' => t('End Date'),
      '#required' => TRUE,
      '#default_value' => $ed2,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#weight' => 10,
      '#suffix' => '</div>'
    );
      $form['mydata'] = [
      '#type' => 'markup',
      '#weight' => 1999,
      '#prefix' => '<div id="my-form-sample-form-report">'.$html.'</div>',
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $from_date = $form_state->getValue('start_date');
    $to_date = $form_state->getValue('end_date'); 
   /* $f_date =  explode('-',$from_date);
    $fy = $f_date[0];
    $fm = $f_date[1];
    $fd = $f_date[2];
    $t_date =  explode('-',$to_date);
    $ty = $t_date[0];
    $tm = $t_date[1];
    $td = $t_date[2];
    $start_date = mktime ('00', '00', '00', $fm, $fd, $fy);
    $end_date = mktime ('23', '59', '59', $tm, $td, $ty);*/
    $start_date =strtotime($from_date);
    $end_date = strtotime($to_date);
    $response = new RedirectResponse(\Drupal::url('report.portfolio_search_form', ['start_date' => $start_date, 'end_date' => $end_date]));
    $response->send();
    exit;  
  }
}

function GetUserData($consu_data, $key, $uid){
  $account = User::load($uid);
  $event_id= $account->field_event_name->target_id;
  $event_details = Node::load($event_id);
  $consu_data[$key]['user_event_name'] = $event_details->title->value;

  $consu_data[$key]['user_day1_dist']= 'NULL';
  $consu_data[$key]['user_day1_pic']= '#';

  $consu_data[$key]['user_day2_dist']= 'NULL';
  $consu_data[$key]['user_day2_pic']= '#';

  $consu_data[$key]['user_day3_dist']= 'NULL';
  $consu_data[$key]['user_day3_pic']= '#';

  $consu_data[$key]['user_day4_dist']= 'NULL';
  $consu_data[$key]['user_day4_pic']= '#';

  $consu_data[$key]['user_day5_dist']= 'NULL';
  $consu_data[$key]['user_day5_pic']= '#';

  $consu_data[$key]['user_day6_dist']= 'NULL';
  $consu_data[$key]['user_day6_pic']= '#';

  $consu_data[$key]['user_day7_dist']= 'NULL';
  $consu_data[$key]['user_day7_pic']= '#';

  $consu_data[$key]['user_day8_dist']= 'NULL';
  $consu_data[$key]['user_day8_pic']= '#';

  $consu_data[$key]['user_day9_dist']= 'NULL';
  $consu_data[$key]['user_day9_pic']= '#';

  $consu_data[$key]['user_day10_dist']= 'NULL';
  $consu_data[$key]['user_day10_pic']= '#';

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
    $consu_data[$key]['user_total_walk']= $node->field_day1_distance->value+$node->field_day2_distance->value+$node->field_day3_distance->value+$node->field_day4_distance->value+$node->field_day5_distance->value+$node->field_day6_distance->value+$node->field_day7_distance->value+$node->field_day8_distance->value+$node->field_day9_distance->value+$node->field_day10_distance->value;
  }

  $donarids = \Drupal::entityQuery('node')
    ->condition('type','donars')
    ->condition('uid',$uid)
    ->execute();
  foreach ($donarids as $did) {
    $donar_node = \Drupal\node\Entity\Node::load($did);
    $consu_data[$key]['user_payment_status'] =$donar_node->field_order_status->value;
    $consu_data[$key]['user_payment_mode'] =$donar_node->field_payment_mode->value;
  }
  return $consu_data;
}