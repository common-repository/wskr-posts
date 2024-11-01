<?php

/**
 * Register all Rest APIs for the plugin
 *
 * @link       https://wskr.ie/
 * @since      1.0.0
 *
 * @package    WSKR
 * @subpackage WSKR/includes
 */

/**
 * Register all Rest APIs for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    WSKR
 * @subpackage WSKR/includes
 * @author     WSKR Limited <support@wskr.ie>
 */
class Wskr_RestAPI {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.5
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.5
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Domain of the site
     *
     * @since    1.0.5
     * @access   private
     * @var      string    $domain    The domain name of the site.
     */
    private $domain;

    /**
     * Api Base
     *
     * @since    1.0.5
     * @access   private
     * @var      string    $domain    The domain name of the site.
     */
    private $apiBase;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->domain = get_home_url();
        $this->apiBase = 'https://api.wskr.ie';

        add_action('rest_api_init', array($this, 'register_rest_api'));
    }

    /**
     * Register REST API
     *
     * @return void
     */
    public function register_rest_api()
    {
        register_rest_route( $this->plugin_name, '/authorise', array(
            'methods'  => 'POST',
            'callback' => array($this, 'handleAuthorise'),
            'args' => array(
                'email' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return $this->valid_email( $param );
                    }
                ),
                'password' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_string( $param );
                    }
                ),
            ),
        ));

        register_rest_route( $this->plugin_name, '/pay', array(
            'methods'  => 'POST',
            'callback' => array($this, 'handlePayment'),
            'args' => array(
                'contentUrl' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return $this->validate_url( $param );
                    }
                ),
                'tokenValue' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
                'redirectTo' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            ),
        ));

        register_rest_route( $this->plugin_name, '/wordpress', array(
            'methods'  => 'GET',
            'callback' => array($this, 'fetchWordpressCredential')
        ));

        register_rest_route( $this->plugin_name, '/login', array(
            'methods'  => 'POST',
            'callback' => array($this, 'handleLogin'),
            'args' => array(
                'username' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_string( $param );
                    }
                ),
                'password' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_string( $param );
                    }
                ),
            ),
        ));
    }

    /**
     * Handle the Account Authorisation (Personal Account)
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleAuthorise(WP_REST_Request $request)
    {
        try {
            $email = $request->get_param('email');
            $password = $request->get_param('password');

            $wskr = new WskrClient($this->apiBase, null);
            $response = $wskr->Authorise($email, $password);

            if (!isset($response) || !$response) {
                return new WP_REST_Response([
                    'error'     => 'payment-failed',
                    'message'   => 'Service unavailable.'
                ], 500);
            }

            if ($response && $response->error) {
                return new WP_REST_Response([
                    'error'     => $response->error,
                    'message'   => 'Unknown server error',
                ], 500);
            }

            if ($response === 301) {
                return new WP_REST_Response([
                    'error'     => 'invalid-credential',
                    'message'   => 'Invalid email or password.',
                ], 301);
            }

            return new WP_REST_Response([
                'token'     => $response
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'authorise-failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Handle the Payment
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handlePayment(WP_REST_Request $request)
    {
        try {
            $contentUrl = $request->get_param('contentUrl');
            $tokenValue = $request->get_param('tokenValue');
            $redirectTo = $request->get_param('redirectTo');

            $authToken = $this->getAuthToken();
            if (!$authToken) {
                return new WP_REST_Response([
                    'error'       => 'login-required',
                    'message'     => 'You do not appear to be logged in.'
                ], 300);
            }

            $businessId = $this->getBusinessId();
            if (!$businessId) {
                return new WP_REST_Response([
                    'error'       => 'payment-failed',
                    'message'     => 'Business account is not set.'
                ], 300);
            }

            $wskr = new WskrClient($this->apiBase, $authToken);
            $response = $wskr->Pay($businessId, $contentUrl, $tokenValue);

            if (!isset($response) || !$response) {
                return new WP_REST_Response([
                    'error'       => 'payment-failed',
                    'message'     => 'Service unavailable.'
                ], 500);
            }

            if ($response->error) {
                return new WP_REST_Response([
                    'error'       => $response->error,
                    'message'     => 'Unknown server error',
                ], 500);
            }

            if ($response->code == 200) {
                $result = $this->updatePurchasedPosts($redirectTo);
                if (!$result) {
                    return new WP_REST_Response([
                        'error'      => 'payment-failed',
                        'message'    => 'DB updating is failed'
                    ], 500);
                }
            }

            return new WP_REST_Response([
                'code'      => $response->code,
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'payment-failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch Wordpress Credential
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function fetchWordpressCredential(WP_REST_Request $request)
    {
        try {
            $authToken = $this->getAuthToken();
            if (!$authToken) {
                return new WP_REST_Response([
                    'error'       => 'login-required',
                    'message'     => 'You do not appear to be logged in.'
                ], 300);
            }

            $wskr = new WskrClient($this->apiBase, $authToken);
            $response = $wskr->Wordpress($this->domain);

            if (!isset($response) || !$response) {
                return new WP_REST_Response([
                    'error'       => 'fetch-failed',
                    'message'     => 'Service unavailable.'
                ], 500);
            }

            if ($response->error) {
                return new WP_REST_Response([
                    'error'       => $response->error,
                    'message'     => 'Unknown server error',
                ], 500);
            }

            return new WP_REST_Response([
                'username'    => $response->username,
                'password'    => $response->password
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }


    /**
     * Login to the main site automatically
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleLogin(WP_REST_Request $request)
    {
        try {
            $username = $request->get_param('username');
            $password = $request->get_param('password');

            $email = $username . '@my.wskr.ie'; // v1.2.3
            $loggedIn = false;

            $user = get_user_by( 'email', $email );
            if ( $user ) {
                $user_id = $user->ID;
                wp_set_password( $password, $user_id );
                $loggedIn = $this->autoLogin($email, $password);
            } else {
                $user_data = array(
                    'first_name'    => 'WSKR', // v1.2.3
                    'last_name'     => 'Member', // v1.2.3
                    'user_email'    => $email,
                    'user_pass'     => $password,
                    'user_login'    => $username, // v1.2.3
                    'user_nicename' => $username, // v1.2.3
                    'display_name'  => 'WSKR Member', // v1.2.3
                    'nickname'      => 'WSKR', // v1.2.3
                    'role'          => 'wskr_member'
                );
                $user_id = wp_insert_user( $user_data );
                if( !is_wp_error( $user_id ) ) {
                    $loggedIn = $this->autoLogin($email, $password);
                }
            }

            return new WP_REST_Response([
                'isLoggedIn'    => $loggedIn
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'login-failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Get a business id for the active site
     *
     * @return string
     */
    private function getBusinessId()
    {
        $businessId = get_option('wskr_business_id');

        if (!isset($businessId) || empty($businessId))
            return null;

        return $businessId;
    }

    /**
     * Get auth token for personal account
     *
     * @return string
     */
    private function getAuthToken()
    {
        if(!isset($_COOKIE['wskr_auth_token']) && empty($_COOKIE['wskr_auth_token']))
            return null;

        return $_COOKIE['wskr_auth_token'];
    }

    /**
     * Get host of the site
     *
     * @param string $url       The url
     * @param string $api_url   The api url
     *
     * @return string
     */
    private function getHost($url = '', $api_url = '')
    {
        $get_domain = $get_url = '';
        if (empty($url) && !empty($api_url)) {
            $get_url = home_url();
            $parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
            $get_domain = $api_url.$parse_url['host'];
        } else if (!empty($url) && !empty($api_url)) {
            $get_url = $url;
            $parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
            $get_domain = $api_url.$parse_url['host'];
        } else {
            if(!empty($url)) {
                $get_url = $url;
                $parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
                if (!empty($parse_url) && $parse_url['host'] == 'localhost') {
                    $get_domain = str_replace('http://localhost/', '', $get_url).'.com';
                } else {
                    $get_domain = $parse_url['host'];
                }
            } else {
                $get_url = home_url();
                $parse_url = !empty(parse_url($get_url)) ? parse_url($get_url) : '';
                if (!empty($parse_url) && $parse_url['host'] == 'localhost') {
                    $get_domain = str_replace('http://localhost/', '', $get_url).'.com';
                } else {
                    $get_domain = $parse_url['host'];
                }
            }
        }
        return $get_domain;
    }

    /**
     * WSKR Login (Auto)
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     *
     * @return boolean
     */
    private function autoLogin($email = '', $password = '', $remember = false)
    {
        $creds = array(
            'user_login'    => $email,
            'user_password' => $password,
            'remember'      => $remember
        );
        $user = wp_signon( $creds, false );

        if (is_wp_error($user))
            return false;

        return true;
    }

    /**
     * Update purchased content list
     *
     */
    private function updatePurchasedPosts($postId)
    {
        try {
            $user = wp_get_current_user();
            if (!$user)
                return false;

            $userId = $user->get_ID() ? $user->get_ID() : $user->data->ID;
            $postIds = get_user_meta($userId, 'wskr_purchased_posts', true);
            $postIds = json_decode($postIds, true);

            if (!isset($postIds) || !$postIds)
                $postIds = array();

            if(!in_array($postId, $postIds, true)){
                array_push( $postIds, $postId );
            }
            update_user_meta($userId, 'wskr_purchased_posts', json_encode($postIds));

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Validate email
     *
     * @param $str
     * @return bool
     */
    private function valid_email($str) {

        return filter_var($str, FILTER_VALIDATE_EMAIL);

    }

    /**
     * Validate url
     *
     * @param $url
     * @return bool
     */
    private function validate_url($url) {

        return filter_var($url, FILTER_VALIDATE_URL);

    }
}
