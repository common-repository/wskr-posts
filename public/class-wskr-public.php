<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wskr.ie/
 * @since      1.0.0
 *
 * @package    WSKR
 * @subpackage WSKR/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WSKR
 * @subpackage WSKR/public
 * @author     WSKR Limited <support@wskr.ie>
 */
class Wskr_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        add_filter( 'redirect_canonical', [ $this, 'redirect_canonical' ] );
		add_action('template_redirect', array( $this, 'handle_template_redirect') );
		add_filter( 'login_redirect', array( $this, 'wskr_login_redirect'), 10, 3 );
		add_shortcode( 'wskr-protected-content', array( $this, 'wskr_protected_content') );
		add_filter( 'wp_page_menu_args', array( $this, 'wskr_exclude_menu_pages'), 99 );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wskr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wskr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_style( $this->plugin_name . 'snackbar', plugin_dir_url( __FILE__ ) . 'css/snackbar.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wskr-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wskr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wskr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_script( $this->plugin_name . 'js-cookie', plugin_dir_url( __FILE__ ) . 'js/js-cookie.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name . 'snackbar', plugin_dir_url( __FILE__ ) . 'js/snackbar.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wskr-public.js', array( 'jquery' ), $this->version, false );

        wp_localize_script( $this->plugin_name, 'wskrPublicAjax', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'site_url'  => get_site_url(),
            'api_base'  => esc_url_raw(rest_url()),
            'nonce'     => wp_create_nonce( 'wp_rest' )
        ));

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function add_powered_by_logo(){
		$status = 'false';
		$post_tags = !empty(get_the_terms( get_the_ID(), 'post_tag' )) ? get_the_terms( get_the_ID(), 'post_tag' ) : '';
		$post_cats = !empty(get_the_category( get_the_ID(), 'post_cat' )) ? get_the_category( get_the_ID(), 'post_cat' ) : '';
		$get_post_tags_option = !empty(get_option( 'wskr_tag' )) ? get_option( 'wskr_tag' ) : '';
		$get_post_cats_option = !empty(get_option( 'wskr_category' )) ? get_option( 'wskr_category' ) : '';
		$confirmation_page = !empty(get_option( 'wskr_confirmation_page' )) ? get_option( 'wskr_confirmation_page' ) : '';
		$login_page = !empty(get_option( 'wskr_login_page' )) ? get_option( 'wskr_login_page' ) : '';
		$data = explode(',',$get_post_tags_option);
        $dataC = explode(',',$get_post_cats_option);

   		if(!empty($post_tags)) :
   			foreach($post_tags as $post_tag_key => $post_tag_val) :
   				if(in_array($post_tag_val->slug, $data) ) :
   					$status = 'true';
				endif;
			endforeach;
   		endif;

   		if(!empty($post_cats)) :
            foreach($post_cats as $post_cat_key => $post_cat_val) :
                if(in_array($post_cat_val->slug, $data) ) :
                    $status = 'true';
                endif;
            endforeach;
        endif;
        
		$wskr_token_price = get_post_meta( get_the_ID(), 'wskr_token_price', true );
		if($confirmation_page == get_the_ID() || $login_page == get_the_ID()){
            echo '<div class="powered-by-section on-login d-none">
					<img class="powered-by-wskr-logo" src="'.plugins_url('assets/images/powered-by-wskr.png',__FILE__).'">
				  </div>';
        }

	}

	/**
	 * Hide admin bar for wskr_member role.
	 *
	 * @since    1.0.0
	 */
	public function hide_admin_bar_for_wskr_member_role(){
		$user = wp_get_current_user();
		if ( in_array( 'wskr_member', (array) $user->roles ) ) {
		    show_admin_bar(false);
		}
	}

	/**
	 * WSKR find user login or not and also find the user role wskr member
	 *
     * @since    1.0.0
	 */
	public function wskr_user_login_with_role(){

		$data = array();
		$dataC = array();
		$login_return_url = !empty(get_option( 'wskr_login_page' )) ? get_option( 'wskr_login_page' ) : '';
		$confirmation_page_url = !empty(get_option( 'wskr_confirmation_page' )) ? get_option( 'wskr_confirmation_page' ) : '';
		$get_post_meta = !empty(get_post_meta( get_the_ID(), 'wskr_token_price', true)) ? get_post_meta( get_the_ID(), 'wskr_token_price', true) : '';
		$get_post_tags = wp_get_post_tags(get_the_ID());
        $get_post_categories = wp_get_post_categories(get_the_ID()); // 1.2.0 : addition of multiple categories
		$option_tag = !empty(get_option( 'wskr_tag' )) ? get_option( 'wskr_tag' ) : '';
		$option_category = !empty(get_option( 'wskr_category' )) ? get_option( 'wskr_category' ) : ''; // 1.2.0 : addition of multiple categories
		if(!empty($get_post_tags)) :
			$explode = explode(',', $option_tag);
		    foreach($get_post_tags as $get_post_tag_key => $get_post_tag_val) :
		    	if(in_array($get_post_tag_val->name, $explode)) :
		    	   $data[] = $option_tag;
		    	endif;
		    endforeach;
		endif;
		if(!empty($get_post_categories)) :
			$explode = explode(',', $option_category);
		    foreach($get_post_categories as $get_post_cat_key => $get_post_cat_val) :
		    	if(in_array($get_post_cat_val->name, $explode)) :
		    	   $dataC[] = $option_category;
		    	endif;
		    endforeach;
		endif;

		if(!empty($data) || !empty($dataC) || ($confirmation_page_url == get_the_ID()) || ($login_return_url == get_the_ID()) )  :
			if(is_user_logged_in()){
				$user = wp_get_current_user();
				if(in_array( 'wskr_member', (array) $user->roles ) && !empty($get_post_meta)) :
				elseif(in_array( 'administrator', (array) $user->roles ) && !empty($get_post_meta)) :
				else :
					if( $login_return_url != get_the_ID() && !empty($get_post_meta) ) :
					    setcookie('last-visited-wskr-page', $u_email, time() + (30), "/");
						wp_redirect( get_permalink( $login_return_url ) );
					endif;
				endif;
			}else{
				if(!isset($_COOKIE['token']) && empty($_COOKIE['token'])) :
					if( $login_return_url != get_the_ID() ) :
						setcookie('last-visited-wskr-page', $u_email, time() + (30), "/");
						wp_redirect( get_permalink( $login_return_url ) );
					endif;
				endif;
			}
		endif;
	}

	/**
	 * WSKR login redirect
	 *
     * @since    1.0.0
	 */
	public function wskr_login_redirect( $url, $request, $user ) {
	    if ( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
	        if ( $user->has_cap( 'administrator' ) ) {
	            $url = admin_url();
	        }elseif($user->has_cap( 'wskr_member' )){
	        	$check_wskr_confirmation_page_exist = !empty(get_option( 'wskr_confirmation_page' )) ? get_option( 'wskr_confirmation_page' ) : '';
	        	$url = get_permalink($check_wskr_confirmation_page_exist);
	        }else {
	            $url = home_url();
	        }
	    }
	    return $url;
	}

	/**
	 * WSKR protected content
	 *
     * @since    1.0.0
	 */
	public function wskr_protected_content($atts, $content = null){
		return $content;
	}

	/**
	 * WSKR exclude menu pages
     *
     * @since    1.0.0
	 */
	public function wskr_exclude_menu_pages( $args ){
		$wskr_login_page = !empty(get_option( 'wskr_login_page' )) ? get_option( 'wskr_login_page' ) : '';
		$wskr_confirmation_page = !empty(get_option( 'wskr_confirmation_page' )) ? get_option( 'wskr_confirmation_page' ) : '';
		$exclude_pages = array($wskr_login_page, $wskr_confirmation_page);
		$exclude_pages_ids = '';
		foreach ( $exclude_pages as $exclude_page ) {
			if ( $exclude_pages_ids != '' ) {
				$exclude_pages_ids .= ', ';
			}
			$exclude_pages_ids .= $exclude_page;
		}
		if ( ! empty( $args['exclude'] ) ) {
			$args['exclude'] .= ',';
		} else {
			$args['exclude'] = '';
		}
		$args['exclude'] .= $exclude_pages_ids;
		return $args;
	}

	/**
     * Handle Template Redirect
     *
     * @since    1.0.0
     */
	public function handle_template_redirect()
    {
        $postId = get_the_ID();
        if (!$postId)
            return;

        if (is_front_page()) {
            return;
        }

        if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
            return;
        }

        if ( !is_front_page() && is_home() ) {
            $postId = get_option( 'page_for_posts' );
        }

        if (!$this->is_wskr_content($postId))
            return;

        $user = $this->get_signon_user();
        if ($this->is_administrator($user)) {
            return;
        }

        if ($this->is_wskr_auth_page($postId)) {
            $this->handle_auth_page_entrance();
            return;
        }

        if (!$user || !$this->is_wskr_account($user) || !$this->get_auth_token()) {
            $this->redirect_to_login($postId);
            return;
        }

        if (!$this->has_purchased($user, $postId)) {
            $this->redirect_to_confirmation($postId);
            return;
        }

        return;
    }

    /**
     * Check if active post is WSKR Login page or WSKR Confirmation page
     *
     * @since    1.0.0
     */
    private function is_wskr_auth_page($postId)
    {
        $login_page_id = get_option( 'wskr_login_page' );
        $confirmation_page_id = get_option( 'wskr_confirmation_page' );

        if ($postId == $login_page_id || $postId == $confirmation_page_id)
            return true;

        return false;
    }

    /**
     * Handle direct entrance to the WSKR Login, WSKR Confirmation pages
     *
     * @since    1.0.0
     */
    private function handle_auth_page_entrance()
    {
        $user = $this->get_signon_user();
        $redirectTo = isset($_REQUEST['redirect_to']) ? (int)$_REQUEST['redirect_to'] : null;
        if ($user && $redirectTo && $this->has_purchased($user, $redirectTo)) {
            $contentUrl = get_permalink($redirectTo);
            wp_redirect( $contentUrl );
            die();
        }

        if (!$redirectTo) {
            wp_redirect( get_home_url() );
            die();
        }

        return;
    }

    /**
     * Check if browser has cookie for WSKR User Token
     *
     * @since    1.0.0
     */
    private function get_auth_token()
    {
        if(!isset($_COOKIE['wskr_auth_token']) && empty($_COOKIE['wskr_auth_token']))
            return null;

        return $_COOKIE['wskr_auth_token'];
    }

    /**
     * Check if active post is secured by WSKR plugin
     *
     * @since    1.0.0
     */
    private function is_wskr_content($postId)
    {
        $wskr_tag = get_option( 'wskr_tag' );
        $wskr_category = get_option( 'wskr_category' );

        $wskr_tag_array = explode(',', $wskr_tag);
        $wskr_category_array = explode(',', $wskr_category);

        if ((!isset($wskr_tag) || $wskr_tag == '') && (!isset($wskr_category) || $wskr_category == ''))
            return false;

        $post_tags = wp_get_post_tags($postId);
        $post_categories = wp_get_post_categories($postId);

        if ((!isset($post_tags) || empty($post_tags)) && (!isset($post_categories) || empty($post_categories)))
            return false;

        $is_secured = false;
        foreach ($post_tags as $key => $value) {
            if (in_array($value->name, $wskr_tag_array)) // allow for multiple WSKR protected tags
                $is_secured = true;
        }
        foreach ($post_categories as $c) {
            $cat = get_category( $c );
            if (in_array($cat->name, $wskr_category_array)) // addition of WSKR protected categories
                $is_secured = true;
        }

        return $is_secured;
    }

    /**
     * Get current user
     *
     * @since    1.0.0
     */
    private function get_signon_user()
    {
        $user = wp_get_current_user();
        if (!$user)
            return null;

        return $user;
    }

    /**
     * Check if the user is an admin account or not
     *
     * @since    1.0.0
     */
    private function is_administrator($user)
    {
        if ($user->has_cap( 'administrator' ) || in_array('administrator', $user->roles))
            return true;

        return false;
    }

    /**
     * Check if the user is signed up on WSKR or not
     *
     * @since    1.0.0
     */
    private function is_wskr_account($user)
    {
        if ($user->has_cap( 'wskr_member' ) || in_array('wskr_member', $user->roles))
            return true;

        return false;
    }

    /**
     * Check if the user has handled the payment for the secured post
     *
     * @since    1.0.0
     */
    private function has_purchased($user, $postId)
    {
        $userId = $user->get_ID() ? $user->get_ID() : $user->data->ID;
        $postIds = get_user_meta($userId, 'wskr_purchased_posts', true);
        $postIds = json_decode($postIds, true);

        if (!isset($postIds) || !$postIds)
            $postIds = array();

        if(in_array($postId, $postIds, true))
            return true;

        return false;
    }

    /**
     * Redirect user to the WSKR Login page
     *
     * @since    1.0.0
     */
    private function redirect_to_login($original_id)
    {
        $login_page_id = get_option( 'wskr_login_page' );
        if (!isset($login_page_id) || empty($login_page_id)) {
            wp_redirect( get_home_url() );
            die();
        }

        $login_page = get_page($login_page_id);
        if ($login_page->post_status == 'publish') {
            wp_redirect( get_permalink($login_page_id) . '?redirect_to=' . $original_id );
            die();
        }

        wp_redirect( get_home_url() );
        die();
    }

    /**
     * Redirect user to the WSKR Confirmation page
     *
     * @since    1.0.0
     */
    private function redirect_to_confirmation($original_id)
    {
        $confirmation_page_id = get_option( 'wskr_confirmation_page' );
        if (!isset($confirmation_page_id) || empty($confirmation_page_id)) {
            wp_redirect( get_home_url() );
            die();
        }

        $confirmation_page = get_page($confirmation_page_id);
        if ($confirmation_page->post_status == 'publish') {
            wp_redirect( get_permalink($confirmation_page) . '?redirect_to=' . $original_id );
            die();
        }

        wp_redirect( get_home_url() );
        die();
    }

    /**
     * Stop trailing slashes on sitemap.xml URLs.
     *
     * @param string $redirect The redirect URL currently determined.
     * @return bool|string $redirect
     *
     * @since    1.0.0
     */
    public function redirect_canonical( $redirect ) {

        global $wp;
        $current_url = home_url( add_query_arg( array(), $wp->request ) );

        if (strpos($current_url, 'wskr-login')) {
            return false;
        }

        return $redirect;
    }
}
