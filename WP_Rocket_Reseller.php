<?php
/**
 * WP Rocket is a WordPress premium caching plugin that provides both lightning speed for your website and easy set up for you.
 *
 * This library provides connectivity with the WP Rocket Reseller API
 *
 * Basic usage:
 *
 * 1. Configure your reseller account with your access credentials
 * <code>
 * <?php
 * $wp_rocket = new WP_Rocket_Reseller('YOUR_APP_EMAIL', 'YOUR_API_KEY');
 * ?>
 * </code>
 *
 * 2. Make requests to the API
 * <code>
 * <?php
 * $wp_rocket = new WP_Rocket_Reseller('YOUR_APP_EMAIL', 'YOUR_API_KEY');
 * $orders = $wp_rocket->getOrders();
 * var_dump($orders);
 * ?>
 * </code>
 *
 * @author    Jonathan Buttigieg <jonathan@wp-rocket.me>
 * @copyright Copyright 2015 WP Media
 * @link      http://wp-rocket.me
 * @license   http://opensource.org/licenses/MIT
 **/

/**
 * WP Rocket Reseller API
 */
class WP_Rocket_Reseller
{
    /**
     * The WP Rocket Reseller API endpoint
     */
    private $apiEndpoint = 'https://support.wp-rocket.me/api/v1/reseller/';

    /**
     * The WP Rocket Reseller application ID (email)
     */
    private $appId = '';

    /**
     * The WP Rocket Reseller API key
     */
    private $apiKey = '';

    /**
     * The constructor
     *
     * @param  string $appId  The WP Rocket Reseller application ID (email)
     * @param  string $apiKey The WP Rocket Reseller API key
     * @return void
     **/
    public function __construct( $appId, $apiKey )
    {
        $this->appId  = $appId;
        $this->apiKey = $apiKey;
    }

	/**
     * Create an event associated with a user on your WP Rocket Reseller account
     *
     * @param  string $email       The email of the customer
     * @param  string $first_name  The first name of the customer
     * @param  string $last_name   The last name of the customer
     * @param  string $licence     The licence to give to the customer
     							   Defaults to perso
	 							   By default are available the licences: perso, business, pro, renew
     * @param  string $force       Force the new licence to be combined with current one
     							   Defaults to false
	 							   For example, if the customer has a business licence 
	 							   and you want to offer him a new personal licence (3+1 = 4 websites)
     * @return object
     **/
    public function createOrder($email, $first_name, $last_name, $licence = 'perso', $force = false)
    {
        $data = array();
		$data['email']      = $email;
		$data['first_name'] = $first_name;
		$data['last_name']  = $last_name;
		$data['licence']    = $licence;
		$data['force']      = $force;
		
        $path = 'orders';

        return $this->httpCall(
            $this->apiEndpoint . $path,
            'POST',
            json_encode($data)
        );
    }

    /**
     * Get a specific order from your WP Rocket Reseller account.
     *
     * @param  string $id The ID of the order to retrieve
     * @return object
     **/
    public function getOrder($id)
    {
        return $this->httpCall($this->apiEndpoint . 'orders/' . $id);
    }

    /**
     * Get all orders from your WP Rocket Reseller account.
     *
     * @return object
     **/
    public function getOrders()
    {
        return $this->httpCall($this->apiEndpoint . 'orders');
    }

    /**
     * Get orders count from your WP Rocket Reseller account.
     *
     * @return object
     **/
    public function getOrdersCount()
    {
        return $this->httpCall($this->apiEndpoint . 'orders/count');
    }

    /**
     * Make an HTTP call using curl.
     *
     * @param  string $url       The URL to call
     * @param  string $method    The HTTP method to use, by default GET
     * @param  string $post_data The data to send on an HTTP POST (optional)
     * @return object
     **/
    protected function httpCall($url, $method = 'GET', $post_data = null)
    {
        $header     = array();
        $headers[]  = 'Accept: application/json';
        $headers[]  = 'Content-Type: application/json';
        $headers[]  = 'Authorization: Basic ' . base64_encode( $this->appId . ':' . $this->apiKey );

        $ch = curl_init($url);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_POST, true);
        }

        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response  = curl_exec($ch);
        $error     = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code == 500) {
            return array(
                'error' => $error
            );
        } else {
            return json_decode($response);
        }
    }
}