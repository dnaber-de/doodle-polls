<?php

/**
 * handle all global options for the plugin
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Settings {

	/**
	 * key for the options table
	 *
	 * @const string
	 */
	const OPTION_KEY = 'wp_doodle_polls_options';

	/**
	 * default options
	 *
	 * @var array
	 */
	protected static $default_options = array(
		'default_sync_intervall' => 'dayly',
	);

	/**
	 * options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * instance
	 *
	 * @var WP_Doodle_Settings
	 */
	private static $instance = NULL;

	/**
	 * get the instance and init
	 *
	 * @param WP_Doodle_Polls $plugin
	 * @return WP_Doodle_Settings
	 */
	public static function get_instance( $plugin = NULL ) {

		if ( ! self::$instance instanceof self ) {
			$new = new self( $plugin );
			$new->init();
			self::$instance = $new;
		}

		return self::$instance;
	}

	/**
	 * init
	 *
	 * @param WP_Doodle_Polls $plugin
	 */
	protected function init( $plugin = NULL ) {

		$this->load_options();

		# @todo settings api
	}

	/**
	 * getter for a single option key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get( $key = '' ) {

		if ( isset( $this->options[ $key ] ) )
			return $this->options[ $key ]

		return NULL;
	}

	/**
	 * load options
	 *
	 * @var return void
	 */
	public function load_options() {

		$this->options = get_option( self::OPTION_KEY );
		if ( empty( $this->options ) )
			$this->options = self::$default_options;
	}

}
