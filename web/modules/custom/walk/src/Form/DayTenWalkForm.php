<?php
/**
 * @file
 * Contains \Drupal\walk\Form\DayTenWalkForm.
 */
namespace Drupal\walk\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Url;
class DayTenWalkForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'day_ten_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $uid = \Drupal::currentUser()->id();
     $nids = \Drupal::entityQuery('node')
    ->condition('type','virtual_trail')
    ->condition('uid',$uid)
    ->execute();
   foreach ($nids as $nid) {
  $node = \Drupal\node\Entity\Node::load($nid);
  $walker_image =$node->get('field_day10_pic')->getValue();
  if(!empty($walker_image)){
    $walker_image =$walker_image[0]['target_id'];
    $file = File::load($walker_image);
    // Get origin image URI.
    $image_uri = $file->getFileUri();
    // Load image style "thumbnail".
    $style = ImageStyle::load('medium');
    // Get URI.
    $uri = file_create_url($image_uri);
    // Get URL.
    $walker_image_url = $uri;
    $walker_dist =$node->get('field_day10_distance')->getValue()[0]['value'];
  }
   
}  
    
    if(empty($walker_image_url)){
    $form['day10_walk_distance'] = array (
      '#type' => 'number',
	  '#attributes' => array(
  'min' => '0',
  ),
      '#title' => t('Day 10 | 15 August'),
      '#required' => TRUE,
    );
    $form['day10_image'] = [
        '#type' => 'managed_file',
        '#title' => t(' Upload Day 10'),
        '#upload_location' => 'public://images/',
        '#upload_validators' => array(
          'file_validate_extensions' => array('gif png jpg jpeg'),
          'file_validate_size' => file_upload_max_size(),
        ),
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#required' => TRUE,
    ];
    $form['#cache'] = ['max-age' => 0];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );
  }else
  {
    $form['walker_output_title'] = array (
      '#type' => 'markup',
      '#prefix' =>'<div class="output-cont-title">',
       '#markup' => t('Day 10 Kms Walked'),
       '#suffix' =>'</div>'
    );
    $form['walker_output'] = array (
      '#type' => 'markup',
      '#prefix' =>'<div class="output-cont">',
       '#markup' => '<img src="'.$walker_image_url.'"> <h2>Distance '.$walker_dist.' KM</h2>',
       '#suffix' =>'</div>'
    );
  }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {
     $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      $event_type = $user->field_event_type->getValue()[0]['value'];
      if (($form_state->getValue('day10_walk_distance')) > $event_type) {
        $form_state->setErrorByName('day10_walk_distance', $this->t('Your distance is greater than the total event type.'));
      }

    }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $image = $form_state->getValue('day10_image');
    $distanace = $form_state->getValue('day10_walk_distance');
    $file = File::load($image[0]);
    $file->setPermanent();
    $file->save();
    $uid = \Drupal::currentUser()->id();
    $nids = \Drupal::entityQuery('node')
    ->condition('type','virtual_trail')
    ->condition('uid',$uid)
    ->execute();
    foreach ($nids as $nid) {
      $node = \Drupal\node\Entity\Node::load($nid); 
      $node->field_day10_pic->target_id =$image[0];
      $node->field_day10_distance->value =$distanace;
      $walker_day1_dist =$node->get('field_day1_distance')->getValue()[0]['value'];
      $walker_day2_dist =$node->get('field_day2_distance')->getValue()[0]['value'];
      $walker_day3_dist =$node->get('field_day3_distance')->getValue()[0]['value'];
      $walker_day4_dist =$node->get('field_day4_distance')->getValue()[0]['value'];
      $walker_day5_dist =$node->get('field_day5_distance')->getValue()[0]['value'];
      $walker_day6_dist =$node->get('field_day6_distance')->getValue()[0]['value'];
      $walker_day7_dist =$node->get('field_day7_distance')->getValue()[0]['value'];
      $walker_day8_dist =$node->get('field_day8_distance')->getValue()[0]['value'];
      $walker_day9_dist =$node->get('field_day9_distance')->getValue()[0]['value'];
      $node->save();
    }
    $account = User::load($uid);
    $walker_total_distance = $account->get('field_event_type')->getValue()[0]['value'];
     $walker_total_distance = (int)$walker_total_distance;
     $walker_name =$account->get('field_first_name')->getValue()[0]['value'];
     $walker_last_name = $account->get('field_last_name')->getValue()[0]['value'];
  $event_id = $account->field_event_name->getValue()[0]['target_id'];
  $event_data = Node::load($event_id);
  $event_name = $event_data->getTitle();
  $walker_full_name = $walker_name.' '.$walker_last_name;
  $walker_full_name = ucfirst($walker_full_name);
     $last_pending_walk= $walker_total_distance-$walker_day1_dist-$walker_day2_dist-$walker_day3_dist-$walker_day4_dist-$walker_day5_dist-$walker_day6_dist-$walker_day7_dist-$walker_day8_dist-$walker_day9_dist;
     $pending_walk =$walker_total_distance-$distanace-$walker_day1_dist-$walker_day2_dist-$walker_day3_dist-$walker_day4_dist-$walker_day5_dist-$walker_day6_dist-$walker_day7_dist-$walker_day8_dist-$walker_day9_dist;
     $actual_pending_walk =$walker_total_distance-$distanace-$walker_day1_dist-$walker_day2_dist-$walker_day3_dist-$walker_day4_dist-$walker_day5_dist-$walker_day6_dist-$walker_day7_dist-$walker_day8_dist-$walker_day9_dist;
     $current_walk_distance= $distanace+$walker_day1_dist+$walker_day2_dist+$walker_day3_dist+$walker_day4_dist+$walker_day5_dist+$walker_day6_dist+$walker_day7_dist+$walker_day8_dist+$walker_day9_dist;
     if($pending_walk < 0){
       $pending_walk = 0;
     }

 if($last_pending_walk>0)
{
    $mailManager = \Drupal::service('plugin.manager.mail');
 $module = 'walk';
 $key = 'walker_day_ten_mail';
 $to = \Drupal::currentUser()->getEmail();
 $days = 10;
 $params['message'] = $walker_name.'&'.$walker_total_distance.'&'.$current_walk_distance.'&'.$pending_walk.'&'.$days;
 $params['mail_title'] = 'Day10';
 $langcode = \Drupal::currentUser()->getPreferredLangcode();
 $send = true;

 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
if($walker_total_distance == $distanace || $pending_walk == 0 ){
     $certificate_html = ob_get_clean();
  $certificate_html = getHtml10($walker_full_name, $walker_total_distance, $event_name, $days);
  

        $certificate_html = iconv("UTF-8","UTF-8//IGNORE",$certificate_html);
  include(DRUPAL_ROOT . '/modules/custom/walk/mpdf/mpdf.php');
       // include("mpdf/mpdf.php");
        $mpdf=new \mPDF('c','A4','','' , 0, 0, 0, 0, 0, 0); 

        //write html to PDF
        $mpdf->WriteHTML($certificate_html);
        $walker_name_file = preg_replace("/\s+/", "", $walker_name);
        $filename= 'pdf/'.$walker_name_file.'_'.$uid.'_'.$days.'.pdf';
        //output pdf
        $mpdf->Output($filename,'F');
    $key = 'certificate_mail';
    $days = 10;
    $eparams['message'] = $walker_full_name.'&'.$walker_total_distance.'&'.$event_name.'&'.$days;
    $eparams['mail_title'] = 'E-certificate';
    $attachment = array(
        'filepath' => $filename,
        'filename' => 'E-certificate.pdf',
        'filemime' => 'application/pdf'
    );
 $eparams['attachments'][] = $attachment;
    $result = $mailManager->mail($module, $key, $to, $langcode, $eparams, NULL, $send);
    $key = 'congrates_mail';
 $params['message'] = $walker_total_distance;
 $params['mail_title'] = 'Congratulation';
 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
  }
 
 } 
 $response = Url::fromUserInput('/walk-submit/'.$distanace);
  $form_state->setRedirectUrl($response);
  }
}
function getHtml10($walker_full_name, $walker_total_distance, $event_name, $days){
    $my_html='<HTML>
<body style="margin: 0; padding: 0;">

<table cellspacing="0" cellpadding="0" border="0" align="center" width="650">
  <tr>
    <td><img src="http://donate.oxfamindia.org/donatetoeducate/ravi/mailer/e-certificates01s.jpg" style="display: block; outline: none;"></td>
  </tr>

  <tr>

    <td>
      <table cellspacing="0" cellpadding="0" border="0" align="center">
        <tr>
          <td><img src="http://donate.oxfamindia.org/donatetoeducate/ravi/mailer/e-certificates04s.jpg" style="display: block; outline: none;"></td>
          <td>
      <table cellpadding="0" cellspacing="0" border="0" align="center" width="577" style="font-family: arial;">
        <tr>
          <td style="font-size: 28px; font-weight: bold; text-align: center;">'.$walker_full_name.'</td>
        </tr>

        <tr>
          <td height="10"></td>
        </tr>

        <tr>
          <td style="font-size: 22px; text-align: center; line-height: 30px;">displayed exceptional spirit and completed the <br>Oxfam Trailwalker Virtual Challenge 2020</td>
        </tr>

        <tr>
          <td height="20"></td>
        </tr>

        <tr>
          <td style="font-size: 28px; font-weight: bold; text-align: center;">'.$walker_total_distance.' kms in '.$days.' days <br>'.$event_name.'</td>
        </tr>
      </table>
    </td>
          <td><img src="http://donate.oxfamindia.org/donatetoeducate/ravi/mailer/e-certificates05s.jpg" style="display: block; outline: none;"></td>

        </tr>
      </table>
    </td>

  </tr>

  <tr>
    <td><img src="http://donate.oxfamindia.org/donatetoeducate/ravi/mailer/e-certificates02s.jpg" style="display: block; outline: none;"></td>
  </tr>

</table>

</body>
</HTML>';
return $my_html;

  }