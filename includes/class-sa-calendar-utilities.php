<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SA_Calendar_Utilities
 */
class SA_Calendar_Utilities {

	/**
	 * Gets all pages list
	 */
	public static function get_pages() {
		$pages = array();
		$pages[] = __( 'Not set', 'sa-calendar' );

		foreach ( get_pages() as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}

		return $pages;
	}
	
	/**
	 * Convert sa-calendar date and time to user date and time
	 */
	public static function convert_2user_datetime($user_id, $date, $minutes, $with_offset = false) {
		$dtFormat = SA_Calendar_Settings::instance()->sa_date_format;
		$tmFormat = SA_Calendar_Settings::instance()->sa_time_format;
		$user_timezone = self::get_user_timezone($user_id);
		$utz = new DateTimeZone($user_timezone);
		$minutes = intval($minutes);
		$dt = date_create_from_format('Y-m-d H:i', $date . ' ' . str_pad(floor($minutes / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT), $utz);
		return $dt->format($dtFormat . ' ' . $tmFormat . ($with_offset ? ' \G\M\T P' : ''));
	}
	
	/**
	 * Gets user timezone
	 */
	public static function get_user_timezone($user_id) {
		$user_timezone = get_user_meta( $user_id, SA_Calendar::USER_TIMEZONE_META_NAME, true );
		if (empty($user_timezone))
			$user_timezone = 'UTC';
		return $user_timezone;
	}

	/**
	 * Gets user date and time in specified format
	 */
	public static function get_user_datetime($user_id, $format, $time = 'today') {
		$user_timezone = self::get_user_timezone($user_id);
		$date = new DateTime($time, new DateTimeZone($user_timezone));
		return $date->format($format);
	}

	/**
	 * Gets template path
	 */
	public static function locate( $name, $plugin_dir = SA_CALENDAR_DIR ) {
		$template = '';

		// Current theme base dir
		if ( ! empty( $name ) ) {
			$template = locate_template( "{$name}.php" );
		}

		// Child theme
		if ( ! $template && ! empty( $name ) && file_exists( get_stylesheet_directory() . "/templates/{$name}.php" ) ) {
			$template = get_stylesheet_directory() . "/templates/{$name}.php";
		}

		// Original theme
		if ( ! $template && ! empty( $name ) && file_exists( get_template_directory() . "/templates/{$name}.php" ) ) {
			$template = get_template_directory() . "/templates/{$name}.php";
		}

		// Current Plugin
		if ( ! $template && ! empty( $name ) && file_exists( $plugin_dir . "/templates/{$name}.php" ) ) {
			$template = $plugin_dir . "/templates/{$name}.php";
		}

		// Nothing found
		if ( empty( $template ) ) {
			throw new Exception( "Template /templates/{$name}.php in plugin dir {$plugin_dir} not found." );
		}

		return $template;
	}

	/**
	 * Loads template content
	 */
	public static function load( $name, $args = array(), $plugin_dir = SA_CALENDAR_DIR ) {
        if ( is_array( $args ) && count( $args ) > 0 ) {
			extract( $args, EXTR_SKIP );
		}

		$path = self::locate( $name, $plugin_dir );
		ob_start();
		include $path;
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	/**
	 * Check if string is valid date YYYY-MM-DD
	 */
	public static function check_date($str) {
		return (!empty($str) && is_string($str) && date_create_from_format('Y-m-d', $str) !== FALSE);
	}
	
	/**
	 * Check if string is valid time in minutes
	 */
	public static function check_time($str) {
		return (ctype_digit("$str") && intval($str) <= 1440);
	}

	/**
	 * Gets header for calendar
	 */
	public static function get_cal_header($date) {
		return $date->format('F Y');
	}

	/**
	 * Gets header for ext calendar
	 */
	public static function get_extcal_header($date) {
		$frmt = SA_Calendar_Settings::instance()->sa_date_format;
		$nextDT = clone $date;
		$nextDT->modify('+6 days');
		return $date->format($frmt) . ' - ' . $nextDT->format($frmt);
	}

	/**
	 * Convert total minutes to time
	 */
	public static function minutes_2_time($minutes, $fmt) {
		$minutes = intval($minutes);
		return date($fmt, $minutes * 60);
	}
	
	/**
	 * Returns timezone list
	 */
	public static function timezone_list() {
		static $timezones = null;

		if ($timezones === null) {
			$timezones = [];
			$offsets = [];
			$now = new DateTime('today', new DateTimeZone('UTC'));

			foreach (DateTimeZone::listIdentifiers() as $timezone) {
				$now->setTimezone(new DateTimeZone($timezone));
				$offsets[] = $offset = $now->getOffset();
				$timezones[$timezone] = '(' . self::format_GMT_offset($offset) . ') ' . self::format_timezone_name($timezone);
			}

			array_multisort($offsets, $timezones);
		}

		return $timezones;
	}

	private static function format_GMT_offset($offset) {
		$hours = intval($offset / 3600);
		$minutes = abs(intval($offset % 3600 / 60));
		return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
	}

	private static function format_timezone_name($name) {
		$name = str_replace('/', ', ', $name);
		$name = str_replace('_', ' ', $name);
		$name = str_replace('St ', 'St. ', $name);
		return $name;
	}

	/**
	 * Get week day short name by number: 0 - Monday
	 */
	private static function get_week_day_shortname($weekDay) {
		static $weekDayShortNames = null;
		if ($weekDayShortNames === null) {
			$weekDayShortNames = array( 0 => __( 'Mo', 'sa-calendar' ), 1 => __( 'Tu', 'sa-calendar' ), 2 => __( 'We', 'sa-calendar' ), 3 => __( 'Th', 'sa-calendar' ), 4 => __( 'Fr', 'sa-calendar' ), 5 => __( 'Sa', 'sa-calendar' ), 6 => __( 'Su', 'sa-calendar' ));
		}
		return $weekDayShortNames[$weekDay];
	}

	/**
	 * Get week day name by number: 0 - Monday
	 */
	private static function get_week_day_name($weekDay) {
		static $weekDayNames = null;
		if ($weekDayNames === null) {
			$weekDayNames = array();
			$timestamp = strtotime('next Monday');
			for ($i = 0; $i < 7; $i++) {
				$weekDayNames[] = strftime('%A', $timestamp);
				$timestamp = strtotime('+1 day', $timestamp);
			}
		}
		return $weekDayNames[$weekDay];
	}

	/**
	 * Get user id for shortcode
	 */
	public static function get_user_id($attr) {
		if (isset($attr['for_user']) && !empty($attr['for_user'])) {
			if (ctype_digit($attr['for_user']))
				return intval($attr['for_user']);
			else {
				$for_user = mb_strtolower($attr['for_user']);
				if ($for_user == 'current')
					return get_current_user_id();
				else if ($for_user == 'post_author')
					return get_the_author_meta('ID');
				else
					return null;
			}
		}
		else
			return null;
	}
	
	/**
	 * Check user id for shortcode is current
	 */
	public static function for_current_user($attr) {
		if (isset($attr['for_user']) && !empty($attr['for_user']) && mb_strtolower($attr['for_user']) == 'current')
			return true;
		else
			return false;
	}
	
	/**
	 * Redirect to current URL
	 */
	public static function redirect_to_current($query_param = '', $query_value = '') {
		$current_url = self::get_current_page_url();
		if (!empty($query_param)) {
			$current_url = add_query_arg($query_param, $query_value, $current_url);
		}
		wp_redirect( $current_url );
		exit();
	}
	
	/**
	 * Get current page url
	 */
	public static function get_current_page_url() {
		$parts = parse_url( home_url() );
		return "{$parts['scheme']}://{$parts['host']}" . add_query_arg( NULL, NULL );
	}

	/**
	 * Render fields
	 */
	public static function get_fields() {
		$fields = SA_Calendar_Settings::instance()->get_fields();
		$html = '';
		foreach($fields as $name => $field) {
			$html .= self::get_field( 'sa-' . $name, $field['title'], $field['elem'], $field['with_err'] );
		}
		return $html;
	}

	public static function get_field( $name, $header, $input_elem, $with_err_elem ) {
		$err_elem = $with_err_elem ? '<i>' . __( 'Please, fill this field with correct value', 'sa-calendar' ) . '</i>' : '';
		return sprintf('<div><label for="%s">%s</label><br/>%s%s</div>', $name, $header, $input_elem, $err_elem);
	}
	
	public static function print_select( $sh, $indx, $fmt ) {
		$addBtn = '<button type="button" class="sa-btn-add"></button>';
		if (isset($sh->sched[$indx]) && !empty($sh->sched[$indx])) {
			for ($i = 0; $i < count($sh->sched[$indx]); $i++) {
				$from = $sh->sched[$indx][$i][0];
				$to = $sh->sched[$indx][$i][1];
				$txtFrom = self::minutes_2_time($from, $fmt);
				$txtTo = self::minutes_2_time($to, $fmt);
				$ba = $i == count($sh->sched[$indx]) - 1 ? $addBtn : '';
				echo '<div><select name="sas[w'.$indx.'_from][]"><option value="'.$from.'" selected="selected">'.$txtFrom.'</option></select>&nbsp;&mdash;&nbsp;<select name="sas[w'.$indx.'_to][]"><option value="'.$to.'" selected="selected">'.$txtTo.'</option></select><button type="button" class="sa-btn-rem"></button>'.$ba.'</div>';
			}
		}
		else {
			echo '<div>'.$addBtn.'</div>';
		}
	}
}