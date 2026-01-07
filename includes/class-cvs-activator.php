<?php
/**
 * Fired during plugin activation
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Activator
 *
 * Handles all activation tasks including database table creation
 */
class CVS_Activator {

    /**
     * Run activation tasks
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag for admin notice
        set_transient( 'cvs_activation_notice', true, 5 );
    }

    /**
     * Create custom database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Tour schedules table
        $table_schedules = $wpdb->prefix . 'cvs_tour_schedules';
        $sql_schedules = "CREATE TABLE $table_schedules (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            tour_type varchar(20) NOT NULL DEFAULT 'recurring',
            day_of_week tinyint(1) DEFAULT NULL,
            specific_date date DEFAULT NULL,
            time_slot time NOT NULL,
            max_groups int(11) NOT NULL DEFAULT 5,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY tour_type (tour_type),
            KEY day_of_week (day_of_week),
            KEY specific_date (specific_date),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Bookings table
        $table_bookings = $wpdb->prefix . 'cvs_bookings';
        $sql_bookings = "CREATE TABLE $table_bookings (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_reference varchar(20) NOT NULL,
            schedule_id bigint(20) unsigned DEFAULT NULL,
            tour_date date NOT NULL,
            tour_time time NOT NULL,
            parent_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            adults int(11) NOT NULL DEFAULT 1,
            children int(11) NOT NULL DEFAULT 0,
            child_name varchar(255) DEFAULT NULL,
            year_level varchar(50) DEFAULT NULL,
            special_requirements text,
            status varchar(20) NOT NULL DEFAULT 'confirmed',
            admin_notes text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            cancelled_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY booking_reference (booking_reference),
            KEY schedule_id (schedule_id),
            KEY tour_date (tour_date),
            KEY status (status),
            KEY email (email)
        ) $charset_collate;";

        // Blackout dates table
        $table_blackouts = $wpdb->prefix . 'cvs_blackout_dates';
        $sql_blackouts = "CREATE TABLE $table_blackouts (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            blackout_date date NOT NULL,
            reason varchar(255) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY blackout_date (blackout_date)
        ) $charset_collate;";

        // Notification recipients table
        $table_recipients = $wpdb->prefix . 'cvs_notification_recipients';
        $sql_recipients = "CREATE TABLE $table_recipients (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            notify_new_booking tinyint(1) NOT NULL DEFAULT 1,
            notify_cancellation tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_schedules );
        dbDelta( $sql_bookings );
        dbDelta( $sql_blackouts );
        dbDelta( $sql_recipients );

        // Store database version
        update_option( 'cvs_db_version', CVS_VERSION );
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $default_options = array(
            // General settings
            'cvs_advance_booking_days'   => 60,
            'cvs_max_group_size'         => 6,
            'cvs_min_group_size'         => 1,
            'cvs_bookings_enabled'       => 1,

            // Email templates
            'cvs_confirmation_subject'   => __( 'Your Campus Tour Booking Confirmation - {booking_reference}', 'campus-visit-scheduler' ),
            'cvs_confirmation_body'      => self::get_default_confirmation_email(),
            'cvs_cancellation_subject'   => __( 'Campus Tour Booking Cancelled - {booking_reference}', 'campus-visit-scheduler' ),
            'cvs_cancellation_body'      => self::get_default_cancellation_email(),
            'cvs_admin_notification_subject' => __( 'New Campus Tour Booking - {booking_reference}', 'campus-visit-scheduler' ),
            'cvs_admin_notification_body'    => self::get_default_admin_email(),

            // Reminder settings
            'cvs_send_reminder'          => 1,
            'cvs_reminder_days'          => 2,
            'cvs_reminder_subject'       => __( 'Reminder: Your Campus Tour is Coming Up - {booking_reference}', 'campus-visit-scheduler' ),
            'cvs_reminder_body'          => self::get_default_reminder_email(),
        );

        foreach ( $default_options as $option_name => $default_value ) {
            if ( false === get_option( $option_name ) ) {
                update_option( $option_name, $default_value );
            }
        }
    }

    /**
     * Get default confirmation email template
     */
    private static function get_default_confirmation_email() {
        return __( 'Dear {parent_name},

Thank you for booking a campus tour with us.

Booking Details:
- Reference: {booking_reference}
- Date: {tour_date}
- Time: {tour_time}
- Group Size: {group_size}

Please arrive 10 minutes before your scheduled tour time. Report to the main reception area.

If you need to cancel or modify your booking, please contact us as soon as possible.

We look forward to seeing you!

Best regards,
The School Team', 'campus-visit-scheduler' );
    }

    /**
     * Get default cancellation email template
     */
    private static function get_default_cancellation_email() {
        return __( 'Dear {parent_name},

Your campus tour booking has been cancelled.

Cancelled Booking Details:
- Reference: {booking_reference}
- Date: {tour_date}
- Time: {tour_time}

If you did not request this cancellation or would like to rebook, please contact us.

Best regards,
The School Team', 'campus-visit-scheduler' );
    }

    /**
     * Get default admin notification email template
     */
    private static function get_default_admin_email() {
        return __( 'A new campus tour booking has been received.

Booking Details:
- Reference: {booking_reference}
- Parent Name: {parent_name}
- Email: {email}
- Phone: {phone}
- Date: {tour_date}
- Time: {tour_time}
- Group Size: {group_size}

Special Requirements:
{special_requirements}

View booking in admin: {admin_url}', 'campus-visit-scheduler' );
    }

    /**
     * Get default reminder email template
     */
    private static function get_default_reminder_email() {
        return __( 'Dear {parent_name},

This is a friendly reminder that your campus tour is scheduled for:

- Date: {tour_date}
- Time: {tour_time}
- Reference: {booking_reference}
- Group Size: {group_size}

Please arrive 10 minutes before your scheduled tour time. Report to the main reception area.

If you need to cancel or modify your booking, please contact us as soon as possible.

We look forward to seeing you!

Best regards,
The School Team', 'campus-visit-scheduler' );
    }
}
