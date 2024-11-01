<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SA_Calendar_Logic {
	const nonceAjaxName = 'sa-calendar';
	const nonceScheduleName = 'sa-schedule';
	const nonceUserAppName = 'sa-userapps';
	
	static $addSchedule = false;
	static $addCalendar = false;
	static $addExtCalendar = false;
	
	/**
	* Initialize functionality
	*/
	public static function init() {
		add_action( 'init', array( __CLASS__, 'process_schedule' ), 9999 );
		add_action( 'init', array( __CLASS__, 'process_app_del' ), 9999 );
		add_action( 'wp_ajax_save_excal', array( __CLASS__, 'save_excal_callback') );

		add_action( 'wp_ajax_save_app', array( __CLASS__, 'save_app_callback') );
		add_action( 'wp_ajax_nopriv_save_app', array( __CLASS__, 'save_app_callback') );

		add_action( 'wp_ajax_get_cal', array( __CLASS__, 'get_cal_callback') );
		add_action( 'wp_ajax_nopriv_get_cal', array( __CLASS__, 'get_cal_callback') );
		
		add_action( 'wp_ajax_get_data', array( __CLASS__, 'get_data_callback') );
		add_action( 'wp_ajax_nopriv_get_data', array( __CLASS__, 'get_data_callback') );

		add_action( 'wp_footer', array( __CLASS__, 'footer_callback') );
		add_action( 'admin_footer', array( __CLASS__, 'footer_callback') );
	}

	/**
	* Process user schedule form
	*/
	public static function process_schedule() {
		$superMode = is_admin() && is_super_admin();
		if (! is_user_logged_in() || ! isset( $_POST['sas'] ) || ! is_array( $_POST['sas'] ) || ($superMode && (! isset( $_POST['sas']['user_id'] ) || ! ctype_digit( $_POST['sas']['user_id'] ) ) ) || ! isset( $_POST['sa_sec'] ) || ! wp_verify_nonce( $_POST['sa_sec'], SA_Calendar_Logic::nonceScheduleName ) ) {
			return;
		}

		$user_id = $superMode ? intval($_POST['sas']['user_id']) : get_current_user_id();
		SA_Calendar_Query::set_user_schedule_raw($user_id, $_POST['sas']);
		unset($_POST['sas']);

		$query_param = $superMode ? 'user_id' : '';
		$query_value = $superMode ? $user_id : '';
		SA_Calendar_Utilities::redirect_to_current($query_param, $query_value);
	}

	/**
	* Process user appointment delete
	*/
	public static function process_app_del() {
		$superMode = is_admin() && is_super_admin();
		if (! is_user_logged_in() || ! isset( $_POST['saad'] ) || ! is_array( $_POST['saad'] ) || ! isset( $_POST['saad']['id'] ) || ! ctype_digit($_POST['saad']['id']) || ($superMode && (! isset( $_POST['saad']['user_id'] ) || ! ctype_digit( $_POST['saad']['user_id'] ) ) ) || ! isset( $_POST['sa_sec'] ) || ! wp_verify_nonce( $_POST['sa_sec'], SA_Calendar_Logic::nonceUserAppName ) ) {
			return;
		}

		$user_id = $superMode ? intval($_POST['saad']['user_id']) : get_current_user_id();
		SA_Calendar_Query::delete_user_appointment($user_id, intval($_POST['saad']['id']));
		unset($_POST['saad']);

		SA_Calendar_Utilities::redirect_to_current();
	}

	/**
	* Adds scripts, hidden elements and modal forms
	*/
	public static function footer_callback() {
		$output = '';
		
		if (SA_Calendar_Logic::$addSchedule || SA_Calendar_Logic::$addCalendar || SA_Calendar_Logic::$addExtCalendar) {
			$output .= 'sac_timeframe = ' . SA_Calendar_Settings::instance()->sa_time_frame . ';sac_timefmt = \'' . SA_Calendar_Settings::instance()->sa_time_format . '\';sac_weekstart = ' . SA_Calendar_Settings::instance()->sa_first_week_day . ';';
		}

		if (SA_Calendar_Logic::$addSchedule) {
			$output .= 'jQuery(function() { window.SASHED.init(); });';
		}

		if (SA_Calendar_Logic::$addCalendar || SA_Calendar_Logic::$addExtCalendar) {
			echo SA_Calendar_Utilities::load( 'content-calendar-hid' );

			$sec = wp_create_nonce(SA_Calendar_Logic::nonceAjaxName);
			$defShedVar = array();
			$onOffsVar = array();
			$userForDate = array();
			$userInitDays = array();
			$userMonthNames = array();
			$userWeekNames = array();

			foreach(SA_Calendar_Schedule::$dataCache as $user_id => $val) {
				$onOffs = self::get_onoff_str($val['onoff'], $val['appointments']);

				$onOffsVar[] = $user_id . ':{' . implode(',', $onOffs) . '}';
				$defShedVar[] = $user_id . ':' . $val['defSchedStr'];
				$userForDate[] = $user_id . ':new Date(' . $val['today']->format('Y') . ',' . ($val['today']->format('m') - 1) . ',' . $val['today']->format('j') . ')';
				$userInitDays[] = $user_id . ':' . $val['totaldays'];
				$userMonthNames[] = '\'' . $val['today']->format('Y-m') . '\':\'' . SA_Calendar_Utilities::get_cal_header($val['today']) . '\'';
				$headerText = SA_Calendar_Utilities::get_extcal_header($val['today']);
				$userWeekNames[] = '\'' . $val['today']->format('Y-m-d') . '\':\'' . $headerText . '\'';
			}

			$output .= 'sac_ajax_url = \'' . admin_url('admin-ajax.php') . '\';sac_sec = \'' . $sec . '\';sac_forDate = {'.implode(',', $userForDate).'};sac_initDays = {'.implode(',', $userInitDays).'};sac_monthNames = {'.implode(',', $userMonthNames).'};sac_weekNames = {'.implode(',', $userWeekNames).'};sac_defSched = {'.implode(',', $defShedVar).'};sac_onOffs = {'.implode(',', $onOffsVar).'};jQuery(function() { window.SACAL.init(); });';
		}
		if (!empty($output))
			echo '<script>' . $output . '</script>';
	}
	
	private static function get_onoff_str(&$onoff, &$appointments) {
		$onOffs = array();
		if ($onoff != null)
			foreach($onoff as $dt => $times) {
				$str = array();
				$apps = null;
				if ($appointments != null && isset($appointments[$dt]))
					$apps = $appointments[$dt];
				foreach($times as $time => $isOn) {
					if ($apps == null || !array_key_exists($time, $apps))
						$str[] = $time . ':' . ($isOn ? '1' : '0');
					else
						$str[] = $time . ':2';
				}
				$onOffs[] = '\'' . $dt . '\':{' . implode(',', $str) . '}';
			}
		return $onOffs;
	}

	public static function save_excal_callback() {
		$user_id = get_current_user_id();
		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$tm = isset($_GET['tm']) ? sanitize_text_field($_GET['tm']) : '';
		$act = isset($_GET['act']) ? sanitize_text_field($_GET['act']) : '';
		$sec = isset($_GET['sec']) ? sanitize_text_field($_GET['sec']) : '';
		if ( $user_id > 0 && SA_Calendar_Utilities::check_date($dt) && SA_Calendar_Utilities::check_time($tm) && ($act == 'on' || $act == 'off') && !empty($sec) && wp_verify_nonce($sec, SA_Calendar_Logic::nonceAjaxName)) {
			SA_Calendar_Query::set_user_schedule_excal($user_id, $dt, intval($tm), $act == 'on');
			echo(json_encode( array('status'=>'ok') ));
		}
		else
			echo(json_encode( array('status'=>'error') ));
		wp_die();
	}
	
	public static function save_app_callback() {
		$result = false;
		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$tm = isset($_GET['tm']) ? sanitize_text_field($_GET['tm']) : '';
		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : 0;
		$prefix = isset($_GET['prefix']) ? sanitize_text_field($_GET['prefix']) : '';
		$captcha = isset($_GET['captcha']) ? sanitize_text_field($_GET['captcha']) : '';
		$sec = isset($_GET['sec']) ? sanitize_text_field($_GET['sec']) : '';

		$fields = SA_Calendar_Settings::instance()->get_fields();
		$save_info = array();
		foreach($fields as $name => $field) {
			if (isset($_GET[$name])) {
				$save_info[$name] = sanitize_text_field($_GET[$name]);
			}
		}

		$c = new ReallySimpleCaptcha19();
		$captchaOK = $c->check( $prefix, $captcha );
		$c->remove( $prefix );

		if ($captchaOK && $uid > 0 && SA_Calendar_Utilities::check_date($dt) && SA_Calendar_Utilities::check_time($tm) && !empty($sec) && wp_verify_nonce($sec, SA_Calendar_Logic::nonceAjaxName)) {
			$appid = SA_Calendar_Query::set_user_appointment($uid, $dt, str_pad($tm, 4, '0', STR_PAD_LEFT), $save_info);
			if ($appid <= 0)
				wp_die('', '', array('response' => 500));
			$result = true;
		}

		if ($result)
			echo(json_encode( array('status'=>'ok') ));
		else {
			$resAr = array('status'=>'error');
			if (!$captchaOK)
				self::add_captcha($resAr);
			echo(json_encode( $resAr ));
		}
		wp_die();
	}
	
	public static function get_cal_callback() {
		$result = false;
		$dts = isset($_GET['dts']) ? sanitize_text_field($_GET['dts']) : '';
		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : 0;
		$tp = isset($_GET['tp']) ? sanitize_text_field($_GET['tp']) : '';
		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$resAr = array('status'=>'error');
		if ( !empty($dts) && SA_Calendar_Utilities::check_date($dt) && $uid > 0 && !empty($tp) ) {
			$schedule = SA_Calendar_Query::get_user_schedule($uid);
			if ($schedule->is_avail()) {
				$appointments = SA_Calendar_Query::get_user_appointments($uid);
				$dates = explode(',', $dts);
				$onoff = array();
				foreach($dates as $date)
					$schedule->fill_onoff($appointments, $date, $onoff);
				foreach($onoff as $dt => $times) {
					$apps = null;
					if (isset($appointments[$dt]))
						$apps = $appointments[$dt];
					$resAr[$dt] = array();
					foreach($times as $time => $isOn) {
						if ($apps == null || !array_key_exists($time, $apps))
							$resAr[$dt][$time] = ($isOn ? 1 : 0);
						else
							$resAr[$dt][$time] = 2;
					}
				}
				$isCal = $tp == 'cal' ? true : false;
				$date = date_create_from_format('Y-m-d', $dt);
				if ($isCal)
					$resAr['mn'] = SA_Calendar_Utilities::get_cal_header($date);
				else {
					
					$resAr['wn'] = SA_Calendar_Utilities::get_extcal_header($date);
				}
			}
			$resAr['status'] = 'ok';
		}

		echo(json_encode( $resAr ));
		wp_die();
	}
	
	public static function get_data_callback() {
		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : 0;
		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$tm = isset($_GET['tm']) ? sanitize_text_field($_GET['tm']) : '';
		$dts = isset($_GET['dts']) ? sanitize_text_field($_GET['dts']) : '';
		$tp = isset($_GET['tp']) && ctype_digit($_GET['tp']) ? intval($_GET['tp']) : 0; // 2 - get calendar, 4 - get month name, 8 - get week name, 16 - get captcha, 32 - get user date time
		$resAr = array('status'=>'ok');
		if ($tp > 0) {
			$uidOK = $uid > 0;
			$dtOK = SA_Calendar_Utilities::check_date($dt);
			$tmOK = SA_Calendar_Utilities::check_time($tm);
			$dtsOK = !empty($dts);
			
			if ($uidOK)
				$uid = intval($uid);
			
			if (($tp & 2) != 0) {
				if ($uidOK && $dtsOK && $dtOK) {
					$schedule = SA_Calendar_Query::get_user_schedule($uid);
					$cal = array();
					if ($schedule->is_avail()) {
						$appointments = SA_Calendar_Query::get_user_appointments($uid);
						$dates = explode(',', $dts);
						$onoff = array();
						foreach($dates as $date)
							$schedule->fill_onoff($appointments, $date, $onoff);
						foreach($onoff as $date => $times) {
							$apps = null;
							if (isset($appointments[$date]))
								$apps = $appointments[$date];
							$cal[$date] = array();
							foreach($times as $time => $isOn) {
								if ($apps == null || !array_key_exists($time, $apps))
									$cal[$date][$time] = ($isOn ? 1 : 0);
								else
									$cal[$date][$time] = 2;
							}
						}
					}
					$resAr['cal'] = $cal;
				}
				else
					$resAr['status'] = 'error';
			}

			if (($tp & 4) != 0) {
				if ($dtOK)
					$resAr['mn'] = SA_Calendar_Utilities::get_cal_header(date_create_from_format('Y-m-d', $dt));
				else
					$resAr['status'] = 'error';
			}

			if (($tp & 8) != 0) {
				if ($dtOK)
					$resAr['wn'] = SA_Calendar_Utilities::get_extcal_header(date_create_from_format('Y-m-d', $dt));
				else
					$resAr['status'] = 'error';
			}

			if (($tp & 16) != 0) {
				self::add_captcha($resAr);
			}

			if (($tp & 32) != 0) {
				if ($uidOK && $dtOK && $tmOK)
					$resAr['userDateTime'] = SA_Calendar_Utilities::convert_2user_datetime($uid, $dt, $tm, SA_Calendar_Settings::instance()->sa_timezone_avail);
				else
					$resAr['status'] = 'error';
			}
		}
		else
			$resAr['status'] = 'error';
		
		echo(json_encode( $resAr ));
		wp_die();
	}
	
	public static function add_captcha(&$ar) {
		$captcha = new ReallySimpleCaptcha19();
		$word = $captcha->generate_random_word();
		$prefix = mt_rand();
		$filename = $captcha->generate_image( $prefix, $word );
		$filePath = substr( trailingslashit( $captcha->tmp_dir ) . $filename, strlen( $_SERVER[ 'DOCUMENT_ROOT' ] ) );
		$ar['path'] = $filePath;
		$ar['prefix'] = $prefix;
	}
}

SA_Calendar_Logic::init();
