<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wskr.ie/
 * @since             1.0.0
 * @package           WSKR
 *
 * @wordpress-plugin
 * Plugin Name:       WSKR Posts
 * Plugin URI:        https://wskr.ie/wskr-posts/
 * Description:       WSKR Posts. Bring the power of WSKR to WordPress. Turn your posts into premium content and generate pay-per-article revenue.
 * Version:           1.2.3
 * Author:            WSKR Limited
 * Author URI:        https://wskr.ie/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wskr
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WSKR_VERSION', '1.2.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wskr-activator.php
 */
function activate_wskr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wskr-activator.php';
	Wskr_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wskr-deactivator.php
 */
function deactivate_wskr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wskr-deactivator.php';
	Wskr_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wskr' );
register_deactivation_hook( __FILE__, 'deactivate_wskr' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wskr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wskr() {

	$plugin = new WSKR();
	$plugin->run();

}
run_wskr();

/**
* WSKR Authorise Business Account Ajax.
*
* @since    1.0.0
*/
add_action( 'wp_ajax_wskr_authorise_business_account', 'wskr_authorise_business_account' );
function wskr_authorise_business_account(){
	wskr_save_settings();

	$form_data = array();
	parse_str($_POST['formdata'], $form_data);
	$form_data = wp_unslash($form_data);

	$b_email = trim($form_data['wskr_business_email']);
	$b_password = trim($form_data['wskr_business_password']); 

	/* API URL */
	$url = "https://api.wskr.ie/AuthoriseAccount";

    $args = array(
        'body' => array(
            'email' => $b_email,
            'password' => $b_password
        )
    );

    $responseArr = wp_remote_post( $url , $args );
    $body = $responseArr['body'];
    $response = $responseArr['response'];

    $body = json_decode($body, True);
    $response = json_decode($response, True);

    if ($body['status'] === 'success' && $body['data']) {
        echo json_encode(array('status' => 1,'data'=>$body['data']));
    } else {
        echo json_encode(array('status' => 0,'data'=>$response));
    }

    wp_die();
}
/**
* WSKR Save Setting by ajax
*/
add_action( 'wp_ajax_wskr_save_settings_ajax', 'wskr_save_settings_ajax' );
function wskr_save_settings_ajax(){
    wskr_save_settings();
    echo json_encode(array('status' => 1,'data'=> ''));
    wp_die();
}
function wskr_save_settings() {	
    $form_data = array();	
    parse_str($_POST['formdata'], $form_data);	
    $form_data = wp_unslash($form_data);	
    $wskr_business_email = trim($form_data['wskr_business_email']);	
    $wskr_business_id = trim($form_data['wskr_business_id']);	
    $wskr_token_price = trim($form_data['wskr_token_price']);	
    $wskr_tag = trim($form_data['wskr_tag']);	
    $wskr_category = trim($form_data['wskr_category']);	
    update_option('wskr_business_email', $wskr_business_email);	
    update_option('wskr_business_id', $wskr_business_id);	
    update_option('wskr_token_price', $wskr_token_price);	
    update_option('wskr_tag', $wskr_tag );
    update_option('wskr_category', $wskr_category );
}
/**
* WSKR template header
*/
function get_wskr_header($name){
	if( is_page_template( 'templates/wskr-template.php' ) ) :
    	require_once plugin_dir_path(__FILE__) . "templates/header-wskr.php"; 		
	endif;
}
add_action( 'get_header', 'get_wskr_header' );   		
/**
* WSKR template footer
*/
function get_wskr_footer($name){
	if( is_page_template( 'templates/wskr-template.php' ) ) :
    	$name = require_once plugin_dir_path(__FILE__) . "templates/footer-wskr.php"; 		
	endif;
}
add_action( 'get_footer', 'get_wskr_footer' );
/**
* WSKR get domain
*/
function wskr_get_domain($url='', $api_url=''){
 	$get_domain = $get_url = '';
 	if(empty($url) && !empty($api_url)){
 		$get_url = home_url();
	    $parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
 		$get_domain = $api_url.$parse_url['host'];
 	}elseif(!empty($url) && !empty($api_url)){
 		$get_url = $url;
	    $parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
	    $get_domain = $api_url.$parse_url['host'];
 	}else{
	    if(!empty($url)){
	    	$get_url = $url;
	    	$parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
	    	if(!empty($parse_url) && $parse_url['host'] == 'localhost'){
	    		$get_domain = str_replace('http://localhost/', '', $get_url).'.com';
	    	}else{
	            $get_domain = $parse_url['host'];
	    	}
	    }else{
	    	$get_url = home_url();
	    	$parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
	    	if(!empty($parse_url) && $parse_url['host'] == 'localhost'){
	    		$get_domain = str_replace('http://localhost/', '', $get_url).'.com';
	    	}else{
	            $get_domain = $parse_url['host'];
	    	}
	   }
   }
   return $get_domain;
}
/**
* WSKR signon 
*/
function wskr_signon($username = '', $password = '', $remember = true){
	$creds = array(
			 'user_login'    => $username.'@'.wskr_get_domain(),
			 'user_password' => $password,
			 'remember'      => $remember
			 );
	$user = wp_signon( $creds, false );
	if ( !is_wp_error( $user ) ) {
		 echo json_encode(array('status' => 1, 'user_id' => $user_id, 'message' => 'You are successfully logged in.'));
	}else{
		$message = $user->get_error_message();
		echo json_encode(array('status' => 0, 'user_id' => $user_id, 'message' => $message)); 
	}
}