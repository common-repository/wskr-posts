<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wskr.ie/
 * @since      1.0.0
 *
 * @package    WSKR
 * @subpackage WSKR/admin
 *
 * @author     WSKR Limited <support@wskr.ie>
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WSKR
 * @subpackage WSKR/admin
 * @author     WSKR Limited <support@wskr.ie>
 */
class Wskr_Admin {

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
     * The array of templates that this plugin tracks.
     * @var string
     */
    protected $templates;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public function __construct( $plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->templates = array();

        // Add template actions
		add_action( 'admin_init', array( $this,'wskr_include_files' ) );
		add_action( 'admin_init', array( $this,'wskr_settings_register' ) );
		add_action( 'init', array( $this,'wskr_register_taxonomy' ) );
        add_action( 'add_meta_boxes', array( $this, 'wskr_add_token_price_meta_field' ) );
   		add_action( 'save_post', array( $this, 'wskr_token_price_save' ) );

   		// Add custom template
        add_filter( 'theme_page_templates', array( $this, 'add_new_template' ) );
        add_filter(	'wp_insert_post_data', array( $this, 'register_project_templates' ) );
        add_filter( 'template_include', array( $this, 'view_project_template') );

        // Add shortcodes for Login page and Confirmation page
        add_shortcode('wskr_login_page', array( $this, 'wskr_login_page'));
   		add_shortcode('wskr_confirmation_page', array( $this, 'wskr_confirmation_page') );

        $this->templates = array(
            'templates/empty.php' => esc_html__('WSKR Template', 'wskr')
        );
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wskr-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wskr-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script($this->plugin_name, 'wskrSettingAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'site_url' => get_site_url() ) );

	}
	/**
	 * Inculdes files in plugin .
	 *
	 * @since    1.0.0
	 */
	public function wskr_include_files() {

		/**
		 * Inculde admin section files
		 */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wskr-option-fields.php';
	}

	public function wskr_admin_menu(){
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page( 'WSKR', 'WSKR', 'manage_options', 'wskr', array( $this, 'wskr_admin_setting' ), plugin_dir_url( __FILE__ ) . 'assets/icon/wskr-icon.png',66);
	}

	public function wskr_admin_setting(){
		/**
		 * The file contain plugin setting html form.
		 * 
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wskr-plugin-setting.php';
		
	}
	/**
	 * Register wskr setting options.
	 *
	 * @since    1.0.0
	 */
	public function wskr_settings_register() {	
		/**
		* Get wskr option fields 
		*/
		$args = '';
		if(function_exists('wskr_option_fields')){
        	$args = wskr_option_fields();
		}
		if(!empty($args)) :
		    foreach($args as $key => $val) :
		    	switch($val){
		    		case 'wskr_login_page':
		    		case 'wskr_confirmation_page':
		    			register_setting( 'wskr-page-settings', $val );
		    		break;
		    		default:
		    		 	register_setting( 'wskr-settings', $val );
		    		break;
		    	}
		    endforeach;
		endif;
	}
	/**
	 * Hide admin bar for wskr users.
	 *
	 * @since    1.0.0
	 */
	public function hide_admin_bar_for_wskr_member_role_backend() {
      	$user = wp_get_current_user();
		if ( in_array( 'wskr_member', (array) $user->roles ) ) {
		   echo '<style>#wpadminbar { padding-top: 0px !important;display:none !important; } html.wp-toolbar{ padding-top: 0px !important;}</style>';
		}
    }
    /**
	* Tags enabled for page.
	*/
	public function wskr_register_taxonomy(){
		register_taxonomy_for_object_type( 'post_tag', 'page' );
	}
	/**
	* Add WSKR meta field for post and page.
	*/
	public function wskr_add_token_price_meta_field() {
        add_meta_box( 'wskr-token-price', 'WSKR', array( $this, 'wskr_token_price'), array('post', 'page'), 'side', 'high' );
	}
	/**
	* WSKR field show
	*/
	public function wskr_token_price($post){
		$token_price = '';
		$wskr_get_token_price = !empty( get_post_meta( $post->ID, 'wskr_token_price', true) ) ? get_post_meta( $post->ID, 'wskr_token_price', true) : '';
		$wskr_token_price = !empty(get_option( 'wskr_token_price' )) ? get_option( 'wskr_token_price' ) : '';
		if(!empty($wskr_get_token_price)){
			$token_price = $wskr_get_token_price;
		}else{
			$token_price = $wskr_token_price;
		}
		// We'll use this nonce field later on when saving.
    	wp_nonce_field( 'wskr_token_price_nonce', 'meta_box_nonce' );
		echo '<label for="wskr_token_price">' . esc_html('Token Price', 'wskr') . '</label>
   			  <input type="number" min="1" name="wskr_token_price" id="wskr_token_price" value="' . $token_price . '"/>';
	}
	/**
	* WSKR field data save
	*/
	public function wskr_token_price_save_3($post_id){
		// Bail if we're doing an auto save
    	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
     
    	// if our nonce isn't there, or we can't verify it, bail
    	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'wskr_token_price_nonce' ) ) return;

    	if( isset( $_POST['wskr_token_price'] ) ) :
            $wskr_token_price = zeroise($_POST['wskr_token_price'], 4);
        	update_post_meta( $post_id, 'wskr_token_price', $wskr_token_price );
        else: $get_setting_price = !empty(get_option( 'wskr_token_price' )) ? get_option( 'wskr_token_price' ) : 0;
        	update_post_meta( $post_id, 'wskr_token_price', $get_setting_price );
    	endif;
	}
	/**
	* WSKR update meta filed
	*/
	public function wskr_token_price_save($post_id){
		$status = 'false';
		$price = '';
		$post_tags = !empty(get_the_terms( $post_id, 'post_tag' )) ? get_the_terms( $post_id, 'post_tag' ) : '';
		$post_cats = !empty(get_the_category( $post_id, 'category' )) ? get_the_category( $post_id, 'category' ) : '';
		$get_post_tags_option = !empty(get_option( 'wskr_tag' )) ? get_option( 'wskr_tag' ) : '';
		$get_post_cats_option = !empty(get_option( 'wskr_category' )) ? get_option( 'wskr_category' ) : '';
		$get_setting_price = !empty(get_option( 'wskr_token_price' )) ? get_option( 'wskr_token_price' ) : 0;
		$get_post_price = !empty(get_post_meta( $post_id, 'wskr_token_price', true )) ? get_post_meta( $post_id, 'wskr_token_price', true ) : 0;
		$data = explode(',',$get_post_tags_option);
		$dataC = explode(',',$get_post_cats_option);
		if(!empty($get_post_price)){
			if(isset($_POST['wskr_token_price'])){
				$price =  zeroise($_POST['wskr_token_price'], 4);
			}else{
				$price =  $get_setting_price;
			}
			
		}else{
			$price = $get_setting_price;
		}
   		if(!empty($post_tags)) :
   			foreach($post_tags as $post_tag_key => $post_tag_val) :
   				if(in_array($post_tag_val->slug, $data) ) :
   					$status = 'true';
				endif;
			endforeach;
   		endif;
		   
		// 1.2.0 : 1.2.0 : addition of multiple categories
   		if(!empty($post_cats)) :
   			foreach($post_cats as $post_cat_key => $post_cat_val) :
   				if(in_array($post_cat_val->slug, $dataC) ) :
   					$status = 'true';
				endif;
			endforeach;
   		endif;
   		
        if(!empty($price)){
 			update_post_meta( $post_id, 'wskr_token_price', $price );
        }else{
        	update_post_meta( $post_id, 'wskr_token_price', 0 );
        }
	}

