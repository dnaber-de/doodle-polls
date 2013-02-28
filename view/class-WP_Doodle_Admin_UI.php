<?php

/**
 * Meta Boxes and stuff
 *
 * @package WP Doodle Polls
 */

class WP_Doodle_Admin_UI {

	/**
	 * i'am the one and only
	 *
	 * @var WP_Doodle_Admin_UI
	 */
	private static $instance = NULL;

	/**
	 * allow others to remove hooks by this instance
	 *
	 * @wp-hook wp_doodle_polls_init
	 * @param WP_Doodle_Polls $plugin (Optional)
	 * @return WP_Doodle_Admin_UI
	 */
	public static function get_instance( $plugin = NULL ) {

		if ( ! self::$instance instanceof self ) {
			$instance = new self( $plugin );
			$instance->init();
			self::$instance = $instance;
		}

		return self::$instance;
	}

	/**
	 *
	 * @param WP_Doodle_Polls $plugin
	 * @return void
	 */
	protected function init( $plugin = NULL ) {

		add_action( 'wp_doodle_polls_meta_box_cb', array( $this, 'add_meta_boxes' ), 10, 1 );
		add_action( 'admin_head', array( $this, 'admin_styles' ) );
	}

	/**
	 * add meta boxes to the post type
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 * @wp-hook wp_doodle_polls_meta_box_cb
	 * @param stdClass $post
	 * @return void
	 */
	public function add_meta_boxes( $post ) {

		# meta box for the Poll ID and Key
		add_meta_box(
			'wpdp-poll-head',
			__( 'Poll Access Settings', 'doodle_polls' ),
			array( $this, 'poll_id_mb' ),
			$post->post_type,
			'normal',   #context
			'core'      #priority
		);

		# meta box with main information about the polls content
		if ( ! empty( $post->post_content ) ) {
			add_meta_box(
				'wpdp-poll-info',
				__( 'Poll Summary', 'doodle_polls' ),
				array( $this, 'poll_info_mb' ),
				$post->post_type,
				'side',
				'core'
			);
		}

		# maintenance
		add_meta_box(
			'wpdp-poll-maintenance',
			__( 'Poll maintenance', 'doodle_polls' ),
			array( $this, 'poll_maintenance_mb' ),
			$post->post_type,
			'side',
			'core'
		);
	}

