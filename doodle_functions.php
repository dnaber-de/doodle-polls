<?php

if ( ! function_exists( 'oAuth_urlencode' ) ) {

	/**
	 * urlencode rfc3986
	 * seen in OAuthUtil::urlencode_rfc3986
	 * @link http://oauth.googlecode.com/svn/code/php/OAuth.php
	 *
	 * @param  string
	 * @return string
	 */
	function oAuth_urlencode( $string ) {

		return str_replace(
			array( '+', '%7E' ),
			array( ' ', '~' ),
			rawurlencode( $string )
		);
	}
}

if ( ! function_exists( 'doodle_time_format' ) ) {

	/**
	 * format the time like ISO ...
	 *
	 * @var int $timestamp
	 * @return string
	 */
	function doodle_time_format( $timestamp ) {

		#@todo set time to UTC
		return date( 'Y-m-d\TH:i:s\Z', $timestamp );
	}
}

if ( ! function_exists( 'doodle_time_format_escape' ) ) {

	/**
	 * adds a backslash to each character in the string
	 *
	 * @var string $word
	 * @return string
	 */
	function doodle_time_format_escape( $word ) {

		$word = str_split( $word );

		return '\\' . implode( '\\', $word );
	}
}

if ( ! function_exists( 'doodle_option_sort_callback' ) ) {

	/**
	 * callback for usort() to sort the options
	 * by participation of 'yes' and 'maybe'
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	function doodle_option_sort_callback( $a, $b ) {

		# yes comes first
		if ( $a[ 'yes' ] > $b[ 'yes' ] )
			return -1;

		if ( $a[ 'yes'] < $b[ 'yes' ] )
			return 1;

		if ( $a[ 'maybe' ] > $b[ 'maybe' ] )
			return -1;

		if ( $a[ 'maybe' ] < $b[ 'maybe' ] )
			return 1;

		return 0;
	}

}
