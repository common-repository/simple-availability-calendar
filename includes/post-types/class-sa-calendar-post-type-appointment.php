<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SA_Calendar_Post_Type_Appointment {
    /**
     * Initialize custom post type
     */
    public static function init() {        
        add_action( 'init', array( __CLASS__, 'definition' ), 11 );
    }

    /**
     * Custom post type definition
     */
    public static function definition() {
		$labels = array(
			'name'                  => __( 'Appointments', 'sa-calendar' ),
			'singular_name'         => __( 'Appointment', 'sa-calendar' ),
			'add_new'               => __( 'Add New Appointment', 'sa-calendar' ),
			'add_new_item'          => __( 'Add New Appointment', 'sa-calendar' ),
			'edit_item'             => __( 'Edit Appointment', 'sa-calendar' ),
			'new_item'              => __( 'New Appointment', 'sa-calendar' ),
			'all_items'             => __( 'Appointments', 'sa-calendar' ),
			'view_item'             => __( 'View Appointment', 'sa-calendar' ),
			'search_items'          => __( 'Search Appointment', 'sa-calendar' ),
			'not_found'             => __( 'No Appointments found', 'sa-calendar' ),
			'not_found_in_trash'    => __( 'No Appointments Found in Trash', 'sa-calendar' ),
			'parent_item_colon'     => '',
			'menu_name'             => __( 'Appointments', 'sa-calendar' ),
		);

		register_post_type( SA_Calendar::APPOINTMENT_POST_TYPE,
			array(
				'labels'            => $labels,
				'show_in_menu'	    => false,
				'supports'          => array( null ),
				'public'            => false,
				'has_archive'       => false,
				'show_ui'           => false,
				'categories'        => array(),
				'capabilities'		=> array(
										'create_posts'	=> false,
									),
				'map_meta_cap'		=> true
			)
		);
	}
}

SA_Calendar_Post_Type_Appointment::init();