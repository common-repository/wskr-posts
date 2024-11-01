<?php


class WskrClient
{

    /**
     * Api Base
     */
    private $apiBase;


    /**
     * Authorization Token
     */
    private $authorizationToken;


    /**
     * Create a new RealmClient Instance
     */
    public function __construct($apiBase, $authorizationToken)
    {
        $this->apiBase = $apiBase;
        $this->authorizationToken = $authorizationToken;
    }


    /**
     * Authorise personal account.
     *
     * @param string $email             The email
     * @param string $password          The password
     *
     * @return mixed
     */
    public function Authorise($email, $password)
    {
        try {
            $url = $this->apiBase . '/Authorise';
            $args = array(
                'body' => array(
                    'email'     => $email,
                    'password'  => $password
                )
            );

            $responseArr = wp_remote_post( $url , $args );
            $body = $responseArr['body'];
            $response = $responseArr['response'];

            $body = json_decode($body, True);
            $response = json_decode($response, True);

            if ($body['status'] === 'success' && $body['data']) {
                return $body['data'];
            }

            return (object) array(
                'error' => 'unexpected-response',
                'response'=> $response,
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error' => 'authorise-failed',
                'response'=> $exception,
            );
        }
    }


    /**
     * Handle payment with WSKR.
     *
     * @param string $businessId     The Id of business account
     * @param string $contentUrl            The url to be charged
     * @param string $tokenValue     The token value for a given url
     *
     * @return mixed
     */
    public function Pay($businessId, $contentUrl, $tokenValue)
    {
        try {
            $url = $this->apiBase . '/Pay';
            $fields = array(
                'businessId'    => $businessId,
                'url'           => $contentUrl,
                'tokenValue'    => $tokenValue
            );
            $args = array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'User-Agent'    => 'WSKR for WordPress',
                    'Authorization' => 'Bearer ' . $this->authorizationToken,
                ),
                'body' => json_encode($fields),
                'method'    => 'POST',
                'sslverify' => false,
            );

            $responseArr = wp_remote_post( $url , $args );
            //$body = $responseArr['body'];
            $body = json_decode($responseArr['body'], true, JSON_UNESCAPED_SLASHES); // v1.2.2
            $response = $responseArr['response'];

            if ($body['status'] === 'success') {
                $httpcode = 200;
            } else if ($body['status'] == 'fail' && $body['data'] == 302) { 
                $httpcode = 302;
            } else {
                $httpcode = $response['code'];
            }
            return (object) array(
                'code'  => $httpcode,
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error' => 'payment-failed',
                'response'=> $exception
            );
        }
    }


    /**
     * Fetch Wordpress Credential
     *
     * @param string $domain    The domain of the site
     *
     * @return mixed
     */
    public function Wordpress($domain)
    {
        try {
            $url = $this->apiBase . '/Wordpress?domain=' . $domain;
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->authorizationToken
                )
            );

            $responseArr = wp_remote_get( $url, $args );
            $body = $responseArr['body'];
            $response = $responseArr['response'];

            $body = json_decode($body, True);
            $response = json_decode($response, True);

            if ($body['status'] === 'success' && isset($body['data'])) {
                return (object) array(
                    'username'  => $body['data']['username'],
                    'password'  => $body['data']['password']
                );
            }

            return (object) array(
                'error'     => 'Unknown response',
                'response'  => $response
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => 'fetch-failed',
                'response'  => $exception
            );
        }
    }
}