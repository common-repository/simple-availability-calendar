<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SA_Calendar_Shortcodes {
	/**
	 * Initialize shortcodes
	 */
	public static function init() {
		add_shortcode( 'sa_calendar_schedule', array( __CLASS__, 'schedule' ) );
		add_shortcode( 'sa_calendar_calendar', array( __CLASS__, 'calendar' ) );
		add_shortcode( 'sa_calendar_userapps', array( __CLASS__, 'userapps' ) );
		add_shortcode( 'sa_calendar_extcalendar', array( __CLASS__, 'extcalendar' ) );
	}

	/**
	 * Schedule
	 */
	public static function schedule( $atts = array() ) {
		if ( ! is_user_logged_in() ) {
			echo SA_Calendar_Utilities::load( 'misc/not-allowed' );
			return null;
		}
		
		$a = shortcode_atts( array('user_id' => null), $atts );

		$user_id = is_admin() && ctype_digit($a['user_id']) ? intval($a['user_id']) : get_current_user_id();

		$user_timezone = SA_Calendar_Utilities::get_user_timezone($user_id);
		$schedule = SA_Calendar_Query::get_user_schedule($user_id);

		$attrs = array(
			'schedule'		=> $schedule,
			'user_id'		=> $user_id,
			'user_timezone'	=> $user_timezone
		);
		
		SA_Calendar_Logic::$addSchedule = true;

		return SA_Calendar_Utilities::load( 'content-schedule', $attrs );
	}

	/**
	 * Calendar
	 */
	public static function calendar( $atts = array() ) {
		$a = shortcode_atts( array('for_user' => null), $atts );
		$user_id = SA_Calendar_Utilities::get_user_id($a);

		if ( empty($user_id) ) {
			echo SA_Calendar_Utilities::load( 'misc/not-allowed', null );
			return null;
		}

		$utz = self::prepare_calendar_data($user_id, 'today', 'first day of next month');

		$attrs = array(
			'user_id' => $user_id,
			'utz' => $utz,
		);
		
		SA_Calendar_Logic::$addCalendar = true;

		return SA_Calendar_Utilities::load( 'content-calendar', $attrs );
	}

	/**
	 * Extended Calendar
	 */
	public static function extcalendar( $atts = array() ) {
		$a = shortcode_atts( array('for_user' => null), $atts );
		$user_id = SA_Calendar_Utilities::get_user_id($a);
		
		if ( empty($user_id) ) {
			echo SA_Calendar_Utilities::load( 'misc/not-allowed', null );
			return null;
		}

		$utz = self::prepare_calendar_data($user_id, 'today', '+7 days');

		$attrs = array(
			'user_id' => $user_id,
			'utz' => $utz,
			'for_current_user' => SA_Calendar_Utilities::for_current_user($a)
		);
		
		SA_Calendar_Logic::$addExtCalendar = true;

		return SA_Calendar_Utilities::load( 'content-extcalendar', $attrs );
	}
	
	/**
	 * User Appointments
	 */
	public static function userapps( $atts = array() ) {
		$a = shortcode_atts( array('for_user' => null), $atts );
		$user_id = SA_Calendar_Utilities::get_user_id($a);
		
		if ( empty($user_id) || ! SA_Calendar_Settings::instance()->sa_app_avail ) {
			echo SA_Calendar_Utilities::load( 'misc/not-allowed', null );
			return null;
		}

		$showAll = isset($_GET['show_all']);

		$user_timezone = SA_Calendar_Utilities::get_user_timezone($user_id);
		$utz = new DateTimeZone($user_timezone);

		$attrs = array(
			'user_id' => $user_id,
			'utz' => $utz,
			'show_all' => $showAll,
			'for_current_user' => SA_Calendar_Utilities::for_current_user($a)
		);

		return SA_Calendar_Utilities::load( 'content-userapps', $attrs );
	}
	
	private static function prepare_calendar_data($user_id, $from, $to) {
		$schedule = SA_Calendar_Query::get_user_schedule($user_id);

		$appointments = SA_Calendar_Query::get_user_appointments($user_id);
		
		$user_timezone = SA_Calendar_Utilities::get_user_timezone($user_id);
		$utz = new DateTimeZone($user_timezone);
		$dtFrom = new DateTime($from, $utz);
		$dtTo = new DateTime($to, $utz);
		$schedule->add_onoff_2cache($user_id, $appointments, $dtFrom, $dtTo);
		$schedule->add_user_today_2cache($user_id, $dtFrom->diff($dtTo)->days, $utz);

		$schedule->add_def_sched_str_2cache($user_id);

		return $utz;
	}
}

SA_Calendar_Shortcodes::init();
