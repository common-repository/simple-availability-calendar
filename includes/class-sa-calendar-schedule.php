<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SA_Calendar_Schedule {
	const Type_Off = 0;
	const Type_Free = 1;
	const Type_App = 2;

	static $dataCache = array();

	public $sched = array();
	public $onoff = array();
	public $avail = false;

	public function __construct($sched, $onoff, $avail) {
		if (!empty($sched))
			$this->sched = $sched;
		if (!empty($onoff))
			$this->onoff = $onoff;
		if (!empty($avail))
			$this->avail = $avail == 1;
	}

	public static function get_sched_from_raw($rawData) {
		$sched = array();
		for ($i = 0; $i < 7; $i++) {
			$sched[$i] = array();
			if (!empty($rawData) && is_array($rawData)) {
				$fromKey = 'w'.$i.'_from';
				$toKey = 'w'.$i.'_to';
				if (isset($rawData[$fromKey]) && is_array($rawData[$fromKey]) && !empty($rawData[$fromKey]) && isset($rawData[$toKey]) && is_array($rawData[$toKey]) && !empty($rawData[$toKey]) && count($rawData[$fromKey]) == count($rawData[$toKey]))
					for ($j = 0; $j < count($rawData[$fromKey]); $j++) {
						$from = intval($rawData[$fromKey][$j]);
						$to = intval($rawData[$toKey][$j]);
						if (self::check_from_to($from, $to))
							$sched[$i][] = array($from, $to);
					}
			}
		}
		return $sched;
	}

	public static function check_from_to($from, $to) {
		return $from >= 0 && $to >= 0 && $from < $to && $from < 1440 && $to <= 1440;
	}

	public static function append_onoff($onoff, $dt, $tm, $act, $user_datetime_now) {
		if (empty($onoff))
			$onoff = array();
		if (isset($onoff[$dt]))
			$onoff[$dt][$tm] = $act;
		else
			$onoff[$dt] = array($tm => $act);

		foreach (array_keys($onoff) as $key)
			if ($key < $user_datetime_now)
				unset($onoff[$key]);
		return $onoff;
	}
	
	/**
	 * Check if user has any schedule
	 */
	public function has_schedule() {
		foreach($this->sched as $el)
			if (count($el) > 0)
				return true;
		return false;
	}
	
	public function add_onoff_2cache($user_id, $appointments, $dateStart, $dateEnd) {
		if (!isset(SA_Calendar_Schedule::$dataCache[$user_id]))
			SA_Calendar_Schedule::$dataCache[$user_id] = array();
		if (!isset(SA_Calendar_Schedule::$dataCache[$user_id]['onoff']))
			SA_Calendar_Schedule::$dataCache[$user_id]['onoff'] = array();

		$onoff = &SA_Calendar_Schedule::$dataCache[$user_id]['onoff'];

		if ($this->is_avail()) {
			$tmpDate = clone $dateStart;
			while ($tmpDate < $dateEnd) {
				$curDay = $tmpDate->format('Y-m-d');
				$this->fill_onoff($appointments, $curDay, $onoff);
				$tmpDate->modify('+1 day');
			}
		}
		SA_Calendar_Schedule::$dataCache[$user_id]['appointments'] = $appointments;
	}
	
	public function fill_onoff($appointments, $dt, &$onoff) {
		if (!isset($onoff[$dt])) {
			if (isset($this->onoff[$dt]))
				$onoff[$dt] = $this->onoff[$dt];
			if (isset($appointments[$dt])) {
				if (!isset($onoff[$dt]))
					$onoff[$dt] = array();
				$onoff[$dt] = array_replace($onoff[$dt], $appointments[$dt]);
			}
		}
	}
	
	public function is_avail() {
		return $this->has_schedule() && $this->avail;
	}
	
	public function add_user_today_2cache($user_id, $total_days, $utz) {
		if (!isset(SA_Calendar_Schedule::$dataCache[$user_id]))
			SA_Calendar_Schedule::$dataCache[$user_id] = array();
		SA_Calendar_Schedule::$dataCache[$user_id]['today'] = new DateTime('today', $utz);
		if (!isset(SA_Calendar_Schedule::$dataCache[$user_id]['totaldays']) || SA_Calendar_Schedule::$dataCache[$user_id]['totaldays'] < $total_days)
			SA_Calendar_Schedule::$dataCache[$user_id]['totaldays'] = $total_days;
	}
	
	public function add_def_sched_str_2cache($user_id) {
		if (isset(SA_Calendar_Schedule::$dataCache[$user_id]['defSchedStr']))
			return;
		$defSchedStr = array();
		$defSched = array();
		if ($this->is_avail()) {
			$timeFrame = SA_Calendar_Settings::instance()->sa_time_frame;
			for ($weekDay = 0; $weekDay < 7; $weekDay++) {
				if (!isset($this->sched[$weekDay]) || count($this->sched[$weekDay]) == 0) {
					$defSchedStr[] = '[]';
					$defSched[] = array();
					continue;
				}
				$availTimes = array();
				$tmpAr = array();
				usort($this->sched[$weekDay], array('SA_Calendar_Schedule', 'cmp_timespan'));
				foreach($this->sched[$weekDay] as $el) {
					$from = intval($el[0]);
					$to = intval($el[1]);

					if ($this->check_from_to($from, $to)) {
						$availTimes[] = '\''.$from.'-'.$to.'\'';
						while ($from < $to) {
							$tmpAr[$from] = true;
							$from += $timeFrame;
						}
					}
				}
				$defSched[] = $tmpAr;
				$defSchedStr[] = '['.implode(',', $availTimes).']';
			}
		}
		else
			for ($weekDay = 0; $weekDay < 7; $weekDay++) {
				$defSchedStr[] = '[]';
				$defSched[] = array();
			}
		if (!isset(SA_Calendar_Schedule::$dataCache[$user_id]))
			SA_Calendar_Schedule::$dataCache[$user_id] = array();
		SA_Calendar_Schedule::$dataCache[$user_id]['defSchedStr'] = '['.implode(',', $defSchedStr).']';
		SA_Calendar_Schedule::$dataCache[$user_id]['defSched'] = $defSched;
	}
	
	public static function has_times($user_id, $dt, $week_day) {
		$res = array();
		if (isset(SA_Calendar_Schedule::$dataCache[$user_id]['defSched'][$week_day]))
			$res = SA_Calendar_Schedule::$dataCache[$user_id]['defSched'][$week_day];
		if (isset(SA_Calendar_Schedule::$dataCache[$user_id]['onoff'][$dt]))
			$res = array_replace($res, SA_Calendar_Schedule::$dataCache[$user_id]['onoff'][$dt]);
		return in_array(true, $res);
	}
	
	public static function get_times_type($user_id, $dt, $week_day) {
		$res = array();
		if (isset(SA_Calendar_Schedule::$dataCache[$user_id]['defSched'][$week_day]))
			$res = array_fill_keys(array_keys(SA_Calendar_Schedule::$dataCache[$user_id]['defSched'][$week_day]), SA_Calendar_Schedule::Type_Free);
		if (isset(SA_Calendar_Schedule::$dataCache[$user_id]['onoff'][$dt]))
			foreach(SA_Calendar_Schedule::$dataCache[$user_id]['onoff'][$dt] as $tm => $val)
				if ($val == true)
					$res[$tm] = SA_Calendar_Schedule::Type_Free;
				else
					unset($res[$tm]);
		if (isset(SA_Calendar_Schedule::$dataCache[$user_id]['appointments'][$dt]))
			$res = array_replace($res, array_fill_keys(array_keys(SA_Calendar_Schedule::$dataCache[$user_id]['appointments'][$dt]), SA_Calendar_Schedule::Type_App));
		return $res;
	}

	public static function cmp_timespan($a, $b) {
		return $a[0] < $b[0];
	}
}