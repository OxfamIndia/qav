<?php

namespace Drupal\custom_user_register\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure custom settings for this site.
 */
class CCAvenueForm extends ConfigFormBase {

  /**
   * Constructor for SocialFeedsBlockForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */


  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'ccavenue_config_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['custom_user_register.ccavenue_config'];
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $value = $this->config('custom_user_register.ccavenue_config');

    // Facebook fieldset.
    $form['ccavenue'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('CcAvenue Credentials'),
      '#weight' => 50,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['ccavenue']['marchant_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Marchant ID'),
      '#default_value' => $value->get('marchant_id'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['ccavenue']['access_code'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Access Code'),
      '#default_value' => $value->get('access_code'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['ccavenue']['working_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Working Key'),
      '#default_value' => $value->get('working_key'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    
    $form['ccavenue']['international_marchant_id'] = array(
    		'#type' => 'textfield',
    		'#title' => $this->t('International Marchant ID'),
    		'#default_value' => $value->get('international_marchant_id'),
    		'#maxlength' => 255,
    		'#required' => TRUE,
    );
    
    $form['ccavenue']['international_access_code'] = array(
    		'#type' => 'textfield',
    		'#title' => $this->t('International Access Code'),
    		'#default_value' => $value->get('international_access_code'),
    		'#maxlength' => 255,
    		'#required' => TRUE,
    );
    
    $form['ccavenue']['international_working_key'] = array(
    		'#type' => 'textfield',
    		'#title' => $this->t('International Working Key'),
    		'#default_value' => $value->get('international_working_key'),
    		'#maxlength' => 255,
    		'#required' => TRUE,
    );

    $form['ccavenue']['site_redirect_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Site Redirect URL'),
      '#default_value' => $value->get('site_redirect_url'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['ccavenue']['site_cancel_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Site Cancel URL'),
      '#default_value' => $value->get('site_cancel_url'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    
    $form['ccavenue']['ccavenue_url'] = [
    	'#type' => 'textfield',
    	'#title' => $this->t('CcAvenue URL'),
    	'#default_value' => $value->get('ccavenue_url'),
        '#required' => TRUE,
    ];
	
	$form['ccavenue']['ccavenue_amount'] = [
    	'#type' => 'textfield',
    	'#title' => $this->t('CcAvenue Amount'),
    	'#default_value' => $value->get('ccavenue_amount'),
        '#required' => TRUE,
    ];
    $form['ccavenue']['dashborad_and_event_date'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Enter Date in dd-mm-yyyy exp: 31-12-2021'),
      '#default_value' => $value->get('dashborad_and_event_date'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);

  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('custom_user_register.ccavenue_config')
      ->set('marchant_id', $form_state->getValue('marchant_id'))
      ->set('access_code', $form_state->getValue('access_code'))
      ->set('working_key', $form_state->getValue('working_key'))
      ->set('international_marchant_id', $form_state->getValue('international_marchant_id'))
      ->set('international_access_code', $form_state->getValue('international_access_code'))
      ->set('international_working_key', $form_state->getValue('international_working_key'))
      ->set('site_redirect_url', $form_state->getValue('site_redirect_url'))
      ->set('site_cancel_url', $form_state->getValue('site_cancel_url'))
      ->set('ccavenue_url', $form_state->getValue('ccavenue_url'))
	  ->set('ccavenue_amount', $form_state->getValue('ccavenue_amount'))
    ->set('dashborad_and_event_date', $form_state->getValue('dashborad_and_event_date'))
      ->save();

    parent::submitForm($form, $form_state);

  }

}
