<?php

/**
 * testclass for oAuth
 * Implements the oAuth 1.0a protocol
 *
 * @link http://oauth.net/core/1.0a/
 * @author David Naber <kontakt@dnaber.de>
 */

class oAuth_Request {

	const OAUTH_VERSION = '1.0';

	public static $http_adapter      = '';

	/**
	 * settings object
	 *
	 * @var oAuth_Request_Settings
	 */
	protected $settings              = '';

	# dynamic settings

	/**
	 * send the oauth parameter in http-header
	 * otherwise as query string
	 *
	 * @param bool
	 */
	protected $param_in_header       = FALSE;

	# dynamic settings end

	/**
	 * current request method
	 *
	 * @var string
	 */
	protected $request_method        = '';

	/**
	 * current request url
	 *
	 * @var string
	 */
	protected $request_url           = '';

	/**
	 * current object status
	 * can be request, access or signed_in
	 *
	 * @var string
	 */
	protected $status                = '';

	/**
	 * the request token
	 *
	 * @var string
	 */
	protected $oauth_token           = '';

	/**
	 * the secret request token
	 * used to sign the access-token request
	 *
	 * @var string
	 */
	protected $oauth_token_secret    = '';

	/**
	 * the access token
	 *
	 * @var string
	 */
	protected $oauth_access_token   = '';

	/**
	 * access token secret
	 *
	 * @var string
	 */
	protected $oauth_access_secret = '';


	/**
	 * constructor
	 *
	 * @param oAuth_Request_Settings $settings
	 */
	public function __construct( $settings ) {

		$this->settings           = $settings;
		# dynamic settings
		$this->param_in_header    = $this->settings->param_in_header;

		$this->status             = 'request';
		$this->sign_in();
	}

	protected function sign_in() {

		$this->get_request_token();
		$this->get_access_token();
		//oAuth_Request::$status should be 'signed_in' now

		#test start
		#$this->oauth_access_token = 'bd3331fe243e08aee516520913d8172';
		#$this->oauth_access_secret = 'b6ed8cee357a7332ddd702747e899';
		#$this->status = 'signed_in';
		#test end
	}

	/**
	 * made an authenticated request
	 *
	 * @param string $method
	 * @param string $url
	 * @param  array $headers (Optional)
	 * @return array|FALSE
	 */
	public function request( $method, $url, $headers = array() ) {

		if ( 'signed_in' != $this->status )
			return FALSE;

		$this->request_method = $method;
		$this->request_url    = $url;
		$this->build_request_parameter();

		if ( $this->param_in_header )
			$headers = array_merge( $headers, $this->get_header() );
		else
			$url .= '?' . $this->get_query_string();


		$http = new self::$http_adapter();
		#var_dump( $url, $headers ); exit;

		#test start
		#$headers = 'Authorization: ' . $headers[ 'Authorization' ];
		#var_dump( $url, $headers ); exit;
		#var_dump( $this->test_request( $method, $url, $headers ) );

		return call_user_func_array(
			array( $http, $method ),
			array( $url, $headers )
		);

	}

	/**
	 * wrapper for $this->request( 'GET', $url )
	 *
	 * @param string $url
	 * @param array $headers (Optional)
	 * @return array|FALSE
	 */
	public function get( $url, $headers = array() ) {

		return $this->request( 'GET', $url, $headers );
	}
	/**
	 * wrapper for $this->request( 'POST', $url )
	 *
	 * @param string $url
	 * @param array $headers (Optional)
	 * @return array|FALSE
	 */
	public function post( $url, $headers = array() ) {

		return $this->request( 'POST', $url, $headers );
	}

	/**
	 * get a oAuth request token
	 *
	 * @return void
	 */
	protected function get_request_token() {

		$this->request_method = $this->settings->request_token_method;
		$this->request_url    = $this->settings->request_token_url;
		$http                 = new self::$http_adapter;
		$this->build_request_parameter();

		if ( $this->param_in_header ) {
			$url     = $this->settings->request_token_url;
			$headers = $this->get_header();
		} else {
			$url     = $this->settings->request_token_url . '?' . $this->get_query_string();
			$headers = array();
		}

		$reply = call_user_func_array(
			array( $http, $this->request_method ),
			array( $url, $headers )
		);

		if ( 200 == $reply[ 'response' ][ 'code'] ) {
			$response = array();
			parse_str( $reply[ 'body' ], $response );
			$this->oauth_token = $response[ 'oauth_token' ];
			$this->oauth_token_secret = $response[ 'oauth_token_secret' ];
			$this->status = 'access';
		}
	}

	/**
	 * get the access token
	 *
	 * @return void
	 */
	protected function get_access_token() {

		if ( 'access' !== $this->status )
			return;

		$this->request_method = $this->settings->access_token_method;
		$this->request_url    = $this->settings->access_token_url;
		$this->build_request_parameter();
		$http = new self::$http_adapter();

		if ( $this->param_in_header ) {
			$url = $this->settings->access_token_url;
			$headers = $this->get_header();
		} else {
			$url = $this->settings->access_token_url . '?' . $this->get_query_string();
			$headers = array();
		}
		$reply = call_user_func_array(
			array( $http, $this->request_method ),
			array( $url, $headers )
		);

		if ( 200 == $reply[ 'response' ][ 'code'] ) {
			$response = array();
			parse_str( $reply[ 'body' ], $response );
			$this->oauth_access_token  = $response[ 'oauth_token' ];
			$this->oauth_access_secret = $response[ 'oauth_token_secret' ];
			$this->status = 'signed_in';
		}
	}

