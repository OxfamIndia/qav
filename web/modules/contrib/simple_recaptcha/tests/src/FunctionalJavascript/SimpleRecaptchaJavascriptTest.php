<?php

namespace Drupal\Tests\simple_recaptcha\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * JavaScripts tests for the Simple reCAPTCHA module.
 *
 * @group simple_recaptcha
 */
class SimpleRecaptchaJavascriptTest extends WebDriverTestBase {

  /**
   * WebAssert object.
   *
   * @var \Drupal\Tests\WebAssert
   */
  protected $webAssert;

  /**
   * DocumentElement object.
   *
   * @var \Behat\Mink\Element\DocumentElement
   */
  protected $page;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['simple_recaptcha'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'bartik';

  /**
   * A simple user.
   *
   * @var \Drupal\user\Entity\User
   */
  private $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->page = $this->getSession()->getPage();
    $this->webAssert = $this->assertSession();
    $this->user = $this->drupalCreateUser([
      'administer site configuration',
      'administer simple_recaptcha',
    ],
      'webadmin');
  }

  /**
   * Helper to configure the module.
   *
   * We need to set up reCAPTCHA test keys to make form alteration works.
   * Currently there's no way to set default config for testing.
   *
   * @see https://www.drupal.org/project/drupal/issues/913086
   */
  public function configureModule() {
    $this->drupalLogin($this->user);
    $this->drupalGet('admin/config/services/simple_recaptcha');
    $this->drupalPostForm(NULL, [
      'recaptcha_type' => 'v2',
      'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
      'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
      'form_ids' => 'user_login_form,user_pass,user_register_form',
    ], t('Save configuration'));
    drupal_flush_all_caches();
    $this->drupalLogout();
  }
  
  /**
   * Check if reCAPTCHA validation is added to user login form.
   */
  public function testLoginForm() {
    $this->configureModule();
    $config = $this->config('simple_recaptcha.config');
    $this->drupalGet('/user/login');

    // reCAPTCHA site key exists in drupalSettings.
    $this->assertJsCondition('drupalSettings.simple_recaptcha.sitekey === "' . $config->get('site_key') . '";');

    // Check if hidden field added by the module are present.
    $this->webAssert->hiddenFieldExists('simple_recaptcha_token');

    // This field shoudln't exist as it's added only when we configure v3 reCAPTCHA.
    $this->webAssert->hiddenFieldNotExists('simple_recaptcha_message');

    // Try to click on Log in button and render reCAPTCHA widget.
    $this->page->pressButton('Log in');
    $this->webAssert->waitForElement('css', 'recaptcha-visible');

    // reCAPTCHA doesn't provide consistent iframe name so we need to update it.
    $this->assignNameToCaptchaIframe();
    $this->getSession()->switchToIFrame('recaptcha-iframe');
    $this->assertStringCOntainsString('This reCAPTCHA is for testing purposes only. Please report to the site admin if you are seeing this.', $this->page->getContent());
    $this->htmlOutput($this->page->getHtml());

    // Try to log in, which should fail.
    $this->getSession()->switchToIFrame();
    $user = $this->drupalCreateUser([]);
    $edit = ['name' => $user->getAccountName(), 'pass' => $user->passRaw];
    $this->drupalPostForm(NULL, $edit, t('Log in'));

    // Check if reCAPTCHA wrapper has error class.
    $error_wrapper = $this->page->find('css', '.recaptcha-error');
    $this->assertTrue($error_wrapper->isVisible());

    // And we're still at user login page.
    $this->webAssert->addressEquals('/user/login');
    $this->htmlOutput($this->page->getHtml());

  }

  /**
   * Assigns a name to the reCAPTCHA iframe.
   * @see \Drupal\Tests\media\FunctionalJavascript\CKEditorIntegrationTest::assignNameToCkeditorIframe
   * assignNameToCkeditorIframe
   */
  protected function assignNameToCaptchaIframe() {
    $javascript = <<<JS
(function(){
  var iframes = document.getElementsByTagName('iframe');
    for(var i = 0; i < iframes.length; i++){
        var f = iframes[i];
        f.name = 'recaptcha-iframe';
    }
})()
JS;
    $this->getSession()->evaluateScript($javascript);
  }

}
