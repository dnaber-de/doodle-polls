<?php

/**
 * Adapt the WP HTTP API
 *
 * @see WP_Http
 * @author David Naber
 */

class WP_Http_Adapter {

	public $http_version = '1.1';

	public function post( $url, $headers = array(), $param = array(), $args = array() ) {

		return $this->request( 'POST', $url, $headers, $param, $args );
	}

	public function get( $url, $headers = array(), $param = array(), $args = array() ) {

		return $this->request( 'GET', $url, $headers, $param, $args );
	}

	public function head() {

		return $this->request( 'HEAD', $url, $headers, $param, $args );
	}

	public function delete() {

		#@todo
	}

	public function request( $method, $url, $headers = array(), $param = array(), $args = array() ) {

		$defaults = array(
			'httpversion' => $this->http_version,
			'user-agent' => ''
		);
		$args = wp_parse_args( $args, $defaults );
		# @todo handle $params
		if ( ! empty( $headers ) && is_array( $headers ) )
			$args[ 'headers' ] = $headers;

		switch ( $method ) {

			case 'GET' :
				$return = wp_remote_get( $url, $args );
			break;

			case 'POST' :
				$return = wp_remote_post( $url, $args );
			break;

			case 'HEAD' :
				$return = wp_remote_head( $url, $args );
			break;

			default :
				$return = FALSE;
			break;
		}

		if ( ! is_a( $return, 'WP_Error' ) )
			return $return;

		# @todo proper error handling
		echo '<pre>'; var_dump( $return ); exit;

	}


}
