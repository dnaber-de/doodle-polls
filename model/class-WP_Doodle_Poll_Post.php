<?php
/**
 * Extended Custom Post-Object. Wraps the meta-data and post-content handling.
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Poll_Post {

	/**
	 * meta key for poll id
	 *
	 * @var string
	 */
	public static $poll_id_key = '_wpdp_poll_id';

	/**
	 * meta key for poll data
	 *
	 * @var string
	 */
	public static $poll_meta_key = '_wpdp_poll_meta';

	/**
	 * meta key for last poll activity
	 *
	 * @var string
	 */
	public static $poll_last_activity_key = '_wpdp_last_activity';

	/**
	 * meta key for last synchronization
	 *
	 * @var string
	 */
	public static $poll_last_sync_key = '_wpdp_last_sync';

	/**
	 * meta key for the consumer
	 *
	 * @var string
	 */
	public static $poll_consumer_key = '_wpdp_consumer';

	/**
	 * the doodle Poll ID
	 *
	 * @var string
	 */
	public $ID = '';

	/**
	 * the doodle Poll key
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * the polls consumer
	 *
	 * @var WP_Doodle_Consumer
	 */
	public $consumer = NULL;

	/**
	 * some meta stuff for the poll
	 *
	 * @var array
	 */
	public $meta = array();

	/**
	 * the WP post ID
	 *
	 * @var int
	 */
	public $post_ID = 0;

	/**
	 * WP Post
	 *
	 * @var stdClass
	 */
	public $post = NULL;


	/**
	 * constructor
	 *
	 * @param int|stdClass $post_ID (Optional)
	 * @param string $poll_ID (Optional)
	 */
	public function __construct( $post_ID = 0, $poll_ID = '' ) {

		# get post by poll id
		if ( ! empty( $poll_id ) && empty( $post_ID ) ) {
			#get post by poll ID
			$args = array(
				'post_type'   => 'doodle_poll',
				'meta_key'    => self::$poll_id_key,
				'meta_value ' => $poll_ID,
				'numberposts' => 1
			);
			$post = get_posts( $args );
			if ( current( $post ) ) {
				$post = current( $post );
				$this->setup( $post, $poll_ID );
			}
		} elseif ( ! empty( $post_ID ) && is_numeric( $post_ID ) ) {
			#get post by id
			$post = get_post( $post_ID );
			if ( is_a( $post, 'stdClass' ) )
				$this->setup( $post );
		} elseif ( is_a( $post_ID, 'stdClass' ) ) {
			#setup by post-object
			$post = $post_ID;
			$this->setup( $post );
		}
	}

	/**
	 * setup the properties
	 *
	 * @param stdClass $post
	 * @param string $poll_ID
	 */
	public function setup( $post, $poll_ID = NULL ) {

		$this->post     = $post;
		$this->post_ID  = $post->ID;
		$this->ID       = empty( $poll_ID ) ? $this->get_poll_id() : $poll_ID;
		$this->key      = $this->get_poll_key();
		$this->meta     = $this->get_poll_meta();
		$this->consumer = $this->get_consumer();
	}

	/**
	 * load the poll key or return the current one
	 *
	 * @param int $post_ID (Optional)
	 */
	public function get_poll_key( $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ( ! empty( $this->post_ID ) && empty( $this->key ) ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			$meta = $this->get_poll_meta( $post_ID );

			return isset( $meta[ 'poll_key' ] ) ? $meta[ 'poll_key' ] : '';
		}

		return $this->key;
	}

	/**
	 * load the poll meta or return the current one
	 *
	 * @param int $post_ID (Optional)
	 * @return array
	 */
	public function get_poll_meta( $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ( ! empty( $this->post_ID ) && empty( $this->meta ) ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			$meta = get_post_meta( $id, self::$poll_meta_key, TRUE );

			return is_array( $meta ) ? $meta : array();
		}

		return $this->meta;
	}

	/**
	 * load the poll ID or return the current one
	 *
	 * @param int $post_ID (Optional)
	 */
	public function get_poll_id( $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ( ! empty( $this->post_ID ) && empty( $this->ID ) ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			$poll_ID = get_post_meta( $id, self::$poll_id_key, TRUE );

			return $poll_ID;
		}

		return $this->ID;
	}

	/**
	 * get the last activity
	 *
	 * @param int $post_ID (Optional)
	 * @return string
	 */
	public function get_poll_last_activity( $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ! empty( $this->post_ID ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;

			return get_post_meta( $id, self::$poll_last_activity_key, TRUE );
		}

		return '';
	}

	/**
	 * get the last synchronization date
	 *
	 * @param int $post_ID (Optional)
	 * @return string
	 */
	public function get_poll_last_sync_date( $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ! empty( $this->post_ID ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;

			return get_post_meta( $id, self::$poll_last_sync_key, TRUE );
		}

		return '';
	}

	/**
	 * get the consumer
	 *
	 * @param int $post_ID (Optional)
	 * @return WP_Doodle_Consumer|NULL
	 */
	public function get_consumer( $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ! empty( $this->post_ID ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			$consumer_data = get_post_meta( $id, self::$poll_consumer_key, TRUE );

			return new WP_Doodle_Consumer( $consumer_data );
		}

		return $this->consumer;
	}

	/**
	 * set poll ID (poll can't change)
	 *
	 * @param string $poll_ID
	 * @param string $post_ID (Optional)
	 */
	public function set_poll_id( $poll_ID, $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ! empty( $this->post_ID ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			$current = $this->get_poll_id( $id );
			if ( ! empty( $current ) )
				return;
			add_post_meta( $id, self::$poll_id_key, $poll_ID, TRUE );
			$this->ID = $poll_id;
		}

	}

	/**
	 * set poll key
	 *
	 * @param string $poll_ID
	 * @param string $post_ID (Optional)
	 */
	public function set_poll_key( $poll_key, $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ! empty( $this->post_ID ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			$this->meta[ 'poll_key' ] = $poll_key;
			update_post_meta( $id, self::$poll_meta_key, $this->meta );
		}
	}

	/**
	 * set last activity
	 *
	 * @param string $last_activity
	 * @param string $post_ID (Optional)
	 */
	public function set_poll_last_activity( $last_activity, $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ! empty( $this->post_ID ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			update_post_meta( $id, self::$poll_last_activity_key, $last_activity );
		}
	}

	/**
	 * set last synchronization date
	 *
	 * @param string $last_sync
	 * @param string $post_ID (Optional)
	 */
	public function set_poll_last_sync_date( $last_sync, $post_ID = 0 ) {

		if ( ! empty( $post_ID ) || ! empty( $this->post_ID ) ) {
			$id = empty( $post_ID ) ? $this->post_ID : $post_ID;
			update_post_meta( $id, self::$poll_last_sync_key, $last_sync );
		}
	}

	/**
	 * set post content
	 *
	 * @param string $content
	 */
	public function set_post_content( $content = '' ) {

		$this->post->post_content = $content;
		$this->update_post();
	}

	/**
	 * set the title
	 *
	 * @param string $title
	 */
	public function set_post_title( $title = '' ) {

		$this->post->post_title = $title;
		$this->update_post();
	}

	/**
	 * set the excerpt
	 *
	 * @param string $excerpt
	 */
	public function set_post_excerpt( $excerpt = '' ) {

		$this->post->post_excerpt = $excerpt;
		$this->update_post();
	}

	/**
	 * update post
	 *
	 * @return int (post ID)
	 */
	public function update_post() {

		if ( ! $this->post_ID )
			return;

		#stdClass to array
		$post = get_object_vars( $this->post );

		return wp_update_post( $post );
	}

	/**
	 * set consumer
	 *
	 * @param array|WP_Doodle_Consumer
	 */
	public function set_consumer( $consumer_data ) {

		if ( ! $this->post_ID )
			return;

		if ( is_a( $consumer_data, 'WP_Doodle_Consumer' ) ) {
			$consumer = $consumer_data;
			$consumer_data = $consumer->get_data();
		} else {
			$consumer = new WP_Doodle_Consumer( $consumer_data );
		}
		$this->consumer = $consumer;
		update_post_meta( $this->post_ID, self::$poll_consumer_key, $consumer->get_data() );
	}

	/**
	 * get the content from doodle
	 *
	 * @return string|NULL|FALSE $content;
	 */
	public function get_remote_content() {

		if ( empty( $this->ID )
		  || empty( $this->consumer->key )
		  || empty( $this->consumer->secret )
		)
			return NULL;

		$doodle = new WP_Doodle_API( $this->consumer );
		return $doodle->get_poll( $this->ID );

	}
}
