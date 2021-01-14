<?php

namespace Drupal\oxfam\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

use Drupal\Core\Cache\Cache;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "upcoming",
 *   admin_label = @Translation("Upcoming"),
 * )
 */
class Upcoming extends BlockBase
{
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    // get active slots first
    $view1 = \Drupal\views\Views::getView('active_campaigns_for_paid_sign_ups');
    $view1->execute();
    $total_active_slots_data = $view1->result;

    // put all active slots in an array to compare with slots which user does not paid for
    if (count($total_active_slots_data) > 0) {
      foreach ($total_active_slots_data as $data) {
        $entityData = $data->_entity;
        $activeSlotNid = $entityData->get('nid')->value;
        $activeSlotsAll[] = $activeSlotNid;
      }
    }

    // get those slots for logged in user which are already there in paid webform
    $view2 = \Drupal\views\Views::getView('moreslotsforregistration');
    $view2->execute();
    $total_subscriptions = $view2->result;

    // Paid Subscription
    if (count($total_subscriptions) > 0) {
      foreach ($total_subscriptions as $data) {
        $entityData = $data->_entity;
        $slotNodeID = $entityData->getData()['challenge_slot'];
        $subscriptions[] = $slotNodeID;
      }
    }

    $pendingforpaid = array();
    // save all subscriptions into an array
    if (count($total_subscriptions) > 0) {
      foreach ($total_active_slots_data as $activedata) {
        $entityData = $activedata->_entity;
        $activeSlotNid = $entityData->get('nid')->value;
        if (in_array($activeSlotNid, $subscriptions)) {
          foreach ($total_subscriptions as $subsdata) {
            $entityData = $subsdata->_entity;
            $slotSubsID = $entityData->getData()['challenge_slot'];
            $paymentStatus = $entityData->getData()['payment_status'];
            $submissionID = $entityData->get('sid')->value;
            if ($activeSlotNid == $slotSubsID) {
              if ($paymentStatus != 'Success') {
                $nodeme = \Drupal\node\Entity\Node::load($slotNodeID);
                if (!empty($submissionID)) {
                  $pendingforpaid[] = array(
                    'slotid' => $slotNodeID,
                    'submissionid' => $submissionID,
                    'stitle' => $nodeme->getTitle(),
                  );
                } else {
                  $subid = '';
                  $pendingforpaid[] = array(
                    'slotid' => $slotNodeID,
                    'submissionid' => $subid,
                    'stitle' => $nodeme->getTitle(),
                  );
                }
              }
            }
          }
        } else {
          $nodeme = \Drupal\node\Entity\Node::load($activeSlotNid);
          $pendingforpaid[] = array(
            'slotid' => $activeSlotNid,
            'submissionid' => '',
            'stitle' => $nodeme->getTitle(),
          );
        }
      }
    } else {
      foreach ($activeSlotsAll as $data) {
        $nodeme = \Drupal\node\Entity\Node::load($data);
        $pendingforpaid[] = array(
          'slotid' => $data,
          'submissionid' => '',
          'stitle' => $nodeme->getTitle(),
        );
      }
    }
    //$variables['test'] = $pendingforpaid;
    $customLink = '';
    if(count($pendingforpaid) > 0) {
      foreach ($pendingforpaid as $registrationData) {
        if (empty($registrationData['submissionid'])) {
          $customLink .= "<li><span>".$registrationData['stitle']."</span><a href='".base_path()."form/subscribers?slot=".$registrationData['slotid']."'>Register Now</a></li>";
        } else {
          $customLink .= "<li><span>".$registrationData['stitle']."</span><a href='".base_path()."webform/registration/submissions/".$registrationData['submissionid']."/edit?slot=" . $registrationData['slotid'] . "'>Checkout Now</a></li>";
        }
      }
    }
    return [
      '#prefix' => '<h3>Don\'t miss out on our <strong>upcoming events</strong></h3><ul>',
      '#markup' => $customLink,
      '#suffix' => '</ul>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
