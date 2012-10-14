<?php

/**
 * some basic signature issues
 *
 * @author David Naber <kontakt@dnaber.de>
 */

class oAuth_Signature {

	/**
	 * builds a oAuth compatible query string of parameters
	 *
	 * @param   array $param
	 * @return string
	 */
	public function get_signature_query_string( $param ) {

		if ( empty( $param ) )
			return '';

		$param_keys   = array_map( 'oAuth_urlencode', array_keys( $param ) );
		$param_values = array_map( 'oAuth_urlencode', array_values( $param ) );
		$param        = array_combine( $param_keys, $param_values );
		uksort( $param, 'strcmp' );
		foreach ( $param as $k => $v ) {
			if ( is_array( $v ) ) {
				sort( $v, SORT_STRING );
				foreach ( $v as $duplicate )
					$param[] = $k . '=' . $duplicate;
			} else {
				$param[] = $k . '=' . $v;
			}
			unset( $param[ $k ] );
		}

		return implode( '&', $param );
	}

	/**
	 * builds the signature base string
	 *
	 * @param string $method
	 * @param string $url
	 * @param  array $param
	 */
	public function get_signature_base_string( $method, $url, $param = array() ) {

		return
			  $method
			. '&'
			. oAuth_urlencode( $url )
			. '&'
			. oAuth_urlencode( $this->get_signature_query_string( $param ) )
		;
	}

	/**
	 * get the object for the desired method
	 *
	 * @static
	 * @return oAuth_Signature child
	 */
	public static function factory( $method, $algo = '' ) {

		switch ( $method ) {
			case 'hmac' :
				return new oAuth_Signature_Hmac( $algo );
			break;
			# @todo implement other methods
		}
	}

}
