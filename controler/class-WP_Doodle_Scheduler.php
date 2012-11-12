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

		/**
		 * meta keys die die intervall-slugs enthalten:
		 * _wpdp_never => 6 // 0 bis 6 uhr gar nicht syncen
		 * _wpdp_hourl => 18 // 6 bis 18 uhr stündlich syncen
		 * _wpdp_twicedaly => 24  18 bis 24 uhr aller 6 stunden syncen
		 */

	}

}
