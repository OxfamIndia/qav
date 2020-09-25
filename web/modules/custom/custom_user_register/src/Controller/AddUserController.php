<?php
/**
 * @file
 * Contains \Drupal\custom_user_register\Controller\AddUserController.
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

class AddUserController extends ControllerBase {

	public function RegisterUser(){
				$billing_name = $_GET['u'];
				$user_id = $_GET['uid'];
				$order_id="TWVirtualdonate".$user_id;
$node = Node::create(array(
    'type' => 'donars',
    'title' =>'Donar'.' ('.$billing_name.'-'.$user_id.')',
    'langcode' => 'en',
    'uid' => $user_id,
    'status' => 0,
    'body' => 'Offline',
    'field_card_name' => 'Online',
    'field_currency' => 'INR',
    'field_order_id' => $order_id,
    'field_order_status' => 'Success',
    'field_payment_mode' => 'Online',
    'field_tracking_id' => $order_id,
    'field_user_name' => $user_id,
    'field_bank_ref_number' => $order_id,
));

$node->save();
$nodeData = [
            'type' => 'virtual_trail',
            'title' => 'Dashboard'.' ('.$billing_name.'-'.$user_id.')',
            'uid' => $user_id,
            'field_user_name_id'=>$user_id,
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
        
	}


	
}
?>
