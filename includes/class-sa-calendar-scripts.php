<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SA_Calendar_Scripts {
	/**
	 * Initialize scripts
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ) );
	}

	/**
	 * Loads frontend files
	 */
	public static function enqueue_frontend() {
		$l10n = array(
			'NoAvail' => __('No available date and time', 'sa-calendar'),
			'ConfirmAppDel' => __('Do you really want to cancel this appointment and delete all information about it?', 'sa-calendar'),
		);

		wp_register_script( 'sa-calendar', SA_CALENDAR_URL . 'assets/js/sa-calendar.min.js', array( 'jquery' ), false, false );
		wp_localize_script( 'sa-calendar', 'sac_l10n', $l10n );
		wp_enqueue_script( 'sa-calendar' );

		wp_register_style( 'sa-calendar', SA_CALENDAR_URL . 'assets/css/sa-calendar.min.css' );
		wp_enqueue_style( 'sa-calendar' );
	}
	
	/**
	 * Loads admin files
	 */
	public static function enqueue_admin() {
		wp_register_script( 'sa-calendar', SA_CALENDAR_URL . 'assets/js/sa-calendar.min.js', array( 'jquery' ), false, false );
		wp_enqueue_script( 'sa-calendar' );

		wp_register_style( 'sa-calendar', SA_CALENDAR_URL . 'assets/css/sa-calendar.min.css' );
		wp_enqueue_style( 'sa-calendar' );
	}
}

SA_Calendar_Scripts::init();