	/**
	 * Poll Summary Metabox
	 *
	 * @param stdClass $post
	 * @array $meta_box
	 * @return void
	 */
	public function poll_info_mb( $post, $meta_box ) {

		$poll_post = new WP_Doodle_Poll_Post( $post );
		$poll      = new WP_Doodle_Poll( $post->post_content );
		$initiator = $poll->get_initiator();
		$initiator_mail = empty( $initiator[ 'eMailAddress' ] )
			? '%s'
			: '<a href="mailto:' . $initiator[ 'eMailAddress' ] . '">%s</a>';

		$last_activity = new DateTime( $poll->get_last_activity_date() );
		$datetime_format = sprintf(
			get_option( 'date_format' ) . ' %s ' . get_option( 'time_format' ),
			doodle_time_format_escape( _x( 'at', 'Time information: {date} at {time}', 'doodle_polls' ) )
		);

		?>
		<div class="inside">
			<?php
			if ( !empty( $initiator[ 'name' ] ) ) { ?>
				<p>
					<strong><?php _e( 'Initiator', 'doodle_polls' ); ?></strong><br />
					<?php printf( $initiator_mail, $initiator[ 'name' ] ); ?>
				</p>
				<?php
			} ?>
			<p>
				<strong><?php _e( 'Last Activity', 'doodle_polls' ); ?></strong><br />
				<?php echo $last_activity->format( $datetime_format ); ?>
			</p>

			<p>
				<strong><?php _e( 'Number of participants', 'doodle_polls' ); ?></strong><br />
				<?php echo $poll->get_participants_count(); ?>
			</p>

			<?php
			if ( '' != $poll->get_location() ); { ?>
				<p>
					<strong><?php _e( 'Location', 'doodle_polls' ); ?></strong><br />
					<?php echo $poll->get_location(); ?>
				</p>
				<?php
			} ?>

			<div>
				<strong><?php _e( 'Most popular options', 'doodle_polls' ); ?></strong>

				<?php
				echo $this->get_top_options_list( $poll );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * format the popular options output
	 *
	 * @param WP_Doodle_Poll $poll
	 * @return string
	 */
	public function get_top_options_list( $poll ) {

		$options = $poll->get_top_options();
		//print a table instead of ul if there are "maybe"-opinions
		$print_table = FALSE;
		foreach ( $options as $otion => $count ) {
			if ( 0 < $count[ 'maybe' ] ) {
				$print_table = TRUE;
				break;
			}
		}
		//date format
		$date_poll   = $poll->is_datetime_poll() || $poll->is_date_poll();
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$format = $date_format;
		if ( $poll->is_datetime_poll() ) {
			$format = sprintf(
				_x( '%1$s %2$s %3$s', 'Datetime format. 1: date, 2:separator, 3:time', 'doodle_polls' ),
				$date_format,
				doodle_time_format_escape( ', ' ),
				$time_format
			);
		}
		$yes   = __( 'Yes', 'doodle_polls' );
		$maybe = __( 'Maybe', 'doodle_polls' );
		if ( $print_table ) {
			$body = <<<EOT
<table class="dp_top_options">
	<tr>
		<td></td>
		<th scope="col">$yes</th>
		<th scope="col">$maybe</th>
	</tr>
	%s
</table>
EOT;
			$rows = '';
			foreach ( $options as $option => $count ) {
				if ( $date_poll ) {
					$date = new DateTime( $option );
					$option = $date->format( $format );
				}
				$rows .= <<<EOT
<tr>
	<th class="dp_option" scope="row">$option</th>
	<td class="dp_yes">{$count[ 'yes' ]}</td>
	<td class="dp_maybe">{$count[ 'maybe' ]}</td>
</tr>
EOT;
			}
		} else {
			$body = <<<EOT
<ul class="dp_top_options">
	%s
</ul>
EOT;
			$rows = '';
			foreach ( $options as $option => $count ) {
				if ( $date_poll ) {
					$date = new DateTime( $option );
					$option = $date->format( $format );
				}
				$rows .= <<<EOT
<li>
	<span class="dp_option">$option</span>
	<span class="dp_yes">{$count[ 'yes' ]}</span>
</li>
EOT;
			}
		}

		return sprintf( $body, $rows );
	}

	/**
	 * Metabox for Poll ID and Key
	 *
	 * @param stdClass $post
	 * @param array $meta_box
	 * @return void
	 */
	public function poll_id_mb( $post, $meta_box ) {

		$poll_post       = new WP_Doodle_Poll_Post( $post );
		$new             = empty( $poll_post->ID ) ? TRUE : FALSE;
		$poll_key        = $poll_post->key;
		$poll_ID         = $poll_post->ID;
		$consumer_key    = $poll_post->consumer->key;
		$consumer_secret = $poll_post->consumer->secret;
		$post_nonce      = wp_create_nonce( 'doodle_polls' );
		$post_content    = esc_attr( $poll_post->post->post_content ); # pass post-content through for autosave
		?>
		<div class="inside wpdp-clearfix">
			<div class="wpdp-col">
				<h4><?php _e( 'Poll ID', 'doodle_polls' ); ?></h4>
				<input type="hidden" name="wpdp[post_nonce]" value="<?php echo $post_nonce; ?>" />
				<input type="hidden" name="content" value="<?php echo $post_content; ?>" />
				<p class="description">
					<?php printf( __( 'You can find the ID as a part of the URL. In %1$s it\'s %2$s', 'doodle_polls' ), '<code>http://doodle.com/4mqhvg8safemnv2c</code>', '<code>4mqhvg8safemnv2c</code>' ); ?>
				</p>
				<p>
					<label for="wpdp-poll-id"><?php _e( 'Poll ID', 'doodle_polls' ); ?></label>
					<input id="wpdp-poll-id" class="large-text" type="text" name="wpdp[poll_id]" value="<?php echo $poll_ID; ?>"
						<?php if ( ! $new ) { ?> readonly="readonly" title="<?php _e( "Poll ID can't be changed", 'doodle_polls' ); ?>" <?php } ?>
					/>
				</p>
				<p>
					<label for="wpdp-poll-key"><?php _e( 'Poll Key (Optional)', 'doodle_polls' ); ?></label>
					<input id="wpdp-poll-key" class="large-text" type="text" name="wpdp[poll_key]" value="<?php echo $poll_key; ?>" />
				</p>
			</div>
			<div class="wpdp-col" >
				<h4><?php _e( 'Access keys', 'doodle_polls' ); ?></h4>
				<p class="description">
					<?php printf( __( 'You can generate the access keys at %s', 'doodle_polls' ), '<a href="https://doodle.com/mydoodle/consumer/credentials.html">https://doodle.com/mydoodle/consumer/credentials.html</a>' ); ?>
				</p>
				<p>
					<label for="wpdp-customer-key"><?php _e( 'Customer key', 'doodle_polls' ); ?> </label>
					<input id="wpdp-customer-key" class="large-text" type="text" name="wpdp[consumer_key]" value="<?php echo $consumer_key; ?>" />
				</p>
				<p>
					<label for="wpdp-customer-secret"><?php _e( 'Customer secret', 'doodle_polls' ); ?> </label>
					<input id="wpdp-customer-secret" class="large-text" type="text" name="wpdp[consumer_secret]" value="<?php echo $consumer_secret; ?>" />
				</p>
			</div>
		</div>
		<?php
		$poll = new WP_Doodle_Poll( $post->post_content );

		echo '<pre>'; $poll->dump(); '</pre>';
	}

	/**
	 * Metabox for Poll maintenance settings (update interval e.g.)
	 *
	 * @param stdClass $post
	 * @param array $meta_box
	 * @return void
	 */
	public function poll_maintenance_mb( $post, $meta_box ) {

		?>
		<div class="inside">
			<div>
				<label for="wpdp-sync-intervall"><br />
				<select id="wpdp-sync-intervall" name="wpdp[maintenance][sync_intervall]">
					<option></option>
				</select>
			</div>
			<p><?php printf( __( 'Last synchronization: %s', 'doodle_polls' ), '' ); ?></p>

			<p><a href="#" class="button-primary"><?php _e( 'synchronize now', 'doodle_polls' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * enqueue admin styles
	 *
	 * @wp-hook admin_head
	 * @return void
	 */
	public function admin_styles() {

		wp_enqueue_style(
			'wpdp-admin-style',
			WP_Doodle_Polls::$url . '/misc/css/admin.css',
			array(),
			WP_Doodle_Polls::VERSION
		);

	}
}
