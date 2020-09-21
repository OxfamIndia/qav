<?php
namespace Drupal\walk\Controller;
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
 * Defines walkSubmitData class.
 */
class walkSubmitData extends ControllerBase {
  /**
   * Submit walkSubmitData
   */
  public function walk_submit_data($walked, $pending_walk) {
     return [
      '#theme' => 'walk_submit_template',      
      '#distance' => $walked,
      '#left' => $pending_walk,
    ];
  
  }

}