	/**
	 * get a oAuth accesstoken
	 *
	 * @return void
	 */

	/**
	 * build the oauth request header
	 *
	 * @return array
	 */
	protected function get_header() {

		$header = array( 'Authorization' => '' );
		$param  = $this->request_parameter;
		$param[ 'oauth_signature' ] = $this->get_signature();

		foreach ( $param as $name => $value ) {
			$param[] =
				  oAuth_urlencode( $name )
				. '="'
				. oAuth_urlencode( $value )
				. '"'
			;
			unset( $param[ $name ] );
		}
		$header[ 'Authorization' ] = 'OAuth ' . implode( ',', $param );

		return $header;
	}

	/**
	 * get the parameter as query string
	 *
	 * @return string
	 */
	protected function get_query_string() {

		$param = $this->request_parameter;
		$param[ 'oauth_signature' ] = $this->get_signature();

		foreach ( $param as $name => $value ) {
			$param[] =
				  oAuth_urlencode( $name  )
				. '='
				. oAuth_urlencode( $value )
			;
			unset( $param[ $name ] );
		}

		return implode( '&', $param );
	}

	/**
	 * build request parameter
	 *
	 * @return array
	 */
	protected function build_request_parameter() {

		#$param[ 'realm' ]                  = $this->settings->realm;
		$param[ 'oauth_version' ]          = self::OAUTH_VERSION;
		$param[ 'oauth_nonce' ]            = $this->create_nonce();
		$param[ 'oauth_timestamp' ]        = time();
		$param[ 'oauth_consumer_key' ]     = $this->settings->oauth_consumer_key;
		$param[ 'oauth_signature_method' ] = $this->get_signature_method();

		switch ( $this->status ) {
			case 'access' :
				$param[ 'oauth_token' ] = $this->oauth_token;
			break;

			case 'signed_in' :
				$param[ 'oauth_token' ] = $this->oauth_access_token;
			break;
		}


		$this->request_parameter = $param;

		return $param;
	}

	/**
	 * get the request signature
	 *
	 * @return string
	 */
	protected function get_signature() {

		$sign = oAuth_Signature::factory( $this->settings->signature_method, $this->settings->hash_algorythm );
		return $sign->sign_request( $this );
	}

	/**
	 * return the signatur method
	 *
	 * @return string
	 */
	public function get_signature_method() {

		$method = strtoupper( $this->settings->signature_method );
		if ( '' != $this->settings->hash_algorythm )
			$method .= '-' . strtoupper( $this->settings->hash_algorythm );

		return $method;
	}


	/**
	 * getter for the request_parameter
	 *
	 * @return string
	 */
	public function get_param() {

		return $this->request_param;
	}

	/**
	 * getter for the request method
	 *
	 * @return string
	 */
	public function get_method() {

		return $this->request_method;
	}

	/**
	 * getter for the request url
	 *
	 * @return string
	 */
	public function get_request_url() {

		return $this->request_url;
	}

	/**
	 * getter for the request parameter
	 *
	 * @return array
	 */
	public function get_request_parameter() {

		return $this->request_parameter;
	}

	/**
	 * getter for the sign keys
	 *
	 * @return array 0: consumer secret 1: token secret
	 */
	public function get_sign_key() {

		switch ( $this->status ) {
			case 'request' :
				$token_secret = '';
			break;

			case 'access' :
				$token_secret = $this->oauth_token_secret;
			break;

			case 'signed_in' :
				$token_secret = $this->oauth_access_secret;
			break;
		}

		return array(
			$this->settings->oauth_consumer_secret,
			$token_secret
		);
	}

	/**
	 * generate a request nonce
	 *
	 * @return string
	 */
	public function create_nonce() {

		return md5( microtime() . mt_rand() );
	}

	/**
	 * setter
	 *
	 * @param string $property
	 * @param mixed $value
	 * @return void
	 */
	public function set( $property, $value = NULL ) {

		if ( isset( $this->{ $property } ) )
			$this->{ $property } = $value;
	}

	/**
	 * just for debugging
	 */
	public function test_request( $http_method, $url, $auth_header = '' , $data = NULL ) {

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		switch($http_method) {
			case 'GET':
				if ($auth_header) {
					curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));
				}
			break;

			case 'POST':
				curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/xml', $auth_header ) );
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
				curl_setopt($curl, CURLOPT_HEADER, true);
			break;

			case 'PUT':
				curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml',$auth_header));
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			break;

			case 'DELETE':
				curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
			break;
		}

		$response = curl_exec($curl);
		if (!$response) {
			print("fehler: " . $response);
			$response = curl_error($curl);
			print $response;
		}
		curl_close($curl);

		return $response;
	}

}
