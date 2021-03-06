<?php
/*
Plugin Name: Dynamic Phone Numbers
Plugin URI: https://github.com/nlk-plugins/dynamic-phone
Description: Display dynamic phone numbers based on URL query strings, form POST fields, or cookie data.
Version: 1.1
Author: Ninthlink, Inc.
Author URI: http://www.ninthlink.com
License: GPL2
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Oops';
	exit;
}

define('DYNAMIC_PHONE_VERS', '1.1');
define('DYNAMIC_PHONE_PLUGIN_URL', plugin_dir_url( __FILE__ ));

// Set plugin option defaults on first load
function dynamic_phone_init_options() {

	$default_options = array(
			'shortcode'	=> array(
					'allow_in_widget' => NULL
				),
			'triggers'	=> array(
					'query_string_name'	=> '',
					'form_field_name'	=> '',
					'cookie_name'		=> 'dynaphone',
					'cookie_lifetime'	=> 7,
				),
			'numbers'	=> array(
					'default_phone'		=> '',
					'query_string_value'	=> array(),
					'form_field_value'	=> array(),
					'cookie_value'	=> array(),
					'dynamic_phone_number'	=> array(),
				),
		);

	if ( ! get_option( 'dynamic_phone' ) )
		add_option( 'dynamic_phone', $default_options );

}
add_action( 'admin_init', 'dynamic_phone_init_options' );

// load page structures
if ( is_admin() ) {
	require_once dirname( __FILE__ ) . '/admin-display.php';
}

// Get the set options for the following functions
$dynamic_phone = get_option('dynamic_phone');

//----------------------------------------------------
//
//	Shortcodes
//

if ( $dynamic_phone['shortcode']['allow_in_widget'] && $dynamic_phone['shortcode']['allow_in_widget'] == 'yes'  && !has_filter('widget_text', 'do_shortcode') ) {
	add_filter('widget_text', 'do_shortcode');
}

// [phone_num number="8885551212" link=true format="standard"]
function dynamic_phone_num_shortcode( $atts ) {

	global $dynamic_phone;

	extract( shortcode_atts( array(
		'number'	=> false,
    'defaultfield' => false,
		'link'		=> true,
		'format'	=> 'standard',
	), $atts ) );
  if ( $link == 'false' ) $link = false;
  $defaultnumber = false;
  if ( $defaultfield !== false ) {
    if ( function_exists('get_field') ) {
      $defaultnumber = get_field( $defaultfield );
    }
  }
  
  if ( $number === false ) {
    $number = get_dynamic_phone_number( $defaultnumber );
  }
  
	$clean_number = format_phone_us( $number, 'numeric' );
	$formatted_number = format_phone_us( $number, $format );

	$num = $formatted_number;

	if ( $link != false ) {
		$num = '<a href="tel:'.$clean_number.'">'. $num .'</a>';
	} else {
	$num = '<span class="nolink">'. $num .'</span>';
}
	
	return $num;

}
add_shortcode( 'phone_num', 'dynamic_phone_num_shortcode' );


//----------------------------------------------------
//
//	Helper Functions
//

// set cookie for dynamic number control
function dynamic_phone_set_cookie( $val ) {
	global $dynamic_phone;
	
	$cookiename = ''. $dynamic_phone['triggers']['cookie_name'];
	
	$dur = 60 * 60 * 24 * 100 * $dynamic_phone['triggers']['cookie_lifetime'];
	//$path = get_bloginfo('url');
	
	//wp_die( 'setcookie( '. $name .' , '. $num .' , '. $d .' , '. COOKIEPATH.' , '. COOKIE_DOMAIN.' , false );' );
	
	setcookie( $cookiename, $val, time() + $dur, COOKIEPATH, COOKIE_DOMAIN, false );
	//wp_die( 'cookie set : <pre>'. print_r($_COOKIE[ $name ], true ) .'</pre>' );
	
	return true;
}

// check if cookie should get set, before headers are sent
function dynamic_phone_check_set() {
	global $dynamic_phone;
	$a = false;
	$k = false;
	$v = false;
	if ( $dynamic_phone['triggers']['query_string_name'] != '' ) {
		if ( isset( $_GET[ $dynamic_phone['triggers']['query_string_name'] ] ) ) {
			// if GET, set dynamic value as get value
			$v = $_GET[ $dynamic_phone['triggers']['query_string_name'] ];
			// dynamic phone numbers array for GET
			$a = $dynamic_phone['numbers']['query_string_value'];
		}
	} else if ( $dynamic_phone['triggers']['form_field_name'] != '' ) {
		if ( isset ( $_POST[ $dynamic_phone['triggers']['form_field_name'] ] ) ) {
			// if POST, set value as post value
			$v =  $_POST[ $dynamic_phone['triggers']['form_field_name'] ];
			// dynamic phone numbers array for POST
			$a = $dynamic_phone['numbers']['form_field_value'];
		}
	}

	if ( is_array( $a ) ) {
		// check if value in phone array, and return associated key, or false
		$k = array_search( $v, $a );
	}

	if ( ( $k !== false ) && ( $k !== '' ) ) {
		// if array key value is set
		$num = $dynamic_phone['numbers']['dynamic_phone_number'][ $k ];
		dynamic_phone_set_cookie( $num );
	}
}
add_action( 'init', 'dynamic_phone_check_set' );

// get dynamic number from GET, POST, or COOKIE
function get_dynamic_phone_number( $defaultnumber ) {

	global $dynamic_phone;
	// set default phone number
	if ( $defaultnumber !== false ) {
		// allow override?
		$num = $defaultnumber;
	} else {
		$num = $dynamic_phone['numbers']['default_phone'];
	}
	
	// first check if GET or POST are set, and use those
	$k = false;
	$a = false;
	if ( $_GET[ $dynamic_phone['triggers']['query_string_name'] ] ) {
		$v = $_GET[ $dynamic_phone['triggers']['query_string_name'] ]; // if GET, set dynamic value as get value
		$a = $dynamic_phone['numbers']['query_string_value']; // dynamic phone numbers array
	} else if ( $_POST[ $dynamic_phone['triggers']['form_field_name'] ] ) {
		$v =  $_POST[ $dynamic_phone['triggers']['form_field_name'] ]; // if POST, set value as post value
		$a = $dynamic_phone['numbers']['form_field_value']; // dynamic phone numbers array
	}

	if ( is_array( $a ) ) {
		$k = array_search( $v, $a ); // check if value in phone array, and return associated key, or false
	}

	if ( ( $k !== false ) && ( $k !== '' ) ) { // if array key value is set
		$num = $dynamic_phone['numbers']['dynamic_phone_number'][$k]; // if key exists, return associated dynamic phone number, otherwise use default number
		//dynamic_phone_set_cookie( $k );
	}
	
	// $k = false means not fixed by GET / POST so check Cookie then..
	if ( $k === false ) {
		if ( !empty( $_COOKIE[ $dynamic_phone['triggers']['cookie_name'] ] ) ) {
			// if cookie exists, set dynamic value as cookie value
			$num = $_COOKIE[ $dynamic_phone['triggers']['cookie_name'] ];
		}
	}
	return $num;
}

/*
add_action( 'wp_head', 'my_getcookie' );
function my_getcookie() {
	global $dynamic_phone;
	
	$name = ''. $dynamic_phone['triggers']['cookie_name'];
   $alert = isset( $_COOKIE[ $name ] ) ? $_COOKIE[ $name ] : '"'. $name .'" cookie not set';
   echo "<script type='text/javascript'>alert('$alert')</script>";
}
*/

