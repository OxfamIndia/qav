<?php 

    /**
    * @file
    * Contains Drupal\donations\Form\DonationAddForm.
    */

    namespace Drupal\donations\Form;

    use \Drupal\node\Entity\Node;
    use \Drupal\file\Entity\File;
    use Drupal\taxonomy\Entity\Term;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Symfony\Component\HttpFoundation\Session\Session;
    use \Drupal\user\Entity\User;
    use Drupal\Core\Ajax\AjaxResponse;
    use Drupal\Core\Ajax\ChangedCommand;
    use Drupal\Core\Ajax\CssCommand;
    use Drupal\Core\Ajax\HtmlCommand;
    use Drupal\Core\Ajax\InvokeCommand;
    use Drupal\paytm\ConfigPaytm;
    use Drupal\webform\Entity\Webform;
    use Drupal\webform\Entity\WebformSubmission;
    use Drupal\webform\WebformSubmissionForm;
    use Drupal\Core\Database\Database;


    /**
    * Class DonationAddForm
    * 
    * @package Drupal\donations\Form
    */

    Class DonationAddForm extends FormBase{

        public function getFormId() {
            return 'donation_add_form';
        }

        public function buildForm(array $form, FormStateInterface $form_state, $story = NULL){          
            $default_city = 0;
            if(isset($_COOKIE['Drupal_visitor_city'])){
                $default_city = $_COOKIE['Drupal_visitor_city'];
            }
        	$teamid = 0;
            $default_team = '';            
        	if (isset($_GET['teamid'])) {
            	$teamid = $_GET['teamid'];
            	$default_team = $teamid;
            	$team_info = Node::load($teamid);
            	if (!empty($team_info->field_trailwalker_cities->entity)) {
                    $default_city = $team_info->field_trailwalker_cities->entity->get('tid')->value;
                    $team_year = $team_info->get('field_year_field')->value;
                }
                $default_select_type = 'teams';            	
            }elseif(isset($_GET['memberid'])){
                
               $memberid = $_GET['memberid'];
               
               $default_team = $memberid;
               $teammemberdetail = node::load($memberid);
               
               if(!empty($teammemberdetail)){
                    $member_year = $teammemberdetail->get('field_year_field')->value;                                       
                    $member_city = $teammemberdetail->get('field_trailwalker_cities')->target_id;                                                      
                    $default_city = $member_city;
                }  
                          
            
               $default_select_type = 'user'; 
            }else{
                $default_select_type = 'teams';
            }                        
            $vocabulary_name = 'trailwalker_cities'; //name of your vocabulary
            $query = \Drupal::entityQuery('taxonomy_term');
            $query->condition('vid', $vocabulary_name);
            $query->condition('field_term_status', 1);
            $query->sort('weight');
            $tids = $query->execute();
            $terms = Term::loadMultiple($tids);
            $i = 0;
            foreach($terms as $term) {
                $name = $term->getName();
                $tid = $term->tid->value;
                $term_names[$tid] = $name;
                if ($i == 0) {
                	if ($default_city == 0) {
                		$default_city = $tid;
                	}
                }
                $i++;
            }

                         
            $sqlc = "select td.tid, td.name from taxonomy_term_field_data td 
            		inner join taxonomy_term_hierarchy th on th.tid = td.tid
            		where th.parent = 0 
            		and td.vid='country_state_list'";
            $resultc = db_query($sqlc);
            $countries_datasc  = $resultc->fetchAll();
            
            $countries = array('' => 'Country');
            foreach ($countries_datasc as $val) {
                
            	$countries[$val->tid] = ucfirst($val->name);
            }
            
            $state_value = "";
            if(\Drupal::state()->get('donation_submission_data')){
                $state_value = \Drupal::state()->get('donation_submission_data')['state_value'];
            }
            $form['state_value'] = array(
                '#type' => 'hidden',
                '#attributes' => ['class' => ['state_value']], 
                '#default_value' => $state_value,
            );

            $form['step_donation'] = array(
                '#type' => 'container',
                '#title' => t('Donation'),
                '#title_display' => 'invisible',
                '#prefix' => '<div class="tab-pane fade show active" id="Donation" role="tabpanel" aria-labelledby="Donation-tab">',
                '#suffix' => '</div>',
            );

            $form['step_details'] = array(
                '#type' => 'container',
                '#title' => t('Personal Details'),
                '#title_display' => 'invisible',
                '#prefix' => '<div class="tab-pane fade" id="Personal-Details" role="tabpanel" aria-labelledby="Personal-Details-tab">',
                '#suffix' => '</div>',
            );

            $form['step_payment'] = array(
                '#type' => 'container',
                '#title' => t('Payment'),
                '#title_display' => 'invisible',
                '#prefix' => '<div class="tab-pane fade" id="Payment" role="tabpanel" aria-labelledby="Payment-tab">',
                '#suffix' => '</div>',
            );

            $form['step_donation']['select_recipient'] = array(
                '#type' => 'container',
                '#id' => 'dropdown-second-replace',
            );

            $form['step_donation']['select_recipient']['select_city'] = array(
                '#type' => 'radios',
                '#title' => $this->t('<p class="mb-1 font-weight-semi">Select City:</p>'),
                '#default_value' => $default_city,//default 4389
                '#options' => $term_names,
                
                '#after_build' => array('donations_process_radios'),
                '#prefix' => '<h5 class="font-weight-x-semi uppercase">Donate To</h5>
                            <div class="row">',
                '#ajax' => array(
                    'event' => 'change',
                    'callback' => '::recipient_callback',
                    'wrapper' => 'dropdown-second-replace',
                    'progress' => [
                        'type' => 'throbber',
                        'message' => t('Updating...'),
                      ],
                ),
            );

            $form['step_donation']['select_recipient']['select_type'] = array(
                '#type' => 'radios',
                '#title' => $this->t('<p class="mb-1 font-weight-semi">Select Type:</p>'),
                '#default_value' => $default_select_type,
                '#options' => array(
                    'teams' => $this->t('Team'),
                    'user' => $this->t('Member'),
                ),
                
                '#after_build' => array('donations_process_radios'),
                '#suffix' => '</div>',
                '#ajax' => array(
                    'event' => 'change',
                    'callback' => '::recipients_callback',
                    'wrapper' => 'dropdown-second-replace',
                    'progress' => [
                        'type' => 'throbber',
                        'message' => t('Updating.......'),
                    ],
                ),
            );
            
            if ($form_state->getValues() == NULL) {
                
                if (isset($_GET['teamid'])) {
                    $type = 'teams';                              	
                }elseif(isset($_GET['memberid'])){
                   $type = 'user';                
                }else{
                    $type = 'teams';
                }
            	$recipients = $this->getRecipients($default_city, $type);
                if($recipients == '' || empty($recipients)){                    
                    unset($form);
                    $message = 'The selected city does not have any active events, so you cannot donate. Please change the city and try again.';
                    $form['eventsdata'] = array(
                            '#type' => 'container',
                            '#markup' => $message,
                    );
                    return $form;
                   
                    $recipients = array();
                }
            } else {
               
            	$selected_city = $form_state->getvalue('select_city');
            	$selected_type = $form_state->getvalue('select_type');
            	
            	$recipients = $this->getRecipients($selected_city, $selected_type);
            }            
            $form['step_donation']['select_recipient']['field_donate_to_type'] = array(
                '#type' => 'select',
                '#attributes' => ['class' => ['form-control donate-to-type', 'selectpicker'],'placeholder'=>"Select"],
                '#options' => $recipients,
                '#empty_option' => '-Select-',
                '#empty_value' => '',
                '#prefix' => '<div class="row">
                                <div class="col-md-12 mb-4">
                                    <h5 class="uppercase font-weight-x-semi">Select To whom you want to donate</h5>
                                    <div class="form-group control-label">
                              <label class="control-label">Contribute To:</label></div>',
                '#suffix' => '</div>
                            </div>',
            	'#default_value' => $default_team
            );

            $form['step_donation']['quick_amount'] = array(
                '#type' => 'container',
                '#id' => 'quick-amount',
            );

            $form['step_donation']['quick_amount']['payment_detail'] = array(
                '#type' => 'radios',
                '#title' => $this->t('<h5 class="uppercase font-weight-x-semi">Payment Detail</h5>'),
                '#allowed_tags' => ['i'],
                '#options' => array(
                    '20000' => $this->t('<i class="fas fa-rupee-sign"></i> 20,000'),
                    '10000' => $this->t('<i class="fas fa-rupee-sign"></i> 10,000'),
                    '5000' => $this->t('<i class="fas fa-rupee-sign"></i> 5,000'),
                    'other_amount' => $this->t('Other'),
                ),
                '#default_value' => '20000',
                '#after_build' => array('quick_amount_process_radios'),
              
            );

            
                $form['step_donation']['quick_amount']['other_donation'] = array(
                    '#type' => 'textfield',
                    '#title' => $this->t('Enter the amount below:'),
                    '#allowed_tags' => ['i'],
                    '#default_value'=>20000,
                    '#label_attributes' => ['class' => ['control-label']],
                    '#attributes' => ['class' => ['form-control', 'mb-1','other_amt']],
                    '#wrapper_attributes' => ['class' => ['form-group']],
                   
                    '#description' => $this->t('<p class="font-size-sm">* Please enter a minimum amount of <i class="fas fa-rupee-sign"></i> 100</p>'),
                    '#prefix' => '<div class="row other_amt_div d-none" ><div class="col-md-6">',
                    '#suffix' => '</div></div>',
                    
                );
            

            $form['step_donation']['donor_message'] = array(
                '#type' => 'textarea',
                '#attributes' => array('class' => array('form-control', 'mb-1')),
                '#rows' => 3,
                '#wrapper_attributes' => ['class' => array('form-group')],
                '#placeholder' => "Enter Your Message...",
                '#title' => $this->t('Message for the team:'),
                '#label_attributes' => ['class' => 'control-label'],
                '#prefix' => '<div class="row"><div class="col-md-12">',
                '#suffix' => '</div></div>',
            );
            
            

            $form['step_donation']['certificate_name'] = array(
                '#type' => 'textfield',
                '#attributes' => array('class' => array('form-control','mb-1')),   
                '#wrapper_attributes' => ['class' => array('form-group')],
                '#placeholder' => "Enter Name",
                '#title' => $this->t('Certificate to be issued on which name'),
                '#label_attributes' => ['class' => 'control-label'],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>'
            );

            $form['step_donation']['bulletin'] = array(
                '#type' => 'checkbox',
                '#title' => $this->t('I would like to receive Bulletins from Oxfam India'),
                '#attributes' => ['class' => ['custom-control-input']],
                '#wrapper_attributes' => ['class' => ['custom-control', 'custom-checkbox', 'custom-checkbox-md']],
            	'#label_attributes' => ['class' => ['custom-control-label']],
                '#prefix' => '<div class="d-lg-flex justify-content-lg-between">',
                '#suffix' => '<span class="btn btn-primary donation-next donation-next-step-1">Next</span></div>',
            );

            $form['step_donation']['my_field'] = array(
                '#type' => 'select_or_other',
                '#title' => t('Choose an option'),
                '#default_value' => array('value_1'),
                '#options' => array(
                  'value_1' => t('One'),
                  'value_2' => t('Two'),
                  'value_3' => t('Three'),
                ),
                '#other' => t('Other (please type a value)'),   // Text to show as 'other' option
                '#required' => TRUE,
                '#multiple' => TRUE,
                '#other_unknown_defaults' => 'other', // If the #default_value is not a valid choice in #options, what should we do? Possible values 'append', 'ignore', 'other'  (if 'other' specified you can also override #other_delimiter).
                '#other_delimiter' => ', ', // Delimiter string to delimit multiple 'other' values into the 'other' textfield.  If this is FALSE only the last value will be used.
                '#select_type' => 'select', // Defaults to 'select'.  Can also be 'radios' or 'checkboxes'.
              );

            $form['step_details']['personal_details'] = array(
                '#type' => 'container',
                '#title' => $this->t('Personal Details'),
                '#attributes' => ['class' => ['row', 'mb-4']],
                '#prefix' => '<h5 class="font-weight-x-semi uppercase">Personal Details</h5>',
            );
            
            $form['step_details']['personal_details']['first_name'] = array(
                '#type' => 'textfield',
                '#placeholder' => t('First Name'),
                '#title' => t('FIRST NAME'),
                '#title_display' => 'invisible',
                '#attributes' => ['class' => ['form-control']],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
            );

            $form['step_details']['personal_details']['last_name'] = array(
                '#type' => 'textfield',
                '#placeholder' => t('Last Name'),
                '#title' => t('LAST NAME'),
                '#title_display' => 'invisible',
                '#attributes' => ['class' => ['form-control']],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
            );

            $form['step_details']['personal_details']['e_mail'] = array(
                '#type' => 'email',
                '#placeholder' => t('E-mail'),
                '#title' => t('EMAIL'),
                '#title_display' => 'invisible',
                '#attributes' => ['class' => ['form-control']],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
            );

            $form['step_details']['personal_details']['mobile'] = array(
                '#type' => 'textfield',
                '#placeholder' => t('Mobile Number'),
                '#title' => t('MOBILE NUMBER'), 
                '#title_display' => 'invisible',
                '#attributes' => ['class' => ['form-control'], 'maxlength' => 10],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
                '#id' => 'donationmob'
            );

            $form['step_details']['personal_details']['gender'] = array(
                '#type' => 'select',
                '#options' => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other',
                ],
                '#empty_option' => 'Gender',
                '#empty_value' => '',
                '#attributes' => ['class' => ['form-control selectpicker']],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
            );

            $form['step_details']['personal_details']['date_of_birth'] = array(
                '#type' => 'textfield',
                '#field_prefix' => '<div class="input-group donatenow-form">',
                '#placeholder' => t('Date of Birth'),
                '#title' => t('DATE OF BIRTH'),
                '#title_display' => 'invisible',
                '#attributes' => ['class' => ['form-control'], 'id' => 'date-of-birth', 'autocomplete' => 'off'],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6"> <div class="form-group"> ',
                '#suffix' => '</div> </div> ',
                '#field_suffix'=> '<span class="input-group-append h-100 date-of-birth">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt date-of-birth1"></i></span>
                                    </span></div>',
            );

            $form['step_details']['personal_address'] = array(
                '#type' => 'container',
                '#title' => $this->t('Personal Address'),
                '#attributes' => ['class' => ['row']],
                '#prefix' => '<h5 class="font-weight-x-semi uppercase">Contact Details</h5>',
                '#suffix' => '<div class="note mb-2">
                                <div class="note_label red-text">Note:</div>
                                    <div class="note_text">
                                        <p class="font-size-sm font-weight-semi">NOTE: Please make sure that you put complete and correct details for us to issue the donation certificate under section 80G of Income Tax Act, India.</p>
                                    </div>
                                </div>
                                <hr>',
            );

            $form['step_details']['personal_address']['address_1'] = array(
                '#type' => 'textfield',
                '#placeholder' => t('Address 1'),
                '#title' => t('ADDRESS 1'),
                '#title_display' => 'invisible',
                '#attributes' => ['class' => ['form-control'], 'maxlength' => 250],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-12">',
                '#suffix' => '</div>',
            );

            

            

            $form['step_details']['personal_address']['donation_frm_location'] = array(
                '#type' => 'container',
                '#id' => 'donation-user-location',
                '#prefix' => '<div class="col-12"> <div class="row width-full">',
                '#suffix' =>  '</div> </div',
              );

            
            $form['step_details']['personal_address']['donation_frm_location']['country'] = array(
                '#type' => 'select',
                '#options' => $countries,
                '#empty_option' => 'Country',
                '#empty_value' => '',
                '#attributes' => ['class' => ['form-control selectpicker donate-country']],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
                '#validated'=>true,
               
            );

            $form['step_details']['personal_address']['donation_frm_location']['state'] = array(
                '#type' => 'select',
                '#options' => [],
                '#empty_option' => 'State',
                '#empty_value' => '',
                '#validated'=>true,
                '#attributes' => ['class' => ['form-control selectpicker donate-state']],
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
            );
            
            $form['step_details']['personal_address']['city'] = array(
            		'#type' => 'textfield',
	                '#placeholder' => t('City'),
	                '#title' => t('City'),
	                '#title_display' => 'invisible',
	                '#attributes' => ['class' => ['form-control']],
	                '#wrapper_attributes' => ['class' => ['form-group']],
            		'#prefix' => '<div class="col-md-6">',
            		'#suffix' => '</div>',
            );

            $form['step_details']['personal_address']['zip_code'] = array(
                '#type' => 'textfield',
                '#placeholder' => t('ZIP/PIN Code'),
                '#title' => t('ZIP/PIN Code'),
                '#title_display' => 'invisible',
                '#attributes' => ['class' => ['form-control']],
                '#maxlength' => 6,
                '#wrapper_attributes' => ['class' => ['form-group']],
                '#prefix' => '<div class="col-md-6">',
                '#suffix' => '</div>',
                '#id' => 'donationpin'
            );

            

            $form['step_details']['hardcopy'] = array(
                '#type' => 'checkbox',
                '#title' => $this->t('I want my donation certificate as a hard copy. <small>("Trailwalker is a green event. Let\'s all strive to save paper. We prefer to send you the donation receipt by email.")</small>'),
                '#wrapper_attributes' => ['class' => ['custom-control', 'custom-checkbox', 'custom-checkbox-md']],
                '#prefix' => '<div class="d-lg-flex justify-content-lg-between tab-next">',
                '#suffix' => '<span class="btn btn-dark donation-prev-step-2">Prev</span><span class="btn btn-primary donation-next donation-next-step-2">Next</span></div>',
            );
            
            $form['step_details']['hardcopy'] = array(
            		'#type' => 'checkbox',
            		'#allowed_tags' => ['button','i'],            		
            		'#title' => $this->t('I want my donation certificate as a hard copy. <small>("Trailwalker is a green event. Let\'s all strive to save paper. We prefer to send you the donation receipt by email.")</small>'),
            		'#attributes' => ['class' => ['custom-control-input']],
                	'#wrapper_attributes' => ['class' => ['custom-control', 'custom-checkbox', 'custom-checkbox-md', 'invisible']],
            		'#label_attributes' => ['class' => ['custom-control-label']],
            		'#prefix' => '<div class="d-lg-flex justify-content-lg-between tab-next">',
            		'#suffix' => '<span class="btn btn-dark donation-prev-step-2 mr-2">Prev</span><span class="btn btn-primary donation-next donation-next-step-2">Next</span></div>',
            );

            $form['step_payment']['payment'] = array(
                '#type' => 'container',
                '#id' => 'payment-details-options',
            	
            );

            $form['step_payment']['payment']['nationality'] = array(
            		'#type' => 'select',
            		'#title' => $this->t('Payment'),
            		'#title_display' => 'invisible',
            		'#attributes' => ['class' => ['form-control ddl-nationality']],
            		'#wrapper_attributes' => ['class' => ['form-group']],
            		'#default_value' => '',
            		
            		'#options' => array('' => 'Select Nationality', 'Indian' => 'Indian', 'NRI' => 'NRI', 'International' => 'Foreign'),
            		'#ajax' => array(
            				'event' => 'change',
            				'callback' => '::payment_option_callback',
            				'wrapper' => 'payment-details-options',
            				'progress' => [
            						'type' => 'throbber',
            						'message' => t('Updating...'),
            				],
            		),
            		'#prefix' => '<div class="col-md-6">',
            		'#suffix' => '</div>',
            );
            if($form_state->getValue('nationality') != NULL && $form_state->getValue('nationality') != ''){
            	if ($form_state->getValue('nationality') == 'Indian') {
            		$options = array(
            				
            				'paytm' => 'Pay By Paytm Wallet',
            				'online' => 'Pay Online',
            		);
            		$default_value = 'offline';
            		$form['step_payment']['payment']['pancard'] = array(
            				'#type' => 'textfield',
            				'#placeholder' => t('PAN Number (Optional)'),
                            '#title' => t('PAN NUMBER'),
                            '#maxlength' => 10,
            				'#title_display' => 'invisible',
            				'#attributes' => ['class' => ['form-control donationpan']],
            				'#wrapper_attributes' => ['class' => ['form-group']],
            				'#prefix' => '<div class="col-md-6">',
            				'#suffix' => '</div>',
            		);
            	} else if ($form_state->getValue('nationality') == 'NRI') {//exit('123');
            		$options = array(
            				
            				'paytm' => 'Pay By Paytm Wallet',
            				'online' => 'Pay Online',
            		);
            		$default_value = 'offline';
            		$form['step_payment']['payment']['passport'] = array(
            				'#type' => 'textfield',
                            '#placeholder' => 'Passport No',
                            '#maxlength' => 10,
            				'#description' => '*Required for FCRA compliance',
            				'#attributes' => ['class' => ['form-control']],
            				'#wrapper_attributes' => ['class' => ['form-group']],
            				'#prefix' => '<div class="col-md-6">',
            				'#suffix' => '</div>',
            		);
            	} else if ($form_state->getValue('nationality') == 'International') {
            		$options = array(
            				
            				'online' => 'Pay Online',
            		);
            		$default_value = 'offline';
            	} else {
            		$options = array(
            				
            				'paytm' => 'Pay By Paytm Wallet',
            				'online' => 'Pay Online',
            		);
            		$default_value = 'offline';
            	}
            	
            	$form['step_payment']['payment']['payment_option_container'] = array(
            			'#type' => 'container',
            			'#id' => 'payment-options-container',
            			'#attributes' => ['class' => ['row']],
            			
            	);
	            $form['step_payment']['payment']['payment_option_container']['payment_option'] = array(
	                '#type' => 'radios',
	                '#title' => $this->t('Payment'),
	                '#title_display' => 'invisible',
	            	'#attributes' => ['class' => ['ddl_payment_option']],
	            	
	                '#after_build' => array('donations_process_radios'),
	                '#options' => $options,
	                '#ajax' => array(
	                    'event' => 'change',
	                    'callback' => '::payment_options_callback',
	                    'wrapper' => 'payment-options-container',
	                    'progress' => [
	                        'type' => 'throbber',
	                        'message' => t('Updating...'),
	                      ],
	                ),
	            	'#prefix' => '<div class="col-md-12">',
	            	'#suffix' => '</div>',
	            );            
	
	            if($form_state->getValue('payment_option') == 'offline'){//echo $form_state->getValue('payment_option');exit('1');
	                $form['step_payment']['payment']['payment_option_container']['pay_offline'] = array(
	                    '#type' => 'container',	                    
	                    '#attributes' => ['class' => ['col-12 d-flex flex-wrap']],	                   
	                );
	    
	                $form['step_payment']['payment']['payment_option_container']['pay_offline']['cheque_no'] = array(
	                    '#type' => 'textfield',
	                    '#placeholder' => 'Cheque No',
	                    '#attributes' => ['class' => ['form-control']],
	                    '#wrapper_attributes' => ['class' => ['form-group']],
	                    '#prefix' => '<div class="col-md-6">',
	                    '#suffix' => '</div>',
	                );
	    
	                $form['step_payment']['payment']['payment_option_container']['pay_offline']['cheque_date'] = array(
	                    '#type' => 'textfield',
	                    '#placeholder' => 'Cheque date',
	                    '#attributes' => ['class' => ['form-control'], 'id' => 'check-date-donate'],
	                    '#wrapper_attributes' => ['class' => ['form-group']],
	                    '#prefix' => '<div class="col-md-6">',
	                    '#suffix' => '</div><div class="col-md-12 mt-4">
	                                        <h5 class="font-weight-x-semi uppercase purple-text">donate via cheque</h5>
	                                        <p class="font-size-sm">Dear Donor,
	                                        <br> Please make your cheque in favor of \'Oxfam India\' send it to Oxfam India.
	                                        <br> Bhavika Nagar
	                                        <br> B-304, Riddhi Siddhi Complex,
	                                        <br> M.G Road, Borivli East,
	                                        <br> Mumbai - 400 066, Maharashtra.
	                                        <br> Phone : 022-69000700
	                                        <br> Please write your name, email, address, mobile number and team name at the back of the cheque or covering letter before sending it.</p>
	                                    </div><hr>',
	                	'#field_prefix' => '<div class="input-group donatenow-form">',
	                	'#field_suffix'=> '<span class="input-group-append h-100 cheque-date-picker">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt cheque-date-picker1"></i></span>
                                    </span></div>',
	                );
	            } else {
	                switch ($form_state->getvalue('payment_option')){
	                    case 'offline':
	                        $form['step_payment']['payment']['payment_option_container']['pay_offline'] = array(
	                            '#type' => 'container',
	                            
	                            '#attributes' => ['class' => ['col-12 d-flex flex-wrap']],
	                            
	                        );
	            
	                        $form['step_payment']['payment']['payment_option_container']['pay_offline']['cheque_no'] = array(
	                            '#type' => 'textfield',
	                            '#placeholder' => 'Cheque No',
	                            '#attributes' => ['class' => ['form-control']],
	                            '#wrapper_attributes' => ['class' => ['form-group']],
	                            '#prefix' => '<div class="col-md-6">',
	                            '#suffix' => '</div>',
	                        );
	            
	                        $form['step_payment']['payment']['payment_option_container']['pay_offline']['cheque_date'] = array(
	                            '#type' => 'textfield',
	                            '#placeholder' => 'Date of Birth',
	                            '#attributes' => ['class' => ['form-control']],
	                            '#wrapper_attributes' => ['class' => ['form-group']],
	                            '#prefix' => '<div class="col-md-6">',
	                            '#suffix' => '</div><div class="col-md-12 mt-4">
	                                                <h5 class="font-weight-x-semi uppercase purple-text">donate via cheque</h5>
	                                                <p class="font-size-sm">Dear Donor,
	                                                <br> Please make your cheque in favor of \'Oxfam India\' send it to Oxfam India.
	                                                <br> Bhavika Nagar
	                                                <br> B-304, Riddhi Siddhi Complex,
	                                                <br> M.G Road, Borivli East,
	                                                <br> Mumbai - 400 066, Maharashtra.
	                                                <br> Phone : 022-69000700
	                                                <br> Please write your name, email, address, mobile number and team name at the back of the cheque or covering letter before sending it.</p>
	                                            </div>',
	                        	'#field_prefix' => '<div class="input-group donatenow-form">',
	                        	'#field_suffix'=> '<span class="input-group-append h-100 cheque-date-picker">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt cheque-date-picker1"></i></span>
                                    </span></div>',
	                        );
	                        break;
	                    case 'paytm':
	                        $form['step_payment']['payment']['payment_option_container']['pay_wallet'] = array(
	                            '#type' => 'container',
	                            '#id' => 'pay-wallet',
	                            '#suffix' => '<hr>',
	                        );
	                        break;
	                    case 'online':
	                        $form['step_payment']['payment']['payment_option_container']['pay_online'] = array(
	                            '#type' => 'container',
	                            '#id' => 'pay-online',
	                            '#attributes' => ['class' => ['col-12 d-flex flex-wrap']],
	                            '#suffix' => '<hr>',
	                        );
							
	                        if ($form_state->getValue('nationality') == 'Indian' || $form_state->getValue('nationality') == 'NRI') {
	                        	$gateway_options = array('' => 'Select Gateway', 'Debit Card' => 'Debit Card', 'Credit Card' => 'Credit Card', 'Net Banking' => 'Net Banking', 'Wallet' => 'Wallet', 'CCAvenue' => 'Others');
	                        } else if ($form_state->getValue('nationality') == 'International') {
	                        	$gateway_options = array('CCAvenue International' => 'CCAvenue International');
	                        } else {
	                        	$gateway_options = array('' => 'Select Gateway');
	                        }
	                        
	                        $form['step_payment']['payment']['payment_option_container']['pay_online']['payment_gateway'] = array(
	                        		'#type' => 'select',
				            		'#title' => $this->t('Payment'),
				            		'#title_display' => 'invisible',
				            		'#default_value' => '',
	                        		'#attributes' => ['class' => ['form-control ddl_payment_gateway']],
				            		
				            		'#options' => $gateway_options,
	                        		'#prefix' => '<div class="col-md-6">',
	                        		'#suffix' => '</div>',
	                        );
	                        break;
	                    default:
	                    	
	                        break;
	                }
	            }
        	}

            $form['step_payment']['actions']['#type'] = 'actions';
            $form['step_payment']['actions']['submit'] = array(
                '#type' => 'submit',
                '#value' => $this->t('Donate Now'),
                '#button_type' => 'primary',
                '#attributes' => array('class' => ['btn btn-success sub-btn donate-submit-btn']),
                '#prefix' => '<hr><div class="d-flex justify-content-end"><span class="btn btn-dark donation-prev-step-3 mr-2">Prev</span>',
                '#suffix' => '</div>',
            );

        return $form;
        }
        
        public function validateForm(array &$form, FormStateInterface $form_state) {
            
        }

        public function submitForm(array &$form, FormStateInterface $form_state) {
            $values = $form_state->getvalues();
            
            $first_name = trim($values['first_name']);
            $last_name = trim($values['last_name']);
            $e_mail = trim($values['e_mail']);
            $mobile = trim($values['mobile']);
            $gender = trim($values['gender']);
            $dob = trim($values['date_of_birth']);
            $address_1 = trim($values['address_1']);           
            $address_2 = '';
            $country = trim($values['country']);
            $state = trim($values['state']);
            $city = trim($values['city']);
            $zip_code = trim($values['zip_code']);
            $pan_number = trim($values['pancard']);
            $hardcopy = trim($values['hardcopy']);
            $donor_message = trim($values['donor_message']);            
            $email_to_team = 0;
            if($certificate_name != ''){
                $certificate_name = trim($values['certificate_name']);
            }
            else{
                $certificate_name = '';
            }
            $nationality = trim($values['nationality']);
            
            $city_id = trim($values['select_city']);
            $select_type = trim($values['select_type']);//teams or user
            $field_donate_to_type = trim($values['field_donate_to_type']);//user_id or team_id
            $bulletin = trim($values['bulletin']);
            $payment_option_type = trim($values['payment_option']);
            $cheque_no = '';
            $cheque_date = '';
            $gateway_method = '';
            if ($payment_option_type == 'offline') {
            	$gateway_type = 'offline';
            	$gateway_method = 'offline';
            	$cheque_no = trim($values['cheque_no']);
            	$cheque_date = trim($values['cheque_date']);
            	$cheque_date = str_replace('/', '-', $cheque_date);
            } else if ($payment_option_type == 'paytm') {
            	$gateway_type = 'paytm';
            	$gateway_method = 'paytm';
            } else if ($payment_option_type == 'online') {
            	$gateway_method = trim($values['payment_gateway']);
            }else if($payment_option_type == ''){               
                $values['payment_option'] = $payment_option_type = 'online';                                      
                if ($nationality == 'Indian' || $nationality == 'NRI') {                    
                    $gateway_method = 'CCAvenue';
                } else {                    
                    $gateway_method = 'CCAvenue International';
                }
            }

            if ($gateway_method == 'Credit Card') {
            	$payment_option = 'OPTCRDC';
            	$gateway_type = 'CCAvenue';
            } else if ($gateway_method == 'Debit Card') {
            	$payment_option = 'OPTDBCRD';
            	$gateway_type = 'CCAvenue';
            } else if ($gateway_method == 'Net Banking') {
            	$payment_option = 'OPTNBK';
            	$gateway_type = 'CCAvenue';
            } else if ($gateway_method == 'Cash Card') {
            	$payment_option = 'OPTCASHC';
            	$gateway_type = 'CCAvenue';
            } else if ($gateway_method == 'Wallet') {
            	$payment_option = 'OPTWLT';
            	$gateway_type = 'CCAvenue';
            } else if ($gateway_method == 'Mobile Payments') {
            	$payment_option = 'OPTMOBP';
            	$gateway_type = 'CCAvenue';
            } else if ($gateway_method == 'CCAvenue') {
            	$gateway_type = 'CCAvenue';
            } else if ($gateway_method == 'CCAvenue International') {
            	$gateway_type = 'CCAvenue International';
            }
            $values['gateway_type'] = $gateway_type;//for donation success or failure purpose                        
            $year = date('Y');
            $donation_amount = 0;
            if ($values['payment_detail'] != '' && $values['payment_detail'] != 'other_amount') {
            	$donation_amount = $values['payment_detail'];
            } else if ($values['payment_detail'] != '' && $values['payment_detail'] == 'other_amount') {
            	$donation_amount = $values['other_donation'];
            }
            $donation_amount = trim($donation_amount);            
            $values['price'] = $donation_amount;            
            $gateway_mode = '';
            if ($nationality == 'Indian' || $nationality == 'NRI') {
            	$gateway_mode = 'domestic';
            } else {
            	$gateway_mode = 'international';
            }
            $jsonvalue = json_encode($values);
            $conn = Database::getConnection();
            $last_id = $conn->insert('donations_log')->fields(array(
                        'donateto'=> $values['select_type'],                      
                        'amount'=> $values['price'],
                        'first_name' => $values['first_name'],
                        'last_name'=> $values['last_name'],                        
                        'mobilenumber'=> $mobile, 
                        'emailid'=> $values['e_mail'],
                        'payment_type'=> $gateway_method,
                        'payment_mode'=> $gateway_mode,
                        'countryid'=> $values['country'],
                        'stateid'=> $values['state'],
                        'fieldvalues'=> $jsonvalue,                        
                ))->execute(); 
            $passport = '';
            if (isset($values['passport'])) {
            	$passport = trim($values['passport']);
            }
            
            $country_res = Term::load($country);//for country saving in donation
            if (!empty($country_res)) {
            	$country = $country_res->get('name')->value;
            } else {
            	$country = '';
            }
            $state_res = Term::load($state);//for country saving in donation
            if (!empty($state_res)) {
            	$state = $state_res->get('name')->value;
            } else {
            	$state = '';
            }
            
            $team_id = '';
            $user_id = '';
            $event_id = 0;
            $values['donated_to'] = '';
            $values['emails_to'] = array();
            if ($select_type == 'teams') {
            	$team_id = $field_donate_to_type;
            	$user_id = '';
            	$query = \Drupal::entityQuery('node')
						->condition('type', 'teams')
						->condition('nid', $team_id)
						->condition('field_trailwalker_cities', $city_id);
            			
            	$ids = $query->execute();
            	$ids = implode($ids);
            	
				$team_info = Node::load($ids);
				if (!empty($team_info)) {
					$event_id = $team_info->get('field_event_entity_data')->target_id;
					$donated_to_name = $team_info->getTitle();
					$values['donated_to'] = 'Team - '.$donated_to_name;
					/*used to create donation post timeline start*/
					$values['donated_to_type'] = 'Team';
					$values['donated_to_id'] = $team_id;
					$values['donated_to_city'] = $city_id;
					/*used to create donation post timeline start*/
				}
            	if ($email_to_team) {
            		$query2 = \Drupal::entityQuery('node')
            		->condition('type', 'team_member')
            		->condition('field_team_id', $team_id)
            		->condition('field_trailwalker_cities', $city_id);            		
            		$teammemberids = $query2->execute();            		
            		$team_member_info = Node::loadMultiple($teammemberids);
            		if (!empty($team_member_info)) {
            			foreach ($team_member_info as $t_info) {
            				$values['emails_to'][$t_info->field_user_id_value->entity->get('name')->value] = $t_info->field_user_id_value->entity->get('mail')->value;
            			}
            		}
            		
            	}
            } else {
            	$team_id = 0;
            	$user_id = $field_donate_to_type;
                $team_member_id = $user_id;
                $team_member_data = Node::load($team_member_id);
                $event_id = $team_member_data->field_team_id->entity->field_event_entity_data->target_id;
                $user_id = $team_member_data->field_user_id_value->target_id;
            	$user_data = user::load($user_id);
                $donated_to_name = "";
                if($user_data!="" && !empty($user_data)){ 
                    $donated_to_name = $user_data->get('name')->value;   
                } 
                $values['donated_to'] = 'Member - '.$donated_to_name;            		
                /*used to create donation post timeline start*/
                $values['donated_to_type'] = 'User';
                $values['donated_to_id'] = $user_id;
                $values['donated_to_city'] = $city_id;                
            }            
            if($values['first_name'] == "test_donation_rbt"){
				$donation_amount = 1;
            }
            // updating the donor details in log table
            $donor_array = array(
                'type'        => 'donor',
            		'title' => $first_name,
            		'field_donor_first_name' => $first_name,
            		'field_donor_last_name'    => $last_name,
            		'field_donor_e_mail' => $e_mail,
            		'field_donor_mobile_number'  => $mobile,
            		'field_donor_gender' => $gender,
            		'field_donor_date_of_birth' => date('Y-m-d', strtotime($dob)),
            		'field_donor_address_1'        =>    $address_1,
            		'field_donor_address_2'       =>    $address_2,
            		'field_donor_country'	=> $country,
            		'field_donor_state'	=> $state,
            		'field_donor_city'	=> $city,
            		'field_donor_zip_code'     =>    $zip_code,
            		'field_donor_pan_number' => $pan_number,
                    'field_donations_hardcopy' => $hardcopy,
                    'field_nationality' => $nationality
            );
            $donor_values =   json_encode($donor_array);            
            $conn->update('donations_log')->fields(['donor_values' => $donor_values,])
            ->condition('id', $last_id, '=')->execute();
            // end
            $donor_node = Node::create([
            		'type'        => 'donor',
            		'title' => $first_name,
            		'field_donor_first_name' => $first_name,
            		'field_donor_last_name'    => $last_name,
            		'field_donor_e_mail' => $e_mail,
            		'field_donor_mobile_number'  => $mobile,
            		'field_donor_gender' => $gender,
            		'field_donor_date_of_birth' => date('Y-m-d', strtotime($dob)),
            		'field_donor_address_1'        =>    $address_1,
            		'field_donor_address_2'       =>    $address_2,
            		'field_donor_country'	=> $country,
            		'field_donor_state'	=> $state,
            		'field_donor_city'	=> $city,
            		'field_donor_zip_code'     =>    $zip_code,
            		'field_donor_pan_number' => $pan_number,
                    'field_donations_hardcopy' => $hardcopy,
                    'field_nationality' => $nationality
            ]);
            $donor_node->save();
            $values['donor_id'] = $donor_node->id();
            $donor_id = $values['donor_id'];
            $values['donor_name'] = $first_name;
            $field_donation_to_team = $team_id;
            $field_donation_to_user  = $user_id;
            // updating donation detail in log table
            $donation_array = array(
                'type'        => 'donations',
            		'title' => $values['donated_to'],
            		'uid' => \Drupal::currentUser()->id(),
            		'field_donate_to_type'    => $select_type,
            		'field_donation_to_team' => $team_id,
            		'field_donation_to_user'  => $user_id,
            		'field_donation_amount' => $donation_amount,
            		'field_mer_amount_' => $donation_amount,
            		'field_email_to_team' => $email_to_team,
            		'field_donor_message'  => $donor_message,
            		'field_donor_name_on_certificate' =>  $certificate_name,
            		'field_trailwalker_cities'    =>    $city_id,
            		'field_event_entity_data'    =>    $event_id,
            		'field_donor_details_id'     =>    $values['donor_id'],
            		'field_billing_name'        =>    $first_name,
            		'field_billing_email'        =>    $e_mail,
            		'field_billing_address'	=> $address_1.' '.$address_2,
            		'field_billing_city'	=> $city,
            		'field_billing_state'	=> $state,
            		'field_billing_country'	=> $country,
            		'field_billing_zip' => $zip_code,
            		'field_gateway_type' => $gateway_type,//offline or paytm or ccavenue or ccavenue international
            		'field_gateway_mode' => $gateway_mode,//domestic or international
            		'field_payment_type' => $gateway_method,//online offline debit card credit card
            		'field_payment_for' => 'donation',//for registration or donation
            		'field_contributor_ip' => $_SERVER['REMOTE_ADDR'],
            		'field_cheque_number' => $cheque_no,
            		'field_cheque_due_date' => date('Y-m-d', strtotime($cheque_date)),
            		'field_trans_date' => date('Y-m-d\TH:i:s'),
            		'field_bulletin' => $bulletin
            );
            $donation_values =   json_encode($donation_array);             
            $conn->update('donations_log')->fields(['donation_values' => $donation_values,])
            ->condition('id', $last_id, '=')->execute();
            // end
            $node = Node::create([
            		'type'        => 'donations',
            		'title' => $values['donated_to'],
            		'uid' => \Drupal::currentUser()->id(),
            		'field_donate_to_type'    => $select_type,
            		'field_donation_to_team' => $team_id,
            		'field_donation_to_user'  => $user_id,
            		'field_donation_amount' => $donation_amount,
            		'field_mer_amount_' => $donation_amount,
            		'field_email_to_team' => $email_to_team,
            		'field_donor_message'  => $donor_message,
            		'field_donor_name_on_certificate' =>  $certificate_name,
            		'field_trailwalker_cities'    =>    $city_id,
            		'field_event_entity_data'    =>    $event_id,
            		'field_donor_details_id'     =>    $values['donor_id'],
            		'field_billing_name'        =>    $first_name,
            		'field_billing_email'        =>    $e_mail,
            		'field_billing_address'	=> $address_1.' '.$address_2,
            		'field_billing_city'	=> $city,
            		'field_billing_state'	=> $state,
            		'field_billing_country'	=> $country,
            		'field_billing_zip' => $zip_code,
            		'field_gateway_type' => $gateway_type,//offline or paytm or ccavenue or ccavenue international
            		'field_gateway_mode' => $gateway_mode,//domestic or international
            		'field_payment_type' => $gateway_method,//online offline debit card credit card
            		'field_payment_for' => 'donation',//for registration or donation
            		'field_contributor_ip' => $_SERVER['REMOTE_ADDR'],
            		'field_cheque_number' => $cheque_no,
            		'field_cheque_due_date' => date('Y-m-d', strtotime($cheque_date)),
            		'field_trans_date' => date('Y-m-d\TH:i:s'),
            		'field_bulletin' => $bulletin
            ]);
            $node->save();
            $values['donation_id'] = $node->id();
            $order_id = 'twdonate'.$values['donation_id'];
            $values['order_id'] = $order_id;
            
            $donation_node = Node::load($node->id());
            $donation_node->set('field_order_status', 'In progress');
            $donation_node->set('field_order_id', $order_id);
            $donation_node->save();
            // news bulletin insert 
            if($bulletin == 1){
                $webformvalues = [
                    'webform_id' => 'newsletter',
                    'entity_type' => NULL,
                    'entity_id' => NULL,
                    'in_draft' => FALSE,
                    'uid' => \Drupal::currentUser()->id(),
                    'langcode' => 'en',
                    'token' => 'pgmJREX2l4geg2RGFp0p78Qdfm1ksLxe6IlZ-mN9GZI',
                    'uri' => '/public/donate/add',           
                    'data' => [              
                    'name' => $first_name.' '.$last_name,
                    'telephone' => $mobile,
                    'email' => $e_mail,
                    ],
                ];  			
                $webform = Webform::load($webformvalues['webform_id']);			
                $is_open = WebformSubmissionForm::isOpen($webform);        
                if ($is_open === TRUE) {                                          
                    $errors = WebformSubmissionForm::ValidateValues($webformvalues); 
                    if(empty($errors)){
                        $webform_submission =  WebformSubmissionForm::submitValues($webformvalues);    
                    } 						           
                }
            }
            $num_updated = $conn->update('donations_log')
            ->fields([
                'donationid' => $values['donation_id'],
                'donorid' => $values['donor_id'],
                'transactionid' => $values['order_id'],
                'teamid' => $team_id,
                'userid' => $user_id,
            ])
            ->condition('id', $last_id, '=')
            ->execute();	           
            \Drupal::state()->set('donation_submission_data', $values);//session data to paas in other function            
/* Start For 1 Rupee condition */
		if($zip_code == '121212' && $city == 'oxfamdel' && $address_1 == 'addoxfam' )
			{
				$donation_amount = 1;
			}
/* End 1 Rupee condition */
            if ($gateway_type == 'paytm') {
            	header("Pragma: no-cache");
            	header("Cache-Control: no-cache");
            	header("Expires: 0");            	
            	$final = date("Y-m-d", strtotime("+1 month"));            	 
            	$paytm = new ConfigPaytm();
            	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
						<html>
						<head>
						<title>Merchant Check Out Page</title>
						<meta name="GENERATOR" content="Evrsoft First Page">
						</head>
						<body>
							<form method="post" name="paytmcheckoutform" action="/pgredirect">
							<input type="hidden" id="SUBS_EXPIRY_DATE" tabindex="1" maxlength="20" size="20" name="SUBS_EXPIRY_DATE" autocomplete="off" value="'.$final.'">
							<input type="hidden" id="ORDER_ID" tabindex="1" maxlength="20" size="20" name="ORDER_ID" autocomplete="off" value="'.$order_id.'">
							<input type="hidden" title="CUST_ID" tabindex="10"  name="CUST_ID" value="'.$donor_id.'">
							<input type="hidden" id="INDUSTRY_TYPE_ID" tabindex="4" maxlength="12" size="12" name="INDUSTRY_TYPE_ID" autocomplete="off" value="'.$paytm->PAYTM_INDUSTRY_TYPE_IDE.'">
							<input type="hidden" id="CHANNEL_ID" tabindex="4" maxlength="12" size="12" name="CHANNEL_ID" autocomplete="off" value="'.$paytm->PAYTM_CHANNEL.'">
							<input type="hidden" title="TXN_AMOUNT" tabindex="10"  name="TXN_AMOUNT" value="'.$donation_amount.'">
							<input type="hidden" title="CALLBACK_URL" tabindex="10"  name="CALLBACK_URL" value="">
							</form>
						    <script language="javascript">document.paytmcheckoutform.submit();</script>
						</body>
                        </html>';
                $form_updated = $conn->update('donations_log')
                ->fields([
                    'form_data' => $html,               
                ])
                ->condition('id', $last_id, '=')
                ->execute();
            	echo $html; exit;
            } else if ($gateway_type == 'CCAvenue') {            	
            	include('Crypto.php');//for data encryption
            	$ccavenue_config = \Drupal::config('donations.ccavenue_config');
            	$marchant_id = $ccavenue_config->get('marchant_id');
            	$working_key = $ccavenue_config->get('working_key');
            	$access_code = $ccavenue_config->get('access_code');
            	$site_redirect_url = $ccavenue_config->get('site_redirect_url');
            	$site_cancel_url = $ccavenue_config->get('site_cancel_url');
            	$ccavenue_url = $ccavenue_config->get('ccavenue_url');            	 
            	$cc_avenue_data = array();
            	$cc_avenue_data['merchant_id'] = $marchant_id;
            	$cc_avenue_data['order_id'] = $order_id;
            	$cc_avenue_data['amount'] = $donation_amount;
            	$cc_avenue_data['currency'] = 'INR';
            	$cc_avenue_data['redirect_url'] = $site_redirect_url;
            	$cc_avenue_data['cancel_url'] = $site_cancel_url;
            	$cc_avenue_data['language'] = 'EN';
            	$cc_avenue_data['billing_name'] = $first_name;
            	$cc_avenue_data['billing_address'] = $address_1.' '.$address_2;
            	$cc_avenue_data['billing_city'] = $city;
            	$cc_avenue_data['billing_state'] = $state;
            	$cc_avenue_data['billing_zip'] = $zip_code;
            	$cc_avenue_data['billing_country'] = ucfirst($country);
            	$cc_avenue_data['billing_tel'] = $mobile;
            	$cc_avenue_data['billing_email'] = $e_mail;
            	$cc_avenue_data['delivery_name'] = $first_name;
            	$cc_avenue_data['delivery_address'] = $address_1.' '.$address_2;
            	$cc_avenue_data['delivery_city'] = $city;
            	$cc_avenue_data['delivery_state'] = $state;
            	$cc_avenue_data['delivery_zip'] = $zip_code;
            	$cc_avenue_data['delivery_country'] = $country;
            	$cc_avenue_data['delivery_tel'] = $mobile;
            	$cc_avenue_data['payment_option'] = $payment_option;
            	$cc_avenue_data['merchant_param1'] = '';            	 
            	$merchant_data='';
            	foreach ($cc_avenue_data as $key => $value){
            		$merchant_data.=$key.'='.urlencode($value).'&';
            	}            	
            	$encrypted_data = encrypt($merchant_data,$working_key); // Method for encrypting the data.
            	$html = '
            	<html>
			    <head>
			    <title> Non-Seamless-kit</title>
			    </head>
			    <body>
			    <center><form method="post" name="redirect" action="'.$ccavenue_url.'">
		            	    <input type="hidden" name="encRequest" value="'.$encrypted_data.'">
		            	    <input type="hidden" name="access_code" value="'.$access_code.'">
		            	    		</form>
		            	    		</center>
			    <script language="javascript">document.redirect.submit();</script>
			    </body>
                </html>';
                $form_updated = $conn->update('donations_log')
                ->fields([
                    'form_data' => $html. 'Merchent_Data='. $merchant_data,               
                ])
                ->condition('id', $last_id, '=')
                ->execute();
			    echo $html; exit;
			    //ccavenue payment settings end
            } else if ($gateway_type == 'CCAvenue International') {
            	//ccavenue payment settings
            	include('Crypto.php');//for data encryption
            	$ccavenue_config = \Drupal::config('donations.ccavenue_config');
            	$marchant_id = $ccavenue_config->get('international_marchant_id');
            	$working_key = $ccavenue_config->get('international_working_key');
            	$access_code = $ccavenue_config->get('international_access_code');
            	$site_redirect_url = $ccavenue_config->get('site_redirect_url');
            	$site_cancel_url = $ccavenue_config->get('site_cancel_url');
            	$ccavenue_url = $ccavenue_config->get('ccavenue_url');
   
            	$cc_avenue_data = array();
            	$cc_avenue_data['merchant_id'] = $marchant_id;
            	$cc_avenue_data['order_id'] = $order_id;
    			$cc_avenue_data['amount'] = $donation_amount;
    			$cc_avenue_data['currency'] = 'INR';
                $cc_avenue_data['redirect_url'] = $site_redirect_url;
                $cc_avenue_data['cancel_url'] = $site_cancel_url;
                $cc_avenue_data['language'] = 'EN';
    			$cc_avenue_data['billing_name'] = $first_name;
                $cc_avenue_data['billing_address'] = $address_1.' '.$address_2;
                $cc_avenue_data['billing_city'] = $city;
    			$cc_avenue_data['billing_state'] = $state;
    			$cc_avenue_data['billing_zip'] = $zip_code;
    			$cc_avenue_data['billing_country'] = ucfirst($country);
                $cc_avenue_data['billing_tel'] = $mobile;
                $cc_avenue_data['billing_email'] = $e_mail;                	 
                $merchant_data='';
                foreach ($cc_avenue_data as $key => $value){
                	$merchant_data.=$key.'='.urlencode($value).'&';
                }               
                $encrypted_data = encrypt($merchant_data,$working_key); // Method for encrypting the data.
                $html = '
                	<html>
                	<head>
                	<title> Non-Seamless-kit</title>
				    </head>
				    <body>
				    <center><form method="post" name="redirect" action="'.$ccavenue_url.'">
				    	    <input type="hidden" name="encRequest" value="'.$encrypted_data.'">
				    	    <input type="hidden" name="access_code" value="'.$access_code.'">
				    	    		</form>
			            	    	    		</center>
				    <script language="javascript">document.redirect.submit();</script>
				    </body>
                    </html>';
                    $form_updated = $conn->update('donations_log')
                    ->fields([
                        'form_data' => $html.'Merchent_Data='.$merchant_data,               
                    ])
                    ->condition('id', $last_id, '=')
                    ->execute();
				    echo $html; exit;
				    //ccavenue payment settings end
            } else {
            	$form_state->setRedirect('donations.generic-donation-response');
            	return;            	
            }            
        }

            public function getRecipients($city, $type){                
                $teamid = 0;
                $default_team = '';
                if (isset($_GET['teamid'])) {
                    $teamid = $_GET['teamid'];
                    $default_team = $teamid;
                    $team_info = Node::load($teamid);
                    if (!empty($team_info->field_trailwalker_cities->entity)) {                        
                        $team_year = $team_info->get('field_year_field')->value;
                        $team_id = $team_info->id();                        
                        $team_title = $team_info->get('title')->value;
                    }                   
                }
                if(isset($_GET['memberid'])){
                    $teammemberdetail = node::load($_GET['memberid']);
                   
                    if(!empty($teammemberdetail)){
                        $member_year = $teammemberdetail->get('field_year_field')->value;
                        $member_city = $teammemberdetail->get('field_trailwalker_cities')->target_id;                                                      
                    }                    
                }                                              
			switch ($type) {
                case 'user':                    
                	$user_ids = [];
                	$user_titles = ['0' => '-Select-'];
		            if (isset($_GET['teamid'])) {//this condition is to get related team users only if user select type = user
		            	$teamid = $_GET['teamid'];
		            	$query = \Drupal::entityQuery('node')
			            	->condition('type', 'team_member')
			            	->condition('field_trailwalker_cities', $city)
		            		->condition('field_team_id', $teamid);
		            	$ids = $query->execute();		            	
		            	if (!empty($ids)) {
		            		$user_rel = Node::loadMultiple($ids);		            		
		            		if (!empty($user_rel)) {
		            			foreach ($user_rel as $user_val) {
		            				$user_ids[] = $user_val->field_user_relation_id->entity->uid->value;
		            				$team_name = $user_val->field_team_id->entity->title->value;                                    
                                    $user_titles[$user_val->get('nid')->value] = ucwords($user_val->field_user_id_value->entity->field_first_name->value).' '.($user_val->field_user_id_value->entity->field_last_name->value).'-'.$team_name;
		            				asort($user_titles);
		            			}		            			
		            			$userIds = implode(',', $user_ids);
		            			$member_ids = @$userIds;		            			
		            		}
		            	}
		            } else {
                        $years = [0]; 
                        if(isset($team_year) && $team_year < date('Y')){ 
                            for($i = $team_year; $i<=date('Y'); $i=$i+1){
                             $years[] = $i;
                            } 
                        }elseif(isset($_GET['memberid'])){
                            $years = $member_year;                           
                        }else{
							$events_query = \Drupal::entityQuery('node')
								->condition('type', 'events')
								->condition('field_trailwalker_cities', $city)
								->condition('status', 1)            			
								->condition('field_registration_start_date.value',  date('Y-m-d'), '<=')
								->condition('field_registration_end_date.value',  date('Y-m-d'), '>=');
							$ids = $events_query->execute(); 
            				$eventdetails = node::load(reset($ids));	
            				$year = date('Y',strtotime($eventdetails->get('field_start_date')->value));
							$years[] = $year;							
                        }                                           
                        $query = \Drupal::entityQuery('node')
                        ->condition('type', 'teams')
                        ->condition('status', 1)
                        ->condition('field_trailwalker_cities', $city)
                        ->condition('field_year_field', $years,'IN')
                        ->sort('title', 'asc');
                        $ids1 = $query->execute(); 
                        if(!empty($ids1)){
                            $query1 = \Drupal::entityQuery('node')
                            ->condition('type', 'team_member')
                            ->condition('field_status_member', 1)
                            ->condition('field_team_id', $ids1 ,'IN' );
                            $ids = $query1->execute();
                        }                		
                		if (!empty($ids)) {
                			$user_rel = Node::loadMultiple($ids);
                			//echo '<pre>';print_r($user_rel);exit;
                			if (!empty($user_rel)) {
                				foreach ($user_rel as $user_val) {
                                    $user_ids[] = $user_val->field_user_id_value->entity->uid->value;
                                    $team_name = $user_val->field_team_id->entity->title->value;
                					$user_titles[$user_val->get('nid')->value] = ucwords($user_val->field_user_id_value->entity->field_first_name->value).' '.ucwords($user_val->field_user_id_value->entity->field_last_name->value).' - '.$team_name;
                					asort($user_titles);
                				}                				
                				$userIds = implode(',', $user_ids);
                				$member_ids = @$userIds;                				
                			}
                		}
		            }                	
                	if (isset($user_titles['0'])) {
						$user_titles = array('' => '-Select-') + $user_titles;
						unset($user_titles['0']);
					}
                	
                	$recipients = $user_titles;
                	break;        
                case 'teams':               
                    $years = [0];                                      
                    if(isset($team_year) && $team_year < date('Y')){                        
                        for($i = $team_year; $i<=date('Y'); $i=$i+1){
                            $years[] = $i;
                        }                        
                    }else{
						$events_query = \Drupal::entityQuery('node')
								->condition('type', 'events')
								->condition('field_trailwalker_cities', $city)
								->condition('status', 1)            			
								->condition('field_registration_start_date.value',  date('Y-m-d'), '<=')
								->condition('field_end_date.value',  date('Y-m-d'), '>=');
							$ids = $events_query->execute(); 
            				$eventdetails = node::load(reset($ids));	
                            if(!empty($eventdetails) && $eventdetails !=''){
            				    $year = date('Y',strtotime($eventdetails->get('field_start_date')->value));
                            }
							$years[] = $year;                        
                    }                                                                                                          
                    $team_titles = array();         
                    if(!empty($eventdetails) && $eventdetails !=''){                                      
                        $query = \Drupal::entityQuery('node')
                                    ->condition('type', 'teams')
                                    ->condition('status', 1)
                                    ->condition('field_trailwalker_cities', $city)
                                    ->condition('field_year_field', $years,'IN')
                                    ->sort('title', 'asc');
                        $ids = $query->execute(); 
                        $teams = Node::loadMultiple($ids);
                        $team_titles = ['0' => '-Select-'];
                        if (!empty($teams)) {
                        	foreach($teams as $team){
                        		$team_event_id =$team->get('field_event_entity_data')->target_id;
                        		$team_event_details = node::load($team_event_id);
                                if(!empty($team_event_details) && $team_event_details != ''){ // Added condition to fix Event non-existance
                                    $team_ids[] = $team->id();
                                    $team_titles[$team->id()] = ucwords($team->title->value);
                                }
                        	}                    	
                        	$teamIds = implode(',', $team_ids);
                            $team_ids = @$teamIds;
                            $years = implode(',',$years);                    	
                        }                    
                        if (isset($team_titles['0'])) {
    						$team_titles = array('' => '-Select-') + $team_titles;
    						unset($team_titles['0']);
                        }
                        if (isset($_GET['teamid'])) {                        
                            $defaultteam[$team_id] = $team_title;                                    
                            array_push($team_titles,$defaultteam);                  
                        }
                    }                                                            
                    $recipients = $team_titles;
                    break;        
                default:
                    break;
            }           
            return $recipients;
        }

        function recipient_callback(array &$form, FormStateInterface $form_state){
        	$selected_city = $form_state->getvalue('select_city');
        	$selected_type = $form_state->getvalue('select_type');
        	$recipients = $this->getRecipients($selected_city, $selected_type);
        	$form['step_donation']['select_recipient']['field_donate_to_type']['#options'] = $recipients;
        	return $form['step_donation']['select_recipient'];
        }
        
        function recipients_callback(array &$form, FormStateInterface $form_state){
        	$selected_city = $form_state->getvalue('select_city');
        	$selected_type = $form_state->getvalue('select_type');
        	$recipients = $this->getRecipients($selected_city, $selected_type);
        	$form['step_donation']['select_recipient']['field_donate_to_type']['#options'] = $recipients;
            return $form['step_donation']['select_recipient'];
        }

        function payment_option_callback(array &$form, FormStateInterface $form_state){        	
        	$form_state->set('payment_option', 'offline');        	
        	return $form['step_payment']['payment'];
        }

        function quick_amount_callback(array &$form, FormStateInterface $form_state){
            return $form['step_donation']['quick_amount'];
        }
        
        function payment_options_callback(array &$form, FormStateInterface $form_state){        	
        	return $form['step_payment']['payment']['payment_option_container'];
        }
        function user_location_callback(array &$form, FormStateInterface $form_state){
            return $form['step_details']['personal_address']['donation_frm_location'];
        }
    }
?>
