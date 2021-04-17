<?php
/**
 * @file
 * Contains \Drupal\report_download\Form\UserActivitySearchData.
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
class UserActivitySearchData extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_activity_search_data';
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
        '#prefix' => '<div id="export-data"><a href="/csv-user-activity-download?start_date='.$start_date.'&end_date='.$last_date.'">Export Data</a></div>',
         ];
        foreach ($result as $key => $value) {
          if($value->status ==1 && !empty($value->field_webform_value)){
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
            $consu_data= GetSubmitActivityData($consu_data, $key, $value->uid);
          }
        }
        /*kint($consu_data);
      die();*/
        
        $html = '';
        $html ='<div class="table-responsive"><table class="report-data table"><tr><th>User Id</th><th>First Name</th><th>Last Name</th><th>User Name</th><th>Email Id</th><th>Status</th><th>Registration Date</th><th>Mobile Number</th><th>Submission ID</th><th>Day1 Distance</th><th>Day1 Pic</th><th>Day2 Distance</th><th>Day2 Pic</th><th>Day3 Distance</th><th>Day3 Pic</th><th>Day4 Distance</th><th>Day4 Pic</th><th>Day5 Distance</th><th>Day5 Pic</th><th>Day6 Distance</th><th>Day6 Pic</th><th>Day7 Distance</th><th>Day7 Pic</th><th>Day8 Distance</th><th>Day8 Pic</th><th>Day9 Distance</th><th>Day9 Pic</th><th>Day10 Distance</th><th>Day10 Pic</th></tr>';
       
        foreach ($consu_data as $key => $value) {
         $html .='<tr><td>'.$value['user_id'].'</td><td>'.$value['user_fname'].'</td><td>'.$value['user_lname'].'</td><td>'.$value['user_name'].'</td><td>'.$value['mail_id'].'</td><td>'.$value['status'].'</td><td>'.$value['regist_date'].'</td><td>'.$value['user_mobile_number'].'</td><td>'.$value['user_first_webform_id'].'</td>';

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
          

            $html .='<td>'.$value['user_activity']['user_day1_dist'].'</td><td>'.$value['user_activity']['user_day1_pic'].'</td><td>'.$value['user_activity']['user_day2_dist'].'</td><td>'.$value['user_activity']['user_day2_pic'].'</td><td>'.$value['user_activity']['user_day3_dist'].'</td><td>'.$value['user_activity']['user_day3_pic'].'</td><td>'.$value['user_activity']['user_day4_dist'].'</td><td>'.$value['user_activity']['user_day4_pic'].'</td><td>'.$value['user_activity']['user_day5_dist'].'</td><td>'.$value['user_activity']['user_day5_pic'].'</td><td>'.$value['user_activity']['user_day6_dist'].'</td><td>'.$value['user_activity']['user_day6_pic'].'</td><td>'.$value['user_activity']['user_day7_dist'].'</td><td>'.$value['user_activity']['user_day7_pic'].'</td><td>'.$value['user_activity']['user_day8_dist'].'</td><td>'.$value['user_activity']['user_day8_pic'].'</td><td>'.$value['user_activity']['user_day9_dist'].'</td><td>'.$value['user_activity']['user_day9_pic'].'</td><td>'.$value['user_activity']['user_day10_dist'].'</td><td>'.$value['user_activity']['user_day10_pic'].'</td>';
       }
         
         $html .='</tr>';
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
    $start_date =strtotime($from_date);
    $end_date = strtotime($to_date);
    $response = new RedirectResponse(\Drupal::url('report.user_activity', ['start_date' => $start_date, 'end_date' => $end_date]));
    $response->send();
    exit;  
  }
}

function GetSubmitActivityData($consu_data, $key, $uid){
  //$uid =6782;
  $nids = \Drupal::entityQuery('node')
  ->condition('type','daily_activity')
  ->condition('uid',$uid)
  //->condition('created',1615161599, '>')
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
  //kint($consu_data);
  //die();
  return $consu_data;
}