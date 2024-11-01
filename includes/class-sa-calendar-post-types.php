<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SA_Calendar_Post_Types {
    /**
     * Initialize listing types
     */
    public static function init() {
        self::includes();
    }

    /**
     * Loads listing types
     */
    public static function includes() {
		require_once SA_CALENDAR_DIR . 'includes/post-types/class-sa-calendar-post-type-appointment.php';
    }
}

SA_Calendar_Post_Types::init();