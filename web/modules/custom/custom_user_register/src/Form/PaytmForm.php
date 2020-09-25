<?php

namespace Drupal\custom_user_register\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure custom settings for this site.
 */
class PaytmForm extends ConfigFormBase {

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
    return 'paytm_config_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['custom_user_register.paytm_config'];
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
    $value = $this->config('custom_user_register.paytm_config');

    // Facebook fieldset.
    $form['paytm'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Paytm Credentials'),
      '#weight' => 50,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['paytm']['paytm_marchant_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Paytm Marchant key'),
      '#default_value' => $value->get('paytm_marchant_key'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['paytm']['paytm_marchant_mid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Paytm Marchant MID'),
      '#default_value' => $value->get('paytm_marchant_mid'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['paytm']['paytm_marchant_website'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Paytm marchant website'),
      '#default_value' => $value->get('paytm_marchant_website'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['paytm']['paytm_industry_type_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Paytm industry type ID'),
      '#default_value' => $value->get('paytm_industry_type_id'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $form['paytm']['paytm_channel'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Paytm channel'),
      '#default_value' => $value->get('paytm_channel'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );


    $form['paytm']['paytm_callback_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Callback URL'),
      '#default_value' => $value->get('paytm_callback_url'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );


    $form['paytm']['paytm_environment'] = array(
      '#type' => 'select',
      '#options' => array('TEST' => 'TEST', 'PROD' => 'LIVE'),
      '#title' => $this->t('Paytm environment'),
      '#default_value' => $value->get('paytm_environment'),
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

    $this->config('custom_user_register.paytm_config')
      ->set('paytm_marchant_key', $form_state->getValue('paytm_marchant_key'))
      ->set('paytm_marchant_mid', $form_state->getValue('paytm_marchant_mid'))
      ->set('paytm_marchant_website', $form_state->getValue('paytm_marchant_website'))
      ->set('paytm_industry_type_id', $form_state->getValue('paytm_industry_type_id'))
      ->set('paytm_channel', $form_state->getValue('paytm_channel'))
      ->set('paytm_environment', $form_state->getValue('paytm_environment'))
      ->set('paytm_callback_url', $form_state->getValue('paytm_callback_url'))
      ->save();

    parent::submitForm($form, $form_state);

  }

}
