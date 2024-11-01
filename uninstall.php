<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

require_once( dirname(__FILE__) . '/simple-availability-calendar.php' );

// limit user data returned to just the id
$users = get_users( array( 'fields' => 'ID' ) );

// loop through each user
foreach( $users as $user )
{
	// delete the custom user meta in the wp_usermeta table
	delete_user_meta( $user, SA_Calendar::USER_META_NAME );
	delete_user_meta( $user, SA_Calendar::USER_AVAIL_META_NAME );
	delete_user_meta( $user, SA_Calendar::USER_ONOFF_META_NAME );
	delete_user_meta( $user, SA_Calendar::USER_TIMEZONE_META_NAME );
	delete_user_meta( $user, SA_Calendar::LAST_SCHEDULE_SAVE_META_NAME );
}

$posts = get_posts( array( 'post_type' => SA_Calendar::APPOINTMENT_POST_TYPE ) );

foreach( $posts as $post ) 
{
    wp_delete_post( $post->ID, true );
} 