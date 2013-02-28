<?php

// just a test…

namespace dna;

function sign_request( $method, $url, $param = array() ) {

	# remove query-string from url
	$url = preg_replace( '~\?.*$~', '', $url );

	# remove realm and signature parameters
	if ( isset( $param[ 'realm' ] ) )
		unset( $param[ 'realm' ] );

	if ( isset( $param[ 'oauth_signature' ] ) )
		unset( $param[ 'oauth_signature' ] );

	ksort( $param );
	$param = http_build_query( $param, '', '%26' );
	$param = str_replace( '=', '%3D', $param );

	$base =
		  $method
		. '&'
		. urlencode( $url )
		. '&'
		. $param
	;

	return hash_hmac( 'sha1', $base, 'evmg06rw2wl3108xb18fbuxauzt720nz' );
}

function create_nonce() {

	$nonce = '';
	for ( $i = 0; $i <= 13; $i++ ) {
		$nonce .= rand( 0, 9 );
	}

	return $nonce;
}

function build_header( $method, $url ) {
		$header = array();
		$param  = array();

		$param[ 'realm' ]                  = '';
		$param[ 'oauth_consumer_key' ]     = '544d5ze6inlc0jtzdzihn47d67z77i7r';
		$param[ 'oauth_signature_method' ] = 'HMAC-SHA1';
		$param[ 'oauth_timestamp' ]        = time();
		$param[ 'oauth_nonce' ]            = create_nonce();
		$param[ 'oauth_signature' ]        = sign_request( $method, $url, $param );


		foreach ( $param as $name => $value ) {
			$param[] = $name . '="' . $value . '"';
			unset( $param[ $name ] );
		}
		$header[] = 'Authorization: OAuth ' . implode( ', ', $param );

		return $header;
}
$url    ='https://doodle-test.com/api1/oauth/requesttoken';
$header = build_header( 'GET', $url );
$curl = curl_init( $url );
#$curl = curl_init( 'http://localhost/oauth.php' );

curl_setopt( $curl, CURLOPT_HTTPHEADER,     $header );
curl_setopt( $curl, CURLOPT_HEADER,         TRUE );
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
curl_setopt( $curl, CURLOPT_USERAGENT,      'Jakarta Commons-HttpClient/3.1' );

$response = curl_exec( $curl );
curl_close( $curl );

header( 'Content-type:text/plain;charset=utf-8' );
var_dump( $response ); exit;
