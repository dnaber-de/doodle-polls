<?php

/**
 * represents a consumer identified by 'consumer key' and 'consumer secret'
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Consumer {

	/**
	 * consumer key
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * consumer secret
	 *
	 * @var string
	 */
	public $secret = '';

	/**
	 * setup data from an array or stdClass
	 *
	 * @param array|stdClass $consumer_data
	 * @return WP_Doodle_Consumer
	 */
	public function __construct( $consumer_data = array() ) {

		if ( empty( $consumer_data ) )
			return;

		if ( is_a( $consumer_data, 'stdClass' ) )
			$consumer_data = get_object_vars( $consumer_data );

		$this->key    = isset ( $consumer_data[ 'key' ] )    ? $consumer_data[ 'key' ] : '';
		$this->secret = isset ( $consumer_data[ 'secret' ] ) ? $consumer_data[ 'secret' ] : '';
	}

	/**
	 * get the consumer data
	 *
	 * @param string $type (array_a|array_n|object)
	 * @return array|stdClass
	 */
	public function get_data( $type = 'object' ) {

		$property_map = array( 'key', 'secret' );
		switch ( $type ) {
			case 'array_a' :
			case 'array_n' :
				$ret = array();
				foreach ( $property_map as $p ) {
					if ( 'array_a' == $type )
						$ret[ $p ] = $this->{ $p };
					else
						$ret[] = $this->{ $p };
				}
			break;

			default :
				$ret = new stdClass;
				foreach ( $property_map as $p ) {
					$ret->{ $p } = $this->{ $p  };
				}
			break;
		}

		return $ret;
	}
}
