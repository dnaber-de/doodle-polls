<?php

/**
 * templating
 *
 * filters the content to display the proper table instead of the plain xml data
 *
 */
class WP_Doodle_Frontend {

	/**
	 * instance
	 *
	 * @var WP_Doodle_Frontend
	 */
	private static $instance = NULL;

	/**
	 * get the instance
	 *
	 * @return WP_Doodle_Frontend
	 */
	public static function get_instance() {

		if ( ! self::$instance instanceof self ) {
			$new = new self();
			$new->init();
			self::$instance = $new;
		}

		return self::$instance;
	}

	/**
	 * hook in
	 *
	 * @return void
	 */
	protected function init() {

		add_filter( 'the_content', array( $this, 'filter_content' ) );
	}

	/**
	 * format the poll table
	 *
	 * @wp-hook the_content
	 * @param string $content
	 * @return string
	 */
	public function filter_content( $content ) {

		global $post;
		$id = get_the_ID();
		if ( 'doodle_poll' != $post->post_type )
			return $content;

		$poll = new WP_Doodle_Poll( $post->post_content );
		$tpl  = new WP_Doodle_Template( $poll );
		return $tpl->as_html();
	}

	/**
	 * search in the theme directory for a poll template first
	 *
	 * @param string $file
	 * @return string
	 */
	public function get_poll_template( $file ) {

		$paths = array(
			get_stylesheet_directory(), #child theme
			get_template_directory(), #(parent) theme
			WP_Doodle_Poll::$dir . '/templates' #plugin directory
		);
		foreach ( $paths as $p ) {
			if ( file_exists( $p . '/' . $file ) )
				return $p . '/'. $file;
		}

		return '';
	}

}

