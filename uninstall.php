<?php
/**
 * Uninstall script for Campus Visit Scheduler
 *
 * This file runs when the plugin is deleted through the WordPress admin.
 * It removes all plugin data including database tables and options.
 *
 * @package CampusVisitScheduler
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Remove custom database tables
$tables = array(
    $wpdb->prefix . 'cvs_tour_schedules',
    $wpdb->prefix . 'cvs_bookings',
    $wpdb->prefix . 'cvs_blackout_dates',
    $wpdb->prefix . 'cvs_notification_recipients',
);

foreach ( $tables as $table ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
}

// Remove all plugin options
$options = array(
    'cvs_db_version',
    'cvs_advance_booking_days',
    'cvs_max_group_size',
    'cvs_min_group_size',
    'cvs_bookings_enabled',
    'cvs_confirmation_subject',
    'cvs_confirmation_body',
    'cvs_cancellation_subject',
    'cvs_cancellation_body',
    'cvs_admin_notification_subject',
    'cvs_admin_notification_body',
    'cvs_send_reminder',
    'cvs_reminder_days',
    'cvs_reminder_subject',
    'cvs_reminder_body',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Clear any transients
delete_transient( 'cvs_activation_notice' );

// Clear scheduled cron events
wp_clear_scheduled_hook( 'cvs_send_reminders' );
wp_clear_scheduled_hook( 'cvs_cleanup_old_bookings' );
