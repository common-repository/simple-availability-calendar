<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SA_Calendar_Settings {

	private static $instance = null;
	
	public static function instance() {
		if (self::$instance == null) {
			$init = array(
							'sa_time_frame' => 30,
							'sa_date_format' => get_option('date_format'),
							'sa_time_format' =>  get_option('time_format'),
							'sa_first_week_day' => get_option('start_of_week'),
							'sa_posts_per_page' => get_option( 'posts_per_page' ),
							'sa_app_avail' => true,
							'sa_timezone_avail' => true,
							'sa_style_schedule' => '',
							'sa_style_calendar' => '',
							'sa_style_extcalendar' => '',
							'sa_style_userapps' => '',
							'sa_style_modal' => '',
							'sa_text_appheader' => __( 'Book an Appointment', 'sa-calendar' ),
							'sa_text_appsubmit' => __( 'Book', 'sa-calendar' ),
					);
			$init_fields = array(
							'email'		=> array('title' => __( 'E-Mail', 'sa-calendar' ), 'elem' => '<input id="sa-email" name="sa-email" type="email" required="required" maxlength="50" spellcheck="no" autocapitalize="no" autocorrect="no" autocomplete="email" inputmode="email" />', 'with_err' => true),
							'name'		=> array('title' => __( 'Name', 'sa-calendar' ), 'elem' => '<input id="sa-name" name="sa-name" type="text" maxlength="50" />', 'with_err' => false),
							'msg'		=> array('title' => __( 'Message', 'sa-calendar' ), 'elem' => '<textarea id="sa-msg" name="sa-msg" rows="4" maxlength="200"></textarea>', 'with_err' => false),
						);
			$init_fields = apply_filters( 'sa_fields', $init_fields );
			self::$instance = new SA_Calendar_Settings($init, $init_fields);
		}
		return self::$instance;
	}

	private $defVals = array();
	
	private $fields = array();
	
	private function __construct($init, $init_fields) {
		$this->defVals = $init;
		$this->fields = $init_fields;
	}

	protected function __clone() {}

	public function __get($name) {
		if (array_key_exists($name, $this->defVals)) {
			$val = apply_filters( 'sa_settings', $this->defVals[$name], $name );
			if ($name == 'sa_first_week_day') {
				$val = $val == 0 ? 6 : $val - 1;
			}
			return $val;
		}
        return null;
    }
	
	public function get_default($name) {
		if (array_key_exists($name, $this->defVals))
			return $this->defVals[$name];
		return '';
	}
	
	public function get_fields() {
		return $this->fields;
	}
}