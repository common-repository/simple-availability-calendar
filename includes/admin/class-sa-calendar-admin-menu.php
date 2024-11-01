<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SA_Calendar_Admin_Menu {

	public static function init() {
		add_action( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( __CLASS__, 'menu_reorder' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	public static function menu_reorder( $menu_order ) {
		global $submenu;

		$menu_slugs = array( 'sa-calendar');

		if ( ! empty( $submenu ) && ! empty( $menu_slugs ) && is_array( $menu_slugs ) ) {
			foreach( $menu_slugs as $slug ) {
				if ( ! empty( $submenu[ $slug ] ) ) {
					usort( $submenu[ $slug ], array( __CLASS__, 'sort_alphabet' ) );
				}
			}
		}

		return $menu_order;
	}

	/**
	 * Compare alphabetically
	 */
	public static function sort_alphabet( $a, $b ) {
		return strnatcmp( $a[0], $b[0] );
	}

	/**
	 * Registers admin menu wrapper
	 */
	public static function admin_menu() {
		$scheduleTitle = __( 'Schedule', 'sa-calendar' );
		if (is_super_admin()) {
			$scheduleTitle = __( 'Users schedules', 'sa-calendar' );
		}

		add_menu_page( __( 'SA Calendar', 'sa-calendar' ), __( 'SA Calendar', 'sa-calendar' ), 'edit_posts', 'sa-calendar', null, SA_CALENDAR_URL . 'assets/img/date.png', '100' );
		add_submenu_page( 'sa-calendar', $scheduleTitle, $scheduleTitle, 'edit_posts', 'sa_calendar_schedule', array( __CLASS__, 'render_schedules') );
		if (SA_Calendar_Settings::instance()->sa_app_avail) {
			add_submenu_page( 'sa-calendar', __( 'Appointments', 'sa-calendar' ), __( 'Appointments', 'sa-calendar' ), 'edit_posts', 'sa_calendar_userapps', array( __CLASS__, 'render_userapps') );
		}
		remove_submenu_page( 'sa-calendar', 'sa-calendar' );
	}
	
	public static function render_schedules() {
		echo SA_Calendar_Utilities::load( 'admin/users-schedules', null );
	}
	
	public static function render_userapps() {
		echo SA_Calendar_Utilities::load( 'admin/users-apps', null );
	}
}

SA_Calendar_Admin_Menu::init();