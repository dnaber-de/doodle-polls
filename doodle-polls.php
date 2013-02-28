<?php
/**
 * Plugin Name: Doodle Polls
 * Description: Sync Doodle Polls to a WP custom post type
 * Plugin URI:
 * Version:     2013.02.28
 * Author:      David Naber
 * Author URI:  http://dnaber.de/
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 * Textdomain:  doodle_polls
 */
if ( ! function_exists( 'add_filter' ) )
	exit( 'Where\'s my WP?' );

# load classes and function files
spl_autoload_register( array( 'WP_Doodle_Polls', 'autoload' ) );
require_once 'doodle_functions.php';

# register static hooks
register_activation_hook( __FILE__,   array( 'WP_Doodle_Polls', 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'WP_Doodle_Polls', 'on_deactivation' ) );
register_uninstall_hook( __FILE__,    array( 'WP_Doodle_Polls', 'on_uninstall' ) );

add_action( 'init',                 array( 'WP_Doodle_Polls',     'get_instance' ) );
add_action( 'wp_doodle_polls_init', array( 'WP_Doodle_Post_Type', 'init' ),         10, 1 );
add_action( 'wp_doodle_polls_init', array( 'WP_Doodle_Admin_UI',  'get_instance' ), 10, 1 );
add_action( 'wp_doodle_polls_init', array( 'WP_Doodle_Admin',     'get_instance' ), 10, 1 );
add_action( 'wp_doodle_polls_init', array( 'WP_Doodle_Frontend',  'get_instance' ), 10, 1 );

class WP_Doodle_Polls {

	const VERSION = '0.1';

	/**
	 * i'am the one and only
	 *
	 * @var WP_Doodle_Polls
	 */
	private static $instance = NULL;

	/**
	 * the plugins base url
	 *
	 * @var string
	 */
	public static $url = '';

	/**
	 * the plugins base directory
	 *
	 * @var string
	 */
	public static $dir = '';

	/**
	 * current status of the plugin
	 *
	 * @var string
	 */
	private static $status = '';

	/**
	 * get the instance to remove filters/actions
	 * if neccessary
	 *
	 * @return WP_Doodle_Polls
	 */
	public static function get_instance() {

		if ( ! self::$instance instanceof self ) {
			$instance = new self();
			$instance->init();
			self::$instance = $instance;
		}

		return self::$instance;
	}

	/**
	 * start the plugin
	 *
	 * @return void
	 */
	protected function init() {

		# setup some defaults
		self::$dir = dirname( __FILE__ );
		self::$url = plugins_url( '', __FILE__ );

		# frequently needed oAuth parameter
		oAuth_Request_Settings::set_defaults( 'request_token_url', 'https://doodle-test.com/api1/oauth/requesttoken' );
		oAuth_Request_Settings::set_defaults( 'access_token_url',  'https://doodle-test.com/api1/oauth/accesstoken' );
		oAuth_Request_Settings::set_defaults( 'realm',              home_url() );
		oAuth_Request_Settings::set_defaults( 'param_in_header',    FALSE );
		oAuth_Request::$http_adapter = 'Wp_Http_Adapter';

		# return here on de-/activation / uninstallation
		if ( in_array( $this->status, array( 'activate', 'deactivate', 'uninstall' ) ) )
			return;

		# run all components
		do_action( 'wp_doodle_polls_init', $this );
	}

	/**
	 * do stuff when plugin is activated
	 *
	 * @wp-hook activate_wp-doodle-polls/wp-doodle-polls.php
	 * @return void
	 */
	public static function on_activation() {

		remove_filter( 'init', array( __CLASS__, 'get_instance' ) );
		self::$status = 'activate';

		$self = self::get_instance();
		do_action( 'wp_doodle_polls_activate', $self );
	}

	/**
	 * and stuff when plugin is deactivateed
	 *
	 * @wp-hook deactivate_wp-doodle-polls/wp-doodle-polls.php
	 * @return void
	 */
	public static function on_activation() {

		remove_filter( 'init', array( __CLASS__, 'get_instance' ) );
		self::$status = 'deactivate';

		$self = self::get_instance();
		do_action( 'wp_doodle_polls_deactivate', $self );
	}

	/**
	 * and stuff when plugin is uninstalled
	 *
	 * @wp-hook uninstall_wp-doodle-polls/wp-doodle-polls.php
	 * @return void
	 */
	public static function on_uninstall() {

		remove_filter( 'init', array( __CLASS__, 'get_instance' ) );
		self::$status = 'uninstall';

		$self = self::get_instance();
		do_action( 'wp_doodle_polls_uninstall', $self );
	}

	/**
	 * autoloader for the environment
	 *
	 * @param string $class Classname
	 * @return void
	 */
	public static function autoload( $class ) {

		$dirs = array(
			dirname( __FILE__ ) . '/api',
			dirname( __FILE__ ) . '/model',
			dirname( __FILE__ ) . '/view',
			dirname( __FILE__ ) . '/controler'
		);

		foreach ( $dirs as $dir ) {
			if ( file_exists( $dir . '/class-' . $class . '.php' ) )
				require_once $dir . '/class-' . $class . '.php';
		}
	}

}

	#sample doodle-api request;

	/*

	$doodle_settings = new oAuth_Request_Settings();
	$doodle_settings->oauth_consumer_key     = '544d5ze6inlc0jtzdzihn47d67z77i7r';
	$doodle_settings->oauth_consumer_secret  = 'evmg06rw2wl3108xb18fbuxauzt720nz';


	$api = new oAuth_Request( $doodle_settings );
	$api->set( 'param_in_header', TRUE );
	$request = $api->get( 'https://doodle-test.com/api1/polls/k3sasxitg3hi5nbn' );

	$poll = simplexml_load_string( $request[ 'body' ], 'Not_So_Simple_XML', LIBXML_NOCDATA );
	header( 'Content-type:text/plain;charset=utf-8' );
	echo $poll->as_formated_xml(); exit;
	*/

#include 'plain-request.php'; exit;
# api access https://doodle.com/mydoodle/consumer/credentials.html
# key     544d5ze6inlc0jtzdzihn47d67z77i7r
# secret  evmg06rw2wl3108xb18fbuxauzt720nz
# poll id k3sasxitg3hi5nbn

# signing requests
# @link https://developers.google.com/accounts/docs/OAuth_ref#SigningOAuth