    /**
     * Add WSKR template to the page dropdown (v4.7+)
     *
     */
    public function add_new_template( $posts_templates ) {

        $posts_templates = array_merge( $posts_templates, $this->templates );
        return $posts_templates;
    }

    /**
     * Add WSKR template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache.
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array.
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page.
     */
    public function view_project_template( $template ) {

        // Get global post
        global $post;

        // Return template if post is empty
        if ( ! $post ) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {
            return $template;
        }

        $file = plugin_dir_path( __FILE__ ). get_post_meta( $post->ID, '_wp_page_template', true );

        // Just to be safe, we check if the file exist first
        if ( file_exists( $file ) ) {
            return $file;
        } else {
            echo $file;
        }

        // Return template
        return $template;

    }

	/**
	* WSKR template
	*/
	public function wskr_template($template) {
    	$post = get_post();
    	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
    	if( $page_template == 'templates/wskr-template.php' ){
        	return plugin_dir_path(__FILE__) . "templates/wskr-template.php";
    	}   
    	return $template;
	}

	/**
	* WSKR page template
    */
    public function wskr_admin_page_templates($templates) {
    	$templates['templates/empty.php'] = esc_html__('WSKR Template', 'wskr');
    	return $templates;
	}

    /**
     * WSKR Login Page Template
     *
     * @return string
     */
    public function wskr_login_page() {
        $redirect_to = isset($_REQUEST['redirect_to']) ? sanitize_text_field($_REQUEST['redirect_to']) : null;
        if (!isset($redirect_to) || empty($redirect_to)) { ?>
            <script>
              window.location = `<?php echo get_home_url(); ?>`;
            </script>
        <?php }

		$login_link = $this->wskr_login_page_link($redirect_to); // v1.2.3
        $confirm_link = $this->wskr_confirmation_page_link($redirect_to);
        $login_form =  '<div class="width-100 wall WSKRLogInForm">
                        <div class="WSKRLogInHere">
                            <div class="wskr_logi_header">
                                <img class="powered-by-wskr-logo on-login" src="'.plugins_url('assets/images/powered-by-wskr.png',__FILE__).'">
                            </div>
                            <div class="wskr-top-message text-center">
                                <p class="m-0">' . esc_html__('You need a WSKR account to access this content.', 'wskr').'</p><p class="mt-0">'.esc_html__('Please enter your WSKR account details to continue.', 'wskr').'</p>
                            </div>
                            <div class="wskr_login_form_sec wall">
                                <form id="WSKRLoginForm" method="post" class=" wskr_login_form_wapper" data-redirect-to=" ' . $confirm_link . ' ">
                                    <div class="form-group form-group-email">
                                        <input type="email" id="FormFieldEmail" class="wskr_form_field" name="formFieldEmail" placeholder="' . esc_attr__('Email address', 'wskr') . '">
                                        <p class="error-msg"></p>
                                    </div>
                                    <div class="form-group form-group-password">
                                        <input type="password" id="FormFieldPassword" class="wskr_form_field psw" name="formFieldPassword" placeholder="' . esc_attr__('Password', 'wskr') . '">
                                        <p class="error-msg"></p>
                                    </div>
                                    <div class="wskr-remember-wrapper">
                                        <div class="wall WSKRCHeckBxHere">
                                            <div class="input-group">
                                                <input type="checkbox" class="form-field-remember-me" id="RemeberMe" name="Remember Me">
                                                <label for="RemeberMe" class="remember-me">' . esc_html('Remember me', 'wskr') . '</label>
                                            </div>                                            
                                            <button type="button" id="Continue" class="btn-continue float-right">' . esc_html('Continue', 'wskr') . '</button>
                                        </div>
                                    </div>';

        if(!empty($confirm_link)) :
            $login_form .= '<div class="wskr_button wall text-center" id="wskr_button">
                                        <a id="Register" class="wskr_link_button" data-url="https://my.wskr.ie/identity/account/register?returnurl=' . $login_link . '">' . esc_html('Register', 'wskr') . '</a>
                                        <a id="ForgotPassword" class="wskr_link_button" data-url="https://my.wskr.ie/Identity/Account/ForgotPassword?returnurl=' . $login_link . '")">' . esc_html('Forgot Password', 'wskr') . '</a>
                                </div>';
        endif;
        $login_form .=          '</form>
                            </div>
								<div class="wskr_error_message"></div>
							</div>
						</div>';
        return $login_form;
    }

	/**
	* WSKR Confirmation Page Template
	*/
	public function wskr_confirmation_page() {
        $redirect_to = isset($_REQUEST['redirect_to']) ? sanitize_text_field($_REQUEST['redirect_to']) : null;
        if (!isset($redirect_to) || empty($redirect_to) || (get_post_status($redirect_to) != 'publish' )) { ?>
            <script>
              window.location = `<?php echo get_home_url(); ?>`;
            </script>
        <?php }

        $contentUrl = get_permalink($redirect_to);
        $returnUrl = get_permalink(get_option( 'wskr_confirmation_page' )) . '?redirect_to=' . $redirect_to;
        $tokenValue = (int) get_post_meta( $redirect_to, 'wskr_token_price', true);
        if (!isset($tokenValue) || empty($tokenValue)) {
            $tokenValue = (int) get_option( 'wskr_token_price' );
        }

 	    $output = '<form id="WSKRConfirmationForm" class="wskr_confirmation_page_form" 
                            data-redirect-to="' . $redirect_to . '" 
                            data-content-url="' . $contentUrl . '"
                            data-return-url="' . $returnUrl . '"
                            data-token-value="' . $tokenValue . '">
 	    				<div class="wskr_confirmation_main_page">
 	    					<div class="wskr-confirmation-page" id="wskr-confirmation-page">
 	    						<div class="wskr-confirmation-page_logo">
									<img class="powered-by-wskr-logo on-login" src="'.plugins_url('assets/images/powered-by-wskr.png',__FILE__).'">
								</div>';
 	                			if ($tokenValue == 1) {
 	                				$output .= '<p class="m-0">' . esc_html__('This content costs 1 WSKR Token to access.', 'wskr') . '</p><p class="m-0">' . esc_html__('Please confirm that you wish to continue.', 'wskr') . '</p>';
 	                			} else if ( $tokenValue > 1 ) {
 	                				$output .= '<p class="m-0">' . esc_html__('This content costs ', 'wskr') . esc_html( $tokenValue ) . esc_html__(' WSKR Tokens to access.', 'wskr') . '</p><p class="m-0">' . esc_html__('Please confirm that you wish to continue.', 'wskr'). '</p>';
 	                			}
 					$output .= '<div class="wskr_conformation_btn"> 
 									<a href="' . site_url() . '" class="wskr-cancel">' . esc_html__('Cancel', 'wskr') . '</a>
 									<a id="Continue" class="btn-continue wskr-continue">' . esc_html__('Continue', 'wskr') . '</a>
 								</div>
 	    					</div>
 	  	  				</div>
 	    			</form>';
        return $output;
	}

	private function wskr_login_page_link($original_id) { // v1.2.3
        $confirmation_page_id = get_option( 'wskr_login_page' );
        if (!isset($confirmation_page_id) || empty($confirmation_page_id)) {
            return get_home_url();
        }

        $confirmation_page = get_page($confirmation_page_id);
        if ($confirmation_page->post_status == 'publish') {
            return get_permalink($confirmation_page) . '?redirect_to=' . $original_id;
        }

        return get_home_url();
    }

    private function wskr_confirmation_page_link($original_id) {
        $confirmation_page_id = get_option( 'wskr_confirmation_page' );
        if (!isset($confirmation_page_id) || empty($confirmation_page_id)) {
            return get_home_url();
        }

        $confirmation_page = get_page($confirmation_page_id);
        if ($confirmation_page->post_status == 'publish') {
            return get_permalink($confirmation_page) . '?redirect_to=' . $original_id;
        }

        return get_home_url();
    }
}
