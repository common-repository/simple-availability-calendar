<?php

/**
 *	Plugin Name: Simple Availability Calendar
 *	Plugin URI: http://sa-calendar.website.tk
 *	Description: Manage user`s availability and book the appointments.
 *	Text Domain: sa-calendar
 *	Version: 1.0.0
 *	Author: Andrey Denisov <sacalendarplugin@gmail.com>
 *	License: GPL2
 */
 
 /*  Copyright 2017 Andrey Denisov (email:andreyvdenisov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists( 'SA_Calendar' ) ) {

	final class SA_Calendar {
		const HOME_SITE_URL = '';
		const HOME_SITE_DATA_URL = '/wp-admin/admin-ajax.php';
		
		const USER_META_NAME = 'sa_calendar_schedule';
		const USER_AVAIL_META_NAME = 'sa_calendar_avail';
		const USER_ONOFF_META_NAME = 'sa_calendar_onoff';
		const USER_TIMEZONE_META_NAME = 'sa_calendar_timezone';
		const LAST_SCHEDULE_SAVE_META_NAME = 'sa_calendar_ssave';

		const APPOINTMENT_POST_TYPE = 'sa_calendar_app';
		const APPOINTMENT_DATETIME_META_NAME = 'sa_calendar_appdt';
		const APPOINTMENT_FIELD_META_NAME = 'sa_calendar_app';

		/**
		 * Initialize plugin
		 */
		public function __construct() {
			$this->constants();
			$this->includes();

			add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );
		}

		/**
		 * Defines constants
		 */
		public function constants() {
			define( 'SA_CALENDAR_DIR', plugin_dir_path( __FILE__ ) );
			define( 'SA_CALENDAR_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Include classes
		 */
		public function includes() {
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-settings.php';
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-utilities.php';
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-schedule.php';
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-scripts.php';
			if ( ! class_exists( 'ReallySimpleCaptcha19' ) ) {
				require_once SA_CALENDAR_DIR . 'includes/really-simple-captcha.php';
			}
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-post-types.php';
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-query.php';
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-shortcodes.php';
			require_once SA_CALENDAR_DIR . 'includes/class-sa-calendar-logic.php';

			// Admin
			if ( is_admin() ) {
				require_once SA_CALENDAR_DIR . 'includes/admin/class-sa-calendar-admin-menu.php';
				require_once SA_CALENDAR_DIR . 'includes/admin/class-sa-calendar-admin-updates.php';
			}
		}

		/**
		 * Loads localization files
		 */
		public static function load_plugin_textdomain() {
			$path = plugin_basename( dirname( __FILE__ ) ) . '/languages';
			load_plugin_textdomain( 'sa-calendar', false, $path );
		}
		
		/**
		 * Gets the plugin version
		 */
		private static $version = '';
		public static function get_version() {
			if (empty(self::$version)) {
				$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
				self::$version = $plugin_data['Version'];
			}
			return self::$version;
		}
	}

	new SA_Calendar();
}
