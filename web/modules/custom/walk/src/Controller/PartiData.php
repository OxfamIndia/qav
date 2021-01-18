<?php
namespace Drupal\walk\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\block\Entity\Block;
use Drupal\file\Entity\File;
use Drupal\Core\Render\Element\PasswordConfirm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Database\Connection;
//require 'vendor/autoload.php'; 

/**
 * Defines PartiData class.
 */
class PartiData extends ControllerBase {
  /**
   * Submit PartiData
   */
  public function parti_mail_data() {
  	include(DRUPAL_ROOT . '/modules/custom/walk/mpdf/mpdf.php');

    if(isset($_POST["submit"]))
{
      $message = $_POST['message'];
      $file = $_FILES['file']['tmp_name'];
      $handle = fopen($file, "r");
      $c = 0;
      while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
      {
         $user_id = $filesop[0];
         $user_saved_fname = $filesop[1];
      $user_full_name = $filesop[2];
      $walker_name = $filesop[2];
      $user_saved_walk =$filesop[3];
       $user_mail = $filesop[4];
      $event_name ='20-29 November';
              
      $certificate_html = ob_get_clean();
      $certificate_html = getpartiHtml($user_full_name, $user_saved_walk, $event_name);
  

        $certificate_html = iconv("UTF-8","UTF-8//IGNORE",$certificate_html);
  
       // include("mpdf/mpdf.php");
        $mpdf=new \mPDF('c','A4','','' , 0, 0, 0, 0, 0, 0); 

        //write html to PDF
        $mpdf->WriteHTML($certificate_html);
         $walker_name_file = preg_replace("/\s+/", "", $walker_name);
        $filename= 'pdf/'.$user_saved_fname.'_'.$user_id.'_parti.pdf';
        //output pdf
        $mpdf->Output($filename,'F');
        $mailManager = \Drupal::service('plugin.manager.mail');
 $module = 'walk';
 $key = 'walker_participated';
 $to = $user_mail;
 $params['message'] = $user_full_name.'&'.$user_saved_walk.'&'.$event_name;
 $params['mail_title'] = 'E-certificate';
 $attachment = array(
        'filepath' => $filename,
        'filename' => 'E-Participate-certificate.pdf',
        'filemime' => 'application/pdf'
    );
 $params['attachments'][0] = $attachment;
 $langcode = \Drupal::currentUser()->getPreferredLangcode();
 $send = true;

 $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
 print_r('mail sent to '.$to);
 echo '<br>';
      }
     
}

    die('All Sent');
     
            
  
  }

}
function getPartiHtml($walker_full_name, $walker_total_distance, $event_name){
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
          <td style="font-size: 22px; text-align: center; line-height: 30px;">displayed exceptional spirit and participated in <br>Oxfam Trailwalker Virtual Challenge 2020</td>
        </tr>

        <tr>
          <td height="20"></td>
        </tr>

        <tr>
          <td style="font-size: 28px; font-weight: bold; text-align: center;">'.$walker_total_distance.' kms in 10 days <br>'.$event_name.'</td>
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