<?php
/**
 * Email notifications for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Notifications
 *
 * Handles all email notification functionality
 */
class CVS_Notifications {

    /**
     * Send confirmation email to parent
     *
     * @param array $booking Booking data.
     * @return bool True if email sent successfully.
     */
    public static function send_confirmation_email( $booking ) {
        $subject = get_option( 'cvs_confirmation_subject' );
        $body = get_option( 'cvs_confirmation_body' );

        // Use defaults if templates are empty
        if ( empty( $subject ) ) {
            $subject = __( 'Your Campus Tour Booking Confirmation - {booking_reference}', 'campus-visit-scheduler' );
        }
        if ( empty( $body ) ) {
            $body = self::get_default_confirmation_body();
        }

        $subject = self::replace_placeholders( $subject, $booking );
        $body = self::replace_placeholders( $body, $booking );

        return self::send_email( $booking['email'], $subject, $body );
    }

    /**
     * Send cancellation email to parent
     *
     * @param array $booking Booking data.
     * @return bool True if email sent successfully.
     */
    public static function send_cancellation_email( $booking ) {
        $subject = get_option( 'cvs_cancellation_subject' );
        $body = get_option( 'cvs_cancellation_body' );

        // Use defaults if templates are empty
        if ( empty( $subject ) ) {
            $subject = __( 'Campus Tour Booking Cancelled - {booking_reference}', 'campus-visit-scheduler' );
        }
        if ( empty( $body ) ) {
            $body = self::get_default_cancellation_body();
        }

        $subject = self::replace_placeholders( $subject, $booking );
        $body = self::replace_placeholders( $body, $booking );

        return self::send_email( $booking['email'], $subject, $body );
    }

    /**
     * Send notification email to admin recipients
     *
     * @param array $booking Booking data.
     * @return bool True if at least one email sent successfully.
     */
    public static function send_admin_notification( $booking ) {
        $recipients = self::get_admin_recipients( 'notify_new_booking' );

        if ( empty( $recipients ) ) {
            // Fallback to site admin email
            $recipients = array( get_option( 'admin_email' ) );
        }

        $subject = get_option( 'cvs_admin_notification_subject' );
        $body = get_option( 'cvs_admin_notification_body' );

        // Use defaults if templates are empty
        if ( empty( $subject ) ) {
            $subject = __( 'New Campus Tour Booking - {booking_reference}', 'campus-visit-scheduler' );
        }
        if ( empty( $body ) ) {
            $body = self::get_default_admin_body();
        }

        $subject = self::replace_placeholders( $subject, $booking );
        $body = self::replace_placeholders( $body, $booking );

        $success = false;
        foreach ( $recipients as $email ) {
            if ( self::send_email( $email, $subject, $body ) ) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Send cancellation notification to admin recipients
     *
     * @param array $booking Booking data.
     * @return bool True if at least one email sent successfully.
     */
    public static function send_admin_cancellation_notification( $booking ) {
        $recipients = self::get_admin_recipients( 'notify_cancellation' );

        if ( empty( $recipients ) ) {
            return false;
        }

        $subject = sprintf(
            /* translators: %s: booking reference */
            __( 'Booking Cancelled - %s', 'campus-visit-scheduler' ),
            $booking['booking_reference']
        );

        $body = sprintf(
            __( "A campus tour booking has been cancelled.\n\nBooking Details:\n- Reference: %s\n- Parent Name: %s\n- Date: %s\n- Time: %s", 'campus-visit-scheduler' ),
            $booking['booking_reference'],
            $booking['parent_name'],
            CVS_Helpers::format_date( $booking['tour_date'] ),
            CVS_Helpers::format_time( $booking['tour_time'] )
        );

        $success = false;
        foreach ( $recipients as $email ) {
            if ( self::send_email( $email, $subject, $body ) ) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Send reminder email to parent
     *
     * @param array $booking Booking data.
     * @return bool True if email sent successfully.
     */
    public static function send_reminder_email( $booking ) {
        $subject = get_option( 'cvs_reminder_subject' );
        $body = get_option( 'cvs_reminder_body' );

        // Use defaults if templates are empty
        if ( empty( $subject ) ) {
            $subject = __( 'Reminder: Your Campus Tour is Coming Up - {booking_reference}', 'campus-visit-scheduler' );
        }
        if ( empty( $body ) ) {
            $body = self::get_default_reminder_body();
        }

        $subject = self::replace_placeholders( $subject, $booking );
        $body = self::replace_placeholders( $body, $booking );

        return self::send_email( $booking['email'], $subject, $body );
    }

    /**
     * Send email
     *
     * @param string $to Recipient email.
     * @param string $subject Email subject.
     * @param string $body Email body.
     * @return bool True if email sent successfully.
     */
    private static function send_email( $to, $subject, $body ) {
        if ( ! is_email( $to ) ) {
            return false;
        }

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
        );

        return wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * Replace placeholders in email templates
     *
     * @param string $content Content with placeholders.
     * @param array  $booking Booking data.
     * @return string Content with replaced placeholders.
     */
    private static function replace_placeholders( $content, $booking ) {
        $group_size = (int) $booking['adults'] + (int) $booking['children'];

        $placeholders = array(
            '{parent_name}'         => $booking['parent_name'],
            '{tour_date}'           => CVS_Helpers::format_date( $booking['tour_date'] ),
            '{tour_time}'           => CVS_Helpers::format_time( $booking['tour_time'] ),
            '{group_size}'          => $group_size,
            '{booking_reference}'   => $booking['booking_reference'],
            '{email}'               => $booking['email'],
            '{phone}'               => $booking['phone'],
            '{adults}'              => $booking['adults'],
            '{children}'            => $booking['children'],
            '{child_name}'          => $booking['child_name'],
            '{year_level}'          => $booking['year_level'],
            '{special_requirements}'=> ! empty( $booking['special_requirements'] ) ? $booking['special_requirements'] : __( 'None specified', 'campus-visit-scheduler' ),
            '{admin_url}'           => admin_url( 'admin.php?page=cvs-bookings&action=view&id=' . $booking['id'] ),
        );

        return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $content );
    }

    /**
     * Get admin notification recipients
     *
     * @param string $notification_type Type of notification (notify_new_booking or notify_cancellation).
     * @return array Array of email addresses.
     */
    public static function get_admin_recipients( $notification_type = 'notify_new_booking' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_notification_recipients';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $recipients = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT email FROM $table WHERE $notification_type = 1",
                array()
            )
        );

        return $recipients;
    }

    /**
     * Add notification recipient
     *
     * @param string $email Email address.
     * @param bool   $notify_new_booking Notify on new bookings.
     * @param bool   $notify_cancellation Notify on cancellations.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function add_recipient( $email, $notify_new_booking = true, $notify_cancellation = true ) {
        global $wpdb;

        $email = sanitize_email( $email );

        if ( ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', __( 'Invalid email address.', 'campus-visit-scheduler' ) );
        }

        $table = $wpdb->prefix . 'cvs_notification_recipients';

        // Check if already exists
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $exists = $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM $table WHERE email = %s", $email )
        );

        if ( $exists ) {
            // Update existing
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->update(
                $table,
                array(
                    'notify_new_booking'  => $notify_new_booking ? 1 : 0,
                    'notify_cancellation' => $notify_cancellation ? 1 : 0,
                ),
                array( 'id' => $exists ),
                array( '%d', '%d' ),
                array( '%d' )
            );
        } else {
            // Insert new
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert(
                $table,
                array(
                    'email'               => $email,
                    'notify_new_booking'  => $notify_new_booking ? 1 : 0,
                    'notify_cancellation' => $notify_cancellation ? 1 : 0,
                ),
                array( '%s', '%d', '%d' )
            );
        }

        return true;
    }

    /**
     * Remove notification recipient
     *
     * @param int $recipient_id Recipient ID.
     * @return bool True on success.
     */
    public static function remove_recipient( $recipient_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_notification_recipients';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return false !== $wpdb->delete( $table, array( 'id' => $recipient_id ), array( '%d' ) );
    }

    /**
     * Get all notification recipients
     *
     * @return array Array of recipients.
     */
    public static function get_all_recipients() {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_notification_recipients';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results( "SELECT * FROM $table ORDER BY email ASC", ARRAY_A );
    }

    /**
     * Process scheduled reminder emails
     */
    public static function process_reminders() {
        if ( ! get_option( 'cvs_send_reminder', 1 ) ) {
            return;
        }

        $reminder_days = (int) get_option( 'cvs_reminder_days', 2 );
        $reminder_date = gmdate( 'Y-m-d', strtotime( "+{$reminder_days} days" ) );

        global $wpdb;
        $table = $wpdb->prefix . 'cvs_bookings';

        // Get bookings for reminder date that haven't been reminded
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE tour_date = %s AND status = 'confirmed'",
                $reminder_date
            ),
            ARRAY_A
        );

        foreach ( $bookings as $booking ) {
            self::send_reminder_email( $booking );
        }
    }

    /**
     * Send a test email
     *
     * @param string $email_type Type of email (confirmation, cancellation, admin, reminder).
     * @param string $to_email Email address to send test to.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function send_test_email( $email_type, $to_email ) {
        if ( ! is_email( $to_email ) ) {
            return new WP_Error( 'invalid_email', __( 'Invalid email address.', 'campus-visit-scheduler' ) );
        }

        // Create sample booking data for placeholders
        $sample_booking = array(
            'id'                   => 999,
            'booking_reference'    => 'CVS-TEST-001',
            'tour_date'            => gmdate( 'Y-m-d', strtotime( '+7 days' ) ),
            'tour_time'            => '10:00:00',
            'parent_name'          => 'John Smith',
            'email'                => $to_email,
            'phone'                => '0400 123 456',
            'adults'               => 2,
            'children'             => 1,
            'child_name'           => 'Emily Smith',
            'year_level'           => 'Year 7',
            'special_requirements' => 'No special requirements - this is a test booking.',
            'status'               => 'confirmed',
        );

        $result = false;

        switch ( $email_type ) {
            case 'confirmation':
                $subject = get_option( 'cvs_confirmation_subject' );
                $body = get_option( 'cvs_confirmation_body' );
                if ( empty( $subject ) ) {
                    $subject = __( 'Your Campus Tour Booking Confirmation - {booking_reference}', 'campus-visit-scheduler' );
                }
                if ( empty( $body ) ) {
                    $body = self::get_default_confirmation_body();
                }
                break;

            case 'cancellation':
                $subject = get_option( 'cvs_cancellation_subject' );
                $body = get_option( 'cvs_cancellation_body' );
                if ( empty( $subject ) ) {
                    $subject = __( 'Campus Tour Booking Cancelled - {booking_reference}', 'campus-visit-scheduler' );
                }
                if ( empty( $body ) ) {
                    $body = self::get_default_cancellation_body();
                }
                break;

            case 'admin':
                $subject = get_option( 'cvs_admin_notification_subject' );
                $body = get_option( 'cvs_admin_notification_body' );
                if ( empty( $subject ) ) {
                    $subject = __( 'New Campus Tour Booking - {booking_reference}', 'campus-visit-scheduler' );
                }
                if ( empty( $body ) ) {
                    $body = self::get_default_admin_body();
                }
                break;

            case 'reminder':
                $subject = get_option( 'cvs_reminder_subject' );
                $body = get_option( 'cvs_reminder_body' );
                if ( empty( $subject ) ) {
                    $subject = __( 'Reminder: Your Campus Tour is Coming Up - {booking_reference}', 'campus-visit-scheduler' );
                }
                if ( empty( $body ) ) {
                    $body = self::get_default_reminder_body();
                }
                break;

            default:
                return new WP_Error( 'invalid_type', __( 'Invalid email type.', 'campus-visit-scheduler' ) );
        }

        // Add test indicator to subject
        $subject = '[TEST] ' . $subject;

        $subject = self::replace_placeholders( $subject, $sample_booking );
        $body = self::replace_placeholders( $body, $sample_booking );

        // Add test notice to body
        $test_notice = __( "*** THIS IS A TEST EMAIL ***\n\nThe following is a preview of how your email template will look with sample data:\n\n---\n\n", 'campus-visit-scheduler' );
        $body = $test_notice . $body;

        $result = self::send_email( $to_email, $subject, $body );

        if ( $result ) {
            return true;
        }

        return new WP_Error( 'send_failed', __( 'Failed to send test email. Please check your WordPress email configuration.', 'campus-visit-scheduler' ) );
    }

    /**
     * Get default confirmation email body
     */
    private static function get_default_confirmation_body() {
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
     * Get default cancellation email body
     */
    private static function get_default_cancellation_body() {
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
     * Get default admin notification email body
     */
    private static function get_default_admin_body() {
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
     * Get default reminder email body
     */
    private static function get_default_reminder_body() {
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

// Schedule reminder cron if not already scheduled
if ( ! wp_next_scheduled( 'cvs_send_reminders' ) ) {
    wp_schedule_event( time(), 'daily', 'cvs_send_reminders' );
}
add_action( 'cvs_send_reminders', array( 'CVS_Notifications', 'process_reminders' ) );
