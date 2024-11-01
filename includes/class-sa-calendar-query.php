<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SA_Calendar_Query {
	
	private static $userLastSave = array();

	public static function get_user_schedule( $user_id ) {
		$sched = get_user_meta( $user_id, SA_Calendar::USER_META_NAME, true );
		$onoff = get_user_meta( $user_id, SA_Calendar::USER_ONOFF_META_NAME, true );
		$avail = get_user_meta( $user_id, SA_Calendar::USER_AVAIL_META_NAME, true );
		return new SA_Calendar_Schedule($sched, $onoff, $avail);
	}

	public static function get_user_appointments( $user_id ) {
		global $wpdb;
		$now = SA_Calendar_Utilities::get_user_datetime($user_id, 'Y-m-d').' 0000';
		$query = "SELECT pm.meta_value FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm on pm.post_id = p.ID WHERE p.post_author = $user_id and p.post_type = '".SA_Calendar::APPOINTMENT_POST_TYPE."' and p.post_status = 'publish' and pm.meta_value >= '$now' and pm.meta_key = '".SA_Calendar::APPOINTMENT_DATETIME_META_NAME.'\'';
		$datetimes = $wpdb->get_col($query);

		if ($datetimes) {
			$res = array();
			foreach($datetimes as $datetime) {
				$dt = explode(' ', $datetime);
				$tm = intval($dt[1]);
				if (isset($res[$dt[0]]))
					$res[$dt[0]][$tm] = false;
				else
					$res[$dt[0]] = array($tm => false);
			}
			return $res;
		}
		else
			return null;
	}

	public static function set_user_schedule_raw( $user_id, $val ) {
		$timeZone = null;
		$avail = false;
		unset($val['f']);
		if (isset($val['timezone'])) {
			$timeZone = sanitize_text_field($val['timezone']);
			unset($val['timezone']);
		}
		if (isset($val['avail'])) {
			$avail = true;
			unset($val['avail']);
		}
		$sched = SA_Calendar_Schedule::get_sched_from_raw( $val );
		if ( update_user_meta( $user_id, SA_Calendar::USER_META_NAME, $sched ) == true ) {
			do_action( 'sa_appointment_set', $user_id );
		}
		if ($timeZone != null && SA_Calendar_Settings::instance()->sa_timezone_avail)
			update_user_meta( $user_id, SA_Calendar::USER_TIMEZONE_META_NAME, $timeZone );
		update_user_meta( $user_id, SA_Calendar::USER_AVAIL_META_NAME, $avail );
		update_user_meta( $user_id, SA_Calendar::LAST_SCHEDULE_SAVE_META_NAME, date('Y-m-d H:i:s') );
	}

	public static function set_user_schedule_excal( $user_id, $dt, $tm, $act ) {
		$onoff = get_user_meta( $user_id, SA_Calendar::USER_ONOFF_META_NAME, true );
		$onoff = SA_Calendar_Schedule::append_onoff( $onoff, $dt, $tm, $act, SA_Calendar_Utilities::get_user_datetime($user_id, 'Y-m-d') );
		if ( update_user_meta( $user_id, SA_Calendar::USER_ONOFF_META_NAME, $onoff ) == true ) {
			do_action( 'sa_extcal_set', $user_id, $dt, $tm, $act ? __( 'enable', 'sa-calendar' ) : __( 'disable', 'sa-calendar' ) );
		}
	}

	public static function set_user_appointment( $user_id, $date, $time, $save_info ) {
		if (self::check_user_appointment($user_id, $date, $time))
		{
			$meta_input = array(SA_Calendar::APPOINTMENT_DATETIME_META_NAME => $date.' '.$time);
			foreach($save_info as $name => $value) {
				$meta_input[SA_Calendar::APPOINTMENT_FIELD_META_NAME . mb_strtolower($name)] = $value;
			}

			$user_appointment = array(
					'post_author'	=> $user_id,
					'post_status'	=> 'publish',
					'post_type'		=> SA_Calendar::APPOINTMENT_POST_TYPE
				);
			$app_id = wp_insert_post( $user_appointment, true );
			if ($app_id > 0) {
				foreach($meta_input as $meta_key => $meta_value) {
					add_post_meta($app_id, $meta_key, $meta_value, true);
				}
				do_action( 'sa_appointment_set', $app_id, $user_id, $date, $time, $save_info );
			}
			return $app_id;
		}
		else
			return -1;
	}
	
	public static function delete_user_appointment( $user_id, $app_id ) {
		$canDelete = is_admin() && is_super_admin();
		if ( ! $canDelete ) {
			$author_id = get_post_field('post_author', $app_id);
			$canDelete = !empty($author_id) && intval($author_id) == intval($user_id);
		}
		if ($canDelete) {
			$post_meta = get_post_meta($app_id);
			$dt = isset($post_meta[SA_Calendar::APPOINTMENT_DATETIME_META_NAME]) && count($post_meta[SA_Calendar::APPOINTMENT_DATETIME_META_NAME]) > 0 ? reset($post_meta[SA_Calendar::APPOINTMENT_DATETIME_META_NAME]) : '';
			$dt = explode(' ', $dt);
			$date = count($dt) > 1 ? $dt[0] : '';
			$time = count($dt) > 1 ? $dt[1] : '';
			$fields = SA_Calendar_Settings::instance()->get_fields();
			$save_info = array();
			foreach($fields as $name => $val) {
				$key = SA_Calendar::APPOINTMENT_FIELD_META_NAME . mb_strtolower($name);
				$save_info[$name] = isset($post_meta[$key]) ? reset($post_meta[$key]) : '';
			}
			if ( wp_delete_post( $app_id, true ) !== false ) {
				do_action( 'sa_appointment_cancel', $app_id, $user_id, $date, $time, $save_info );
			}
		}
	}
	
	public static function check_user_appointment($user_id, $date, $time){
		global $wpdb;
		$query = "SELECT p.ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm on pm.post_id = p.ID WHERE p.post_author = $user_id and p.post_type = '".SA_Calendar::APPOINTMENT_POST_TYPE."' and p.post_status = 'publish' and pm.meta_value = '$date $time' and pm.meta_key = '".SA_Calendar::APPOINTMENT_DATETIME_META_NAME.'\' LIMIT 1';
		$res = $wpdb->get_col($query);
		return empty($res);
	}

	public static function get_user_last_save( $user_id ){
		return get_user_meta( $user_id, SA_Calendar::LAST_SCHEDULE_SAVE_META_NAME, true );
	}
}