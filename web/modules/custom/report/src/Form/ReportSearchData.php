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
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;
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
      // check wheather it has data or not
      if($result){
        $form['export'] = [
        '#type' => 'markup',
        '#weight' => 1999,
        '#prefix' => '<div id="export-data"><a href="/csv-report-download?start_date='.$start_date.'&end_date='.$last_date.'">Export Data</a></div>',
         ];
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
        
        $html = '';
        $html ='<div class="table-responsive"><table class="report-data table"><tr><th>User Id</th><th>First Name</th><th>Last Name</th><th>User Name</th><th>Email Id</th><th>Status</th><th>Registration Date</th><th>Insitution</th><th>Address</th><th>City</th><th>State</th><th>Country</th><th>DOB</th><th>Pin Code</th><th>Nationality</th><th>Employee Id</th><th>Mobile Number</th><th>Sub Id1</th><th>Event1 Name</th><th>Event1 Type</th><th>Day1 Distance</th><th>Day1 Pic</th><th>Day2 Distance</th><th>Day2 Pic</th><th>Day3 Distance</th><th>Day3 Pic</th><th>Day4 Distance</th><th>Day4 Pic</th><th>Day5 Distance</th><th>Day5 Pic</th><th>Day6 Distance</th><th>Day6 Pic</th><th>Day7 Distance</th><th>Day7 Pic</th><th>Day8 Distance</th><th>Day8 Pic</th><th>Day9 Distance</th><th>Day9 Pic</th><th>Day10 Distance</th><th>Day10 Pic</th><th>Total Event1 Distance</th><th>Event1 Payment</th><th>Sub Id2</th><th>Event2 Name</th><th>Event2 Type</th><th>Day1 Distance</th><th>Day1 Pic</th><th>Day2 Distance</th><th>Day2 Pic</th><th>Day3 Distance</th><th>Day3 Pic</th><th>Day4 Distance</th><th>Day4 Pic</th><th>Day5 Distance</th><th>Day5 Pic</th><th>Day6 Distance</th><th>Day6 Pic</th><th>Day7 Distance</th><th>Day7 Pic</th><th>Day8 Distance</th><th>Day8 Pic</th><th>Day9 Distance</th><th>Day9 Pic</th><th>Day10 Distance</th><th>Day10 Pic</th><th>Total Event2 Distance</th><th>Event2 Payment</th></tr>';
       
        foreach ($consu_data as $key => $value) {

          if($value['user_empid'] == ''){
         $html .='<tr><td>'.$value['user_id'].'</td><td>'.$value['user_fname'].'</td><td>'.$value['user_lname'].'</td><td>'.$value['user_name'].'</td><td>'.$value['mail_id'].'</td><td>'.$value['status'].'</td><td>'.$value['regist_date'].'</td><td>'.$value['institution'].'</td><td>'.$value['user_address'].'</td><td>'.$value['user_city'].'</td><td>'.$value['user_state'].'</td><td>'.$value['user_country'].'</td><td>'.$value['user_dob'].'</td><td>'.$value['user_pincode'].'</td><td>'.$value['user_nationality'].'</td><td>'.$value['user_empid'].'</td><td>'.$value['user_mobile_number'].'</td>';

         if(isset($value['user_activity'])){
         foreach ($value['user_activity'] as $activitykey => $activityvalue) {
          if(!isset($activityvalue['user_day1_dist'])){
            $activityvalue['user_day1_dist'] = '';
          }
          if(!isset($activityvalue['user_day2_dist'])){
            $activityvalue['user_day2_dist'] = '';
          }

          if(!isset($activityvalue['user_day3_dist'])){
            $activityvalue['user_day3_dist'] = '';
          }
          if(!isset($activityvalue['user_day4_dist'])){
            $activityvalue['user_day4_dist'] = '';
          }
          if(!isset($activityvalue['user_day5_dist'])){
            $activityvalue['user_day5_dist'] = '';
          }
          if(!isset($activityvalue['user_day6_dist'])){
            $activityvalue['user_day6_dist'] = '';
          }
          if(!isset($activityvalue['user_day7_dist'])){
            $activityvalue['user_day7_dist'] = '';
          }
          if(!isset($activityvalue['user_day8_dist'])){
            $activityvalue['user_day8_dist'] = '';
          }
          if(!isset($activityvalue['user_day9_dist'])){
            $activityvalue['user_day9_dist'] = '';
          }
          if(!isset($activityvalue['user_day10_dist'])){
            $activityvalue['user_day10_dist'] = '';
          }
          if(!isset($activityvalue['user_day1_pic'])){
            $activityvalue['user_day1_pic'] = '';
          }
          if(!isset($activityvalue['user_day2_pic'])){
            $activityvalue['user_day2_pic'] = '';
          }

          if(!isset($activityvalue['user_day3_pic'])){
            $activityvalue['user_day3_pic'] = '';
          }
          if(!isset($activityvalue['user_day4_pic'])){
            $activityvalue['user_day4_pic'] = '';
          }
          if(!isset($activityvalue['user_day5_pic'])){
            $activityvalue['user_day5_pic'] = '';
          }
          if(!isset($activityvalue['user_day6_pic'])){
            $activityvalue['user_day6_pic'] = '';
          }

          if(!isset($activityvalue['user_day7_pic'])){
            $activityvalue['user_day7_pic'] = '';
          }if(!isset($activityvalue['user_day8_pic'])){
            $activityvalue['user_day8_pic'] = '';
          }
          if(!isset($activityvalue['user_day9_pic'])){
            $activityvalue['user_day9_pic'] = '';
          }
          if(!isset($activityvalue['user_day10_pic'])){
            $activityvalue['user_day10_pic'] = '';
          }
          if(!isset($activityvalue['user_payment_status'])){
            $activityvalue['user_payment_status'] = '';
          }

            $html .='<td>'.$activityvalue['second_submission_id'].'</td><td>'.$activityvalue['user_event_name'].'</td><td>'.$activityvalue['user_challenge_type'].'</td><td>'.$activityvalue['user_day1_dist'].'</td><td>'.$activityvalue['user_day1_pic'].'</td><td>'.$activityvalue['user_day2_dist'].'</td><td>'.$activityvalue['user_day2_pic'].'</td><td>'.$activityvalue['user_day3_dist'].'</td><td>'.$activityvalue['user_day3_pic'].'</td><td>'.$activityvalue['user_day4_dist'].'</td><td>'.$activityvalue['user_day4_pic'].'</td><td>'.$activityvalue['user_day5_dist'].'</td><td>'.$activityvalue['user_day5_pic'].'</td><td>'.$activityvalue['user_day6_dist'].'</td><td>'.$activityvalue['user_day6_pic'].'</td><td>'.$activityvalue['user_day7_dist'].'</td><td>'.$activityvalue['user_day7_pic'].'</td><td>'.$activityvalue['user_day8_dist'].'</td><td>'.$activityvalue['user_day8_pic'].'</td><td>'.$activityvalue['user_day9_dist'].'</td><td>'.$activityvalue['user_day9_pic'].'</td><td>'.$activityvalue['user_day10_dist'].'</td><td>'.$activityvalue['user_day10_pic'].'</td><td>'.$activityvalue['user_total_dist'].'</td><td>'.$activityvalue['user_payment_status'].'</td>';
         }
       }
         
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
        /* $walker_image1 =$walker_image1[0]['target_id'];
        $file1 = File::load($walker_image1);
        $image_uri1 = $file1->getFileUri(); 
        $walker_image_url1 = file_create_url($image_uri1);*/
        $walker_image_url1 = 'https://oxfamuploads.s3.ap-south-1.amazonaws.com/donate/hp1.gif';
        $consu_data[$key]['user_activity'][$event_id]['user_day'.$activity_count.'_dist']=$node->field_distance->value;
        $consu_data[$key]['user_activity'][$event_id]['user_day'.$activity_count.'_pic']= $walker_image_url1;
        $activity_count = $activity_count+1;
      }
    }
  }

  return $consu_data;
}