<?php

/**
 * Adapts the Doodle API to some handy methods
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_API {

	/**
	 * doodle api base url
	 *
	 * @var string
	 */
	public static $base_url = 'https://doodle-test.com';

	/**
	 * consumer
	 *
	 * @var WP_Doodle_Consumer
	 */
	public $consumer = NULL;

	/**
	 * constructor
	 *
	 * @param WP_Doodle_Consumer $consumer
	 */
	public function __construct( $consumer = NULL ) {

		$this->consumer = $consumer;
	}

	/**
	 * get poll
	 *
	 * @param string $poll_ID
	 * @param string $poll_key (Optional)
	 * @return string
	 */
	public function get_poll( $poll_ID, $poll_key = '' ) {

		$settings = new oAuth_Request_Settings();
		$settings->oauth_consumer_key = $this->consumer->key;
		$settings->oauth_consumer_secret = $this->consumer->secret;
		$doodle = new oAuth_Request( $settings );
		$doodle->set( 'param_in_header', TRUE );
		$poll = $doodle->get( self::$base_url . '/api1/polls/' . $poll_ID );
		if ( empty( $poll[ 'response' ] ) )
			return '';

		if ( 200 != $poll[ 'response' ][ 'code' ] )
			return FALSE;

		return $poll[ 'body' ];
	}

}
