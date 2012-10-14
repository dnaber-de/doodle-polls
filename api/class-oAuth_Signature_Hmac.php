<?php

/**
 * signing a request
 *
 * @author David Naber <kontakt@dnaber.de>
 */

class oAuth_Signature_Hmac extends oAuth_Signature {

	protected $algorythm = '';

	public function __construct( $algorythm = 'sha1' ) {

		if ( in_array( $algorythm, hash_algos() ) )
			$this->algorythm = $algorythm;

		else
			exit( 'Wrong hash algo' ); # @todo proper error handling

	}

	/**
	 * getter for the name of signature method
	 *
	 * @return string
	 */
	public function get_signature_method() {

		return 'HMAC-' . strtoupper( $this->algorythm );
	}

	/**
	 * sign request as oAuth want's it
	 *
	 * @param  oAuth_Request $request
	 * @return string
	 */
	public function sign_request( $request ) {

		return base64_encode( $this->sign_request_raw( $request, TRUE ) );
	}

	/**
	 * just the raw output of hash_hmac()
	 *
	 * @param oAuth_Request $request
	 * @praam          bool $raw
	 * @return string|binary
	 */
	public function sign_request_raw( $request, $raw = TRUE ) {

		$base = $this->get_signature_base_string(
			$request->get_method(),
			$request->get_request_url(),
			$request->get_request_parameter()
		);

		$key = array_map( 'oAuth_urlencode', $request->get_sign_key() );
		$key = implode( '&', $key );

		return hash_hmac(
			$this->algorythm,
			$base,
			$key,
			$raw
		);

	}


}
