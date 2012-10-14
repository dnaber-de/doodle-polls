<?php

class oAuth_Request_Settings {

	/**
	 * is the object constistant to the defaults
	 *
	 * @var bool
	 */
	private $is_consistent = FALSE;

	# required settings

	/**
	 * oAuth consumer key
	 *
	 * @var string
	 */
	private $oauth_consumer_key = '';

	/**
	 * oAuth consumer secret (key)
	 *
	 * @var string
	 */
	private $oauth_consumer_secret = '';

	/**
	 * url to request for a request-token
	 *
	 * @var string
	 */
	private $request_token_url = '';

	/**
	 * url to request for a access token
	 *
	 * @var string
	 */
	private $access_token_url = '';

	# optional settings

	/**
	 * the signature method
	 *
	 * @var string
	 * @default hmac
	 */
	private $signature_method = '';

	/**
	 * hash algorythm
	 * depends on signature method
	 *
	 * @var string
	 * @default sha1
	 */
	private $hash_algorythm = '';

	/**
	 * realm
	 *
	 * @var string
	 */
	private $realm = '';

	/**
	 * http method for the request token request
	 *
	 * @var string
	 */
	private $request_token_method = '';

	/**
	 * http method for the access token request
	 *
	 * @var string
	 */
	private $access_token_method = '';

	/**
	 * pass the oauth vars in http header
	 *
	 * @var bool
	 */
	private $param_in_header = FALSE;

	# defaults

	/**
	 * required defaults
	 * these values MUST not be empty
	 *
	 * @var array
	 */
	private static $required = array(
		'request_token_url'     => '',
		'access_token_url'      => '',
		'oauth_consumer_key'    => '',
		'oauth_consumer_secret' => '',
	);

	/**
	 * optional defaults
	 *
	 * @var array
	 */
	private static $optional = array(
		'signature_method'      => 'hmac',
		'hash_algorythm'        => 'sha1',
		'realm'                 => '',
		'request_token_method'  => 'GET',
		'access_token_method'   => 'GET',
		'param_in_header'       => FALSE
	);

	/**
	 * intercept reading access to non public properties
	 *
	 * @param string $property
	 * @retun mixed
	 */
	public function __get( $property ) {

		if ( ! $this->is_consistent )
			$this->check_constistency();

		return $this->$property;
	}

	/**
	 * intercept writing access to non public properties
	 *
	 * @param string $property
	 * @param  mixed $value
	 * @return void
	 */
	public function __set( $property, $value ) {

		if ( $this->is_consistent && ! empty( $this->{ $property } ) )
			return FALSE;

		elseif ( ! isset( self::$required[ $property ] )
		      && ! isset( self::$optional[ $property ] )
		)
			return $this->alert( 'Unknown Property ' . $property );

		$this->{ $property } = $value;
	}

	/**
	 * check the object for consistency
	 *
	 * @return void
	 */
	private function check_constistency() {

		foreach ( self::$required as $key => $value ) {
			if ( empty( $this->{ $key } ) && empty( $value ) )
				return $this->alert( 'Missing Property ' . $key );
			if ( ! empty( $value ) )
				$this->{ $key } = $value;
		}

		foreach ( self::$optional as $key => $value ) {
			if ( empty( $this->{ $key } ) )
				$this->{ $key } = self::$optional[ $key ];
		}

		$this->is_consistent = TRUE;
	}

	/**
	 * set defaults
	 *
	 * @param string $propery
	 * @param mixed $value
	 */
	public static function set_defaults( $property, $value = '' ) {

		if ( isset( self::$required[ $property ] ) )
			self::$required[ $property ] = $value;

		elseif ( isset( self::$optional[ $property ] ) )
			self::$optional[ $property ] = $value;

	}

	/**
	 * kick me, i'm an error
	 *
	 * @param string $message
	 * @return FALSE
	 */
	private function alert( $message ) {

		$trigger = debug_backtrace();
		$trigger = $trigger[ 3 ];
		$message .= ' in <b>' . $trigger[ 'file' ] . '</b> on line ' . $trigger [ 'line' ];

		trigger_error( $message, E_USER_NOTICE );

		return FALSE;
	}
}

