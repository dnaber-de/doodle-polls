<?php

/**
 * manage frequently events
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Scheduler {

	/**
	 * instance
	 *
	 * @var WP_Doodle_Scheduler
	 */
	private static $instance = NULL;

	/**
	 * meta keys represetns the cron_schedules
	 *
	 * @var array
	 */
	protected $scheduled_keys = array();

	/**
	 * get the instance
	 *
	 * @param WP_Doodle_Polls $plugin
	 * @return WP_Doodle_Scheduler
	 */
	public static function get_instance( $plugin = NULL ) {

		if ( ! self::$instance instanceof self ) {
			$new = new self;
			$new->init( $plugin );
			self::$instance = $new;
		}

		return self::$instance;
	}

	/**
	 * run all wp stuff
	 *
	 * @param WP_Doodle_Polls $plugin
	 * @return void
	 */
	protected function init( $plugin ) {

		# add post meta to new(!) polls with the default sync-intervall
		$schedules = wp_get_schedules();
		foreach ( $schedules as $s ) {
			self::$scheduled_keys[ $s ] = '_wpdp_' . $s;
		}

		/**
		 * meta keys die die intervall-slugs enthalten:
		 * _wpdp_never => 6 // 0 bis 6 uhr gar nicht syncen
		 * _wpdp_hourly => 18 // 6 bis 18 uhr stündlich syncen
		 * _wpdp_twicedaly => 24  18 bis 24 uhr aller 6 stunden syncen
		 */
	}

	/**
	 * init schedules on activation
	 *
	 * @wp-hook wp_doodle_polls_activate
	 * @param WP_Doodle_Polls
	 * @return void
	 */
	public static function init_schedule( $plugin ) {

		# try to syncronize the first occurence to full hours and
		# 00:00 / 12:00 local time for daily/twice_daily events

		# next local time midnight
		$utc_offset = get_option( 'gmt_offset' );
		$timezone   = get_option( 'timezone_string' );
		$php_default_tz = date_default_timezone_get();



	}

}
