<?php

/**
 * Fired during plugin activation
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
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WSKR
 * @subpackage WSKR/includes
 * @author     WSKR Limited <support@wskr.ie>
 */
class Wskr_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		/**
		 * Add custom role.
		 *
		 * @since    1.0.0
		 */
		
		 
        add_role( 'wskr_member', 'WSKR Member', get_role( 'subscriber' )->capabilities );
        update_option( 'custom_wskr_member_role_version', 1 );
	 
	    /**
		 * Create WSKR Login Page.
		 *
		 * @since    1.0.0
		 */
	    $check_page_exist = get_page_by_title('WSKR Login', 'OBJECT', 'page');
        // Check if the page already exists
		if(empty($check_page_exist)) {
		    $page_id = wp_insert_post(
		        array(
                    'comment_status' => 'close',
                    'ping_status'    => 'close',
                    'post_author'    => 1,
                    'post_title'     => ucwords('WSKR Login'),
                    'post_name'      => sanitize_title('WSKR Login'),
                    'post_status'    => 'publish',
                    'post_content'   => '[wskr_login_page]',
                    'post_type'      => 'page',
                    'post_parent'    => '',
                    'page_template'  => 'empty.php'
		        )
		    );
			$tags = array('WSKR Protected'); 
			wp_set_post_tags( $page_id, $tags);
			update_option( 'wskr_login_page', $page_id );
			update_post_meta( $page_id, '_wp_page_template', 'templates/empty.php' );
		}

		/**
		 * Create WSKR Confirmation Page.
		 *
		 * @since    1.0.0
		 */
	    $check_wskr_confirmation_page_exist = get_page_by_title('WSKR Confirmation', 'OBJECT', 'page');
        // Check if the page already exists
		if(empty($check_wskr_confirmation_page_exist)) {
		    $wskr_confirmation_page_id = wp_insert_post(
                array(
                    'comment_status' => 'close',
                    'ping_status'    => 'close',
                    'post_author'    => 1,
                    'post_title'     => ucwords('WSKR Confirmation'),
                    'post_name'      => sanitize_title('WSKR Confirmation'),
                    'post_status'    => 'publish',
                    'post_content'   => '[wskr_confirmation_page]',
                    'post_type'      => 'page',
                    'post_parent'    => '',
                    'page_template'  => 'empty.php'
                )
            );
		    $tags = array('WSKR Protected'); 
			wp_set_post_tags( $wskr_confirmation_page_id, $tags);
			update_option( 'wskr_confirmation_page', $wskr_confirmation_page_id );
			update_post_meta( $wskr_confirmation_page_id, '_wp_page_template', 'templates/empty.php' );
		}
		/**
		* Term created for tag taxonomy.
		*/
		$parent_term = term_exists( 'post_tag', 'page');
		$parent_term_id = $parent_term['term_id'];
		wp_insert_term(
		    'WSKR Protected',   
		    'post_tag',
		    array(
		        'description' => 'Default tag for WSKR protected content.',
		        'slug'        => 'wskr-protected',
		        'parent'      => $parent_term_id,
		    )
		);

		/** 
		 * Create Category
		 * v1.2.1
		 */
		$term = term_exists( 'WSKR Protected', 'category' );
		if ( $term == null ) {
			wp_insert_category(
				array(
					'cat_name' => 'WSKR Protected', 
					'category_description' => 'Default category for WSKR protected content.', 
					'category_nicename' => 'wskr-protected', 
					'category_parent' => ''
				)
			);
		}

	}

}