// returns US formatted phone number
if ( ! function_exists('format_phone_us') ) {
	function format_phone_us( $phone = '', $format='standard', $convert = true, $trim = true ) {
		if ( empty( $phone ) ) {
			return false;
		}
		// Strip out non alphanumeric
		$phone = preg_replace( "/[^0-9A-Za-z]/", "", $phone );
		// Keep original phone in case of problems later on but without special characters
		$originalPhone = $phone;
		// If we have a number longer than 11 digits cut the string down to only 11
		// This is also only ran if we want to limit only to 11 characters
		if ( $trim == true && strlen( $phone ) > 11 ) {
			$phone = substr( $phone, 0, 11 );
		}
		// letters to their number equivalent
		if ( $convert == true && !is_numeric( $phone ) ) {
			$replace = array(
				'2'=>array('a','b','c'),
				'3'=>array('d','e','f'),
				'4'=>array('g','h','i'),
				'5'=>array('j','k','l'),
				'6'=>array('m','n','o'),
				'7'=>array('p','q','r','s'),
				'8'=>array('t','u','v'),
				'9'=>array('w','x','y','z'),
				);
			foreach ( $replace as $digit => $letters ) {
				$phone = str_ireplace( $letters, $digit, $phone );
			}
		}
		$a = $b = $c = $d = null;
		switch ( $format ) {
			case 'decimal':
			case 'period':
				$a = '';
				$b = '.';
				$c = '.';
				$d = '.';
				break;
			case 'hypen':
			case 'dash':
				$a = '';
				$b = '-';
				$c = '-';
				$d = '-';
				break;
			case 'space':
				$a = '';
				$b = ' ';
				$c = ' ';
				$d = ' ';
				break;
			case 'numeric':
				$a = '';
				$b = '';
				$c = '';
				$d = '';
				break;
			default:
			case 'standard':
				$a = '(';
				$b = ') ';
				$c = '-';
				$d = '(';
				break;
		}
		$length = strlen( $phone );
		// Perform phone number formatting here
		switch ( $length ) {
			case 7:
				// Format: xxx-xxxx / xxx.xxxx / xxx-xxxx / xxx xxxx
				return preg_replace( "/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1$c$2", $phone );
			case 10:
				// Format: (xxx) xxx-xxxx / xxx.xxx.xxxx / xxx-xxx-xxxx / xxx xxx xxxx
				return preg_replace( "/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$a$1$b$2$c$3", $phone );
			case 11:
				// Format: x(xxx) xxx-xxxx / x.xxx.xxx.xxxx / x-xxx-xxx-xxxx / x xxx xxx xxxx
				return preg_replace( "/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1$d$2$b$3$c$4", $phone );
			default:
				// Return original phone if not 7, 10 or 11 digits long
				return $originalPhone;
		}
	}
}
