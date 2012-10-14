<?php

/**
 * Custom Post Type creator. Collect all Metabox-Callbacks on the action
 * 'wp_doodle_polls_meta_box_cb' as an hook for the admin-views.
 *
 * @package WP Doodle Polls
 */
class WP_Doodle_Post_Type {

	public static $labels = array();

	public static $args = array();

	/**
	 * inits the post types
	 *
	 * @wp-hook wp_doodle_polls_init
	 * @param WP_Doodle_Polls $plugin
	 * @return void
	 */
	public static function init( $plugin = NULL ) {

		$labels = array(
			'name'               => __( 'Doodle Polls', 'wp_doodle_polls' ),
			'singular_name'      => __( 'Doodle Poll', 'wp_doodle_polls' ),
			'add_new'            => __( 'Add new', 'wp_doodle_polls' ),
			'all_items'          => __( 'All Polls', 'wp_doodle_polls' ),
			'add_new_item'       => __( 'Add new Doodle Poll', 'wp_doodle_polls' ),
			'edit_item'          => __( 'Edit Poll', 'wp_doodle_polls' ),
			'new_item'           => __( 'New Doodle Poll', 'wp_doodle_polls' ),
			'view_item'          => __( 'View Doodle Poll', 'wp_doodle_polls' ),
			'search_items'       => __( 'Search Doodle Polls', 'wp_doodle_polls' ),
			'not_found'          => __( 'No Polls found', 'wp_doodle_polls' ),
			'not_found_in_trash' => __( 'No Polls found in Trash', 'wp_doodle_polls' ),
			'parent_item_colon'  => __( 'Parent Poll', 'wp_doodle_polls' ),
			'menu_name'          => __( 'Doodle Polls', 'wp_doodle_polls' )
		);
		$args = array(
			'label'                => __( 'Doodle Poll', 'wp_doodle_polls' ),
			'labels'               => $labels,
			'description'          => __( 'Local representation of your doodle polls', 'wp_doodle_polls' ),
			'public'               => TRUE,
			'exclude_from_search'  => TRUE,
			'publicly_queryable'   => TRUE,
			'show_ui'              => TRUE,
			'show_in_nav_menus'    => TRUE,
			'show_in_menu'         => TRUE,
			'show_in_admin_bar'    => TRUE,
			'menu_position'        => 25, # below comments
			'menu_icon'            => WP_Doodle_Polls::$url . '/misc/img/post-type-icon-18.png',
			'capability_type'      => 'page',
			#'capabilities'         => array(),
			'map_meta_cap'         => TRUE,
			'hierarchical'         => FALSE,
			'supports'             => array(
				'title',
				'thumbnail',
				'excerpt',
				#'custom-fields',
				#'comments',
				#'editor'
			),
			'register_meta_box_cb' => array( __CLASS__, 'do_metabox_callbacks' ),
			#'taxonomies'           => array(),
			'has_archive'          => FALSE,
			'permalink_epmask'     => EP_PERMALINK,
			'rewrite'              => array(
				'slug'       => 'polls',
				'with_front' => TRUE,
				'feeds'      => FALSE,
				'pages'      => FALSE,
				'ep_mask'    => EP_PERMALINK
			),
			'query_var'            => 'doodle_poll',
			'can_export'           => TRUE
		);

		self::$args = apply_filters( 'wp_doodle_polls_post_type_args', $args );
		register_post_type( 'doodle_poll', self::$args );

		# disable autosave function
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'dequeue_autosave' ) );
	}

	/**
	 * collect all callbacks for metaboxes on this post type
	 *
	 * @wp-hook add_meta_boxes_doodle_poll
	 * @param sdtClass $post
	 * @return void
	 */
	public static function do_metabox_callbacks( $post ) {

		do_action( 'wp_doodle_polls_meta_box_cb', $post );
	}

	/**
	 * dequeue the autosave-script for this post-type
	 *
	 * @wp-hook admin_enqueue_scripts
	 * @return void
	 */
	public static function dequeue_autosave() {

		if ( 'doodle_poll' == get_post_type() )
			wp_dequeue_script( 'autosave' );

	}
}
