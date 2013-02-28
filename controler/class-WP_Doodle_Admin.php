<?php

/**
 * evaluate admin requests on 'save_post' etc.
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Admin {

	/**
	 * i'am the one and only
	 *
	 * @var WP_Doodle_Admin
	 */
	private static $instance = NULL;

	/**
	 * get the instance
	 *
	 * @wp-hook wp_doodle_polls_init
	 * @param WP_Doodle_Polls $plugin (Optional) Just pass it through
	 * @return WP_Doodle_Admin
	 */
	public static function get_instance( $plugin = NULL ) {

		if ( ! self::$instance instanceof self ) {
			$instance = new self();
			$instance->init( $plugin );
			self::$instance = $instance;
		}

		return self::$instance;
	}

	/**
	 * hook everything in
	 *
	 * @param WP_Doodle_Polls $plugin (Optional)
	 * @return void
	 */
	protected function init( $plugin = NULL ) {

		add_action( 'save_post', array( $this, 'handle_poll_access_data' ) );
		# this disables the autosave functionality for our post type
		# @ link http://wordpress.stackexchange.com/questions/5584/possible-to-turn-off-autosave-for-single-custom-post-type
		add_action( 'wp_ajax_nopriv_autosave', array( $this, 'remove_autosave' ) );

	}

	/**
	 * check for autosave requests and verify the nonce
	 *
	 * @return bool
	 */
	protected function verify_request() {

		if ( 'POST' !== $_SERVER[ 'REQUEST_METHOD' ] )
			return FALSE;

		if ( empty( $_POST[ 'post_type' ] ) || 'doodle_poll' !== $_POST[ 'post_type' ] )
			return FALSE;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return FALSE;

		$cap = WP_Doodle_Post_Type::$args[ 'capability_type' ];
		if ( ! current_user_can( 'edit_' . $cap ) )
			return FALSE;

		if ( ! wp_verify_nonce( $_POST[ 'wpdp' ][ 'post_nonce' ], 'doodle_polls' ) )
			return FALSE;

		return TRUE;
	}

	/**
	 * check the poll_id and 'create' a new poll if necessary
	 *
	 * @wp-hook save_post
	 * @param int $post_id
	 * @return void
	 */
	public function handle_poll_access_data( $post_id ) {

		if ( ! $this->verify_request() || empty( $_POST[ 'wpdp' ][ 'poll_id' ] )  )
			return;

		# avoid infinite loops
		remove_action( 'save_post', array( $this, 'handle_poll_id' ) );

		$request  = $_POST[ 'wpdp' ];
		$consumer = array();
		$consumer_changed = FALSE;
		$poll_post = new WP_Doodle_Poll_Post( $post_id );

		if ( ! $poll_post->ID )
			$poll_post->set_poll_id( $request[ 'poll_id' ] );

		if ( ! empty( $request[ 'poll_key' ] ) )
			$poll_post->set_poll_key( $request[ 'poll_key' ] );

		if ( ! empty( $request[ 'consumer_key' ] ) )
			$consumer[ 'key' ] = $request[ 'consumer_key' ];

		if ( ! empty( $request[ 'consumer_secret' ] ) )
			$consumer[ 'secret' ] = $request[ 'consumer_secret' ] ;

		#echo '<pre>'; var_dump( $consumer, $request ); exit;
		if ( ! empty( $poll_post->consumer ) && $consumer !== $poll_post->consumer->get_data( 'array_a' ) )
			$consumer_changed = TRUE;

		if ( ! empty( $consumer ) )
			$poll_post->set_consumer( $consumer );


		# new poll
		if ( empty( $poll_post->post->post_content ) || $consumer_changed )
			$this->remote_update_poll( $post_id, $poll_post );

	}

	/**
	 * remote access to the poll
	 *
	 * @param          int $post_id
	 * @param WP_Poll_Post $poll_post (Optional)
	 * @return void
	 */
	public function remote_update_poll( $post_id, $poll_post = NULL ) {

		if ( ! is_a( $poll_post, 'WP_Doodle_Poll_Post' ) )
			$poll_post = new WP_Doodle_Poll_Post( $post_id );

		$remote = $poll_post->get_remote_content();
		#header( 'Content-Type: text/plain' );
		#var_dump( $remote ); exit;
		if ( FALSE === $remote )
		{
			# sth's wrong with the id/keys
		}
		elseif ( NULL !== $remote )
		{
			#echo '<pre>'; var_dump( $poll_post->post->post_content ); exit;
			# store the xml response as post content
			$poll_post->post->post_content = $remote;
			$poll_post->set_poll_last_sync_date( doodle_time_format( time() ) );
			# set title and description if they are empty
			if ( empty( $poll_post->post->post_title ) || empty( $poll_post->post->post_excerpt ) )
			{
				$poll = new WP_Doodle_Poll( $remote );
				$poll_post->post->post_title   = $poll->get_title();
				$poll_post->post->post_excerpt = $poll->get_description();
			}

			$poll_post->update_post();
			$this->update_comments( $post_id, $poll_post );
		}

	}

	/**
	 * update comments
	 *
	 * @param int $post_id
	 * @param WP_Poll_Post $poll_post
	 * @return void
	 */
	public function update_comments( $post_id, $poll_post ) {

		if ( ! is_a( $poll_post, 'WP_Doodle_Poll_Post' ) )
			$poll_post = new WP_Doodle_Poll_Post( $post_id );

		# @ todo
	}

	/**
	 * removes the autosave hook from wp_ajax_nopriv_autosave
	 *
	 * @return void
	 */
	public function remove_autosave() {

		if ( empty( $_POST[ 'post_type' ] ) || 'doodle_poll' != $_POST[ 'post_type' ] )
			return;

		remove_action( 'wp_ajax_nopriv_autosave', 'wp_ajax_nopriv_autosave', 1 );
	}


}

