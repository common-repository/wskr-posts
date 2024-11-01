<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://wskr.ie/
 * @since      1.0.0
 *
 * @package    WSKR
 * @subpackage WSKR/includes
 *
 * @author     WSKR Limited <support@wskr.ie>
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WSKR
 * @subpackage WSKR/includes
 * @author     WSKR Limited <support@wskr.ie>
 */
class Wskr_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
    	//remove user role
    	remove_role( 'wskr_member' );
	}

}
