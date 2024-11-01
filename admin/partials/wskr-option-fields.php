<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wskr.ie/
 * @since      1.0.0
 *
 * @package    WSKR
 * @subpackage WSKR/admin/partials
 */
if(!function_exists('wskr_option_fields')){
   function wskr_option_fields(){
   		$args = array('wskr_business_email', 'wskr_business_id', 'wskr_token_price', 'wskr_tag', 'wskr_category', 'wskr_login_page', 'wskr_confirmation_page');
   		return $args;
   }
}
?>