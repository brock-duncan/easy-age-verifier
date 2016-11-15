<?php
/*
Plugin Name: Verify Age
Description: Verify that your visitors are of age.
Version:     1.30
Author:      Alex Standiford
Author URI:  http://www.alexstandiford.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once(plugin_dir_path(__FILE__).'settings.php');

class taseav{
  
  public function __construct(){
    $options = get_option('eav_options');
    $this->dob = $_COOKIE['taseavdob'];
    $this->minAge = $options['eav_minimum_age'] != '' ? $options['eav_minimum_age'] : apply_filters('eav_default_age',21);
    $this->underageMessage = $options['eav_underage_message'] != '' ? $options['eav_underage_message'] : apply_filters('eav_default_underage_message','Sorry! You must be '.$this->options['eav_minimum_age'].' To visit this website.');
    $this->formTitle = $options['eav_form_title'] != '' ? $options['eav_form_title'] : apply_filters('eav_default_form_title','Verify Your Age to Continue');
    $this->wrapperClass = $options['eav_wrapper_class'] != '' ? $options['eav_wrapper_class'] : apply_filters('eav_default_wrapper_class','taseav-age-verify');
    $this->formClass = $options['eav_form_class'] != '' ? $options['eav_form_class'] : apply_filters('eav_default_wrapper_class','taseav-verify-form');
    $this->buttonValue = $options['eav_button_value'] != '' ? $options['eav_button_value'] : apply_filters('eav_default_button_value','Submit');
    $this->overAge = $options['eav_over_age_value'] != '' ? $options['eav_over_age_value'] : apply_filters('eav_over_age_value',"I am ".$this->minAge." or older.");
    $this->underAge = $options['eav_under_age_value'] != '' ? $options['eav_under_age_value'] : apply_filters('eav_under_age_value',"I am under ".$this->minAge);
    $this->debug = $options['eav_debug'];
    $this->formType = $options['eav_form_type'] == null ? 'eav_enter_age' : $options['eav_form_type'];
    $this->beforeForm = apply_filters('eav_before_form','');
    $this->afterForm = apply_filters('eav_after_form','');
    $this->monthClass = apply_filters('eav_month_class','taseav-month');
    $this->dayClass = apply_filters('eav_day_class','taseav-day');
    $this->yearClass = apply_filters('eav_year_class','taseav-year');
    $this->minYear = apply_filters('eav_min_year','1900');
    $this->beforeYear = apply_filters('eav_before_year','');
    $this->beforeDay = apply_filters('eav_before_day','');
    $this->beforeMonth = apply_filters('eav_before_month','');
    $this->beforeButton = apply_filters('eav_before_button','');
		$this->template = apply_filters('eav_modal_template',$this->get_modal_template());
    $this->loggedIn = is_user_logged_in();
  }
	
	/**
	* The default modal template. Can be replaced with eav_modal_template filter
	* Returns HTML string
	**/
	public function get_modal_template(){
		$result = '';

		//Starts the form
		$result =  "<div id='taseav-age-verify' class='" . $this->wrapperClass . "'>";
		$result .=   $this->beforeForm;
		$result .=   "<form class='" . $this->formClass . "'>";
		$result .=   "<h2>" . $this->formTitle . "</h2>";

		//If the settings call to enter the age, do this
		if($this->formType == 'eav_enter_age'){
			$result .=     $this->beforeMonth;
			$result .=     "<div class='" . $this->monthClass . "'>";
			$result .=     "<label>Month</label>";
			$result .=     "<input name='month' type='number' min='1' max='12' required>";
			$result .=     "</div>";
			$result .=     $this->beforeDay;
			$result .=     "<div class='" . $this->dayClass . "'>";
			$result .=     "<label>Day</label>";
			$result .=     "<input name='day' type='number' min='1' max='31' required>";
			$result .=     "</div>";
			$result .=     $this->beforeYear;
			$result .=     "<div class='" . $this->yearClass . "'>";
			$result .=     "<label>Year</label>";
			$result .=     "<input name='year' type='number' min='" . $this->minYear . "' max='" . date("Y") . "' required>";
			$result .=     "</div>";
			$result .=     $this->beforeButton;
			$result .=     "<input type='submit' value='" . $this->buttonValue . "'>";
		}

		//If the settings call to simply verify the age, do this.
		if($this->formType == 'eav_confirm_age'){
			$result .=     "<input name='overAge' type='submit' value='" . $this->overAge . "'>";
			$result .=     "<input name='underAge' type='submit' value='" . $this->underAge . "'>";
		}

		//Closes out the form
		$result .=   "</form>";
		$result .=   $this->afterForm;
		$result .=  "</div>";

		return $result;
	}
  
	/**
	* Checks if the visitor is of-age.
	* Returns a boolean
	**/
  public function isOfAge(){
    if($this->age() >= $this->minAge && $this->age() != false && $this->age() != 'underAge'){
      $this->isOfAge = true;
      return true;
    }
    else{
      $this->isOfAge = false;
      return false;
    }
  }

	/**
	* Allows developers to add custom logic for the modal popup
	* Returns a boolean
	**/
  public function custom_is_true(){
		$checks = array(true);
    $checks = apply_filters('eav_custom_modal_logic', $checks);
		$result = true;
		if(is_array($checks)){
			foreach($checks as $check){
				if($check == false){
					$result = false;
					break;
				}
			}
		}
		elseif($checks == false){
				$result = false;
			}
		
    return $result;
  }

	/**
	* Calculates the age of the visitor
	* Returns a number
	**/
  public function age(){
    if(isset($this->dob)){
      if(($this->dob == 'overAge' || $this->dob == 'underAge')){
        $age = $this->dob;
      }
      else{
        //explode the date to get month, day and year
        $birthDate = explode("-", $this->dob);
        //get age from date or birthdate
        $age = (date("Ymd", date("U", mktime(0, 0, 0, $birthDate[2], $birthDate[0], $birthDate[1]))) > date("Ymd")
             ? ((date("Y") - $birthDate[0]) - 1)
             : (date("Y") - $birthDate[0]));
      }
    return $age;
    }
    else{
      return false;
    }
  }
 
	/**
	* Grabs all of the object data to pass into the Javascript
	* Returns an array
	**/
  public function get(){
    $result = array();
    foreach($this as $var => $value){
      $result = array_merge($result,[$var => $value]);
    }
    $result['isOfAge'] = $this->isOfAge();
    return $result;
  }
  
}

//Enqueues scripts and styles
function taseav_init(){
  //Calls the data to pass to the JS file
  $pass_data = new taseav();
  //Checks to see if the date of birth is above the desired age
  //Also checks to see if the user is logged in.
  if($pass_data->isOfAge() == false && !is_user_logged_in()){
    //Checks to see if there are any custom overrides to the behavior of the modal
    if($pass_data->custom_is_true()){
      //Calls jQuery beforehand as verify-age depends on it
      wp_enqueue_script('jquery');

      //Registers the Age Verification Script
      wp_register_script('verify-age.js',plugin_dir_url(__FILE__).'verify-age.js',array(),time());

      //Adds PHP Variables to the script as an object
      wp_localize_script('verify-age.js','taseavData',$pass_data->get());

      //Calls Age Verification Script
      wp_enqueue_script('verify-age.js',array(),time());

      //Age Verification Style
      wp_enqueue_style('verify-age.css',plugin_dir_url(__FILE__).'verify-age.css',array(),'1.30');
    }
  }
}
add_action('wp_enqueue_scripts','taseav_init');

?>