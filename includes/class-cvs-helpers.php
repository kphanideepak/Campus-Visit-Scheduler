<?php
/**
 * Helper functions for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Helpers
 *
 * Provides utility functions used throughout the plugin
 */
class CVS_Helpers {

    /**
     * Generate a unique booking reference
     *
     * @return string Unique booking reference (e.g., CVS-ABC123)
     */
    public static function generate_booking_reference() {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_bookings';

        do {
            $reference = 'CVS-' . strtoupper( wp_generate_password( 6, false, false ) );

            // Check if reference already exists
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE booking_reference = %s",
                    $reference
                )
            );
        } while ( $exists > 0 );

        return $reference;
    }

    /**
     * Format date for display
     *
     * @param string $date Date string.
     * @return string Formatted date.
     */
    public static function format_date( $date ) {
        $timestamp = strtotime( $date );
        return date_i18n( get_option( 'date_format' ), $timestamp );
    }

    /**
     * Format time for display
     *
     * @param string $time Time string.
     * @return string Formatted time.
     */
    public static function format_time( $time ) {
        $timestamp = strtotime( $time );
        return date_i18n( get_option( 'time_format' ), $timestamp );
    }

    /**
     * Format datetime for display
     *
     * @param string $datetime Datetime string.
     * @return string Formatted datetime.
     */
    public static function format_datetime( $datetime ) {
        $timestamp = strtotime( $datetime );
        return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
    }

    /**
     * Get day name from day number (0 = Sunday, 1 = Monday, etc.)
     *
     * @param int $day_number Day number (0-6).
     * @return string Day name.
     */
    public static function get_day_name( $day_number ) {
        $days = array(
            0 => __( 'Sunday', 'campus-visit-scheduler' ),
            1 => __( 'Monday', 'campus-visit-scheduler' ),
            2 => __( 'Tuesday', 'campus-visit-scheduler' ),
            3 => __( 'Wednesday', 'campus-visit-scheduler' ),
            4 => __( 'Thursday', 'campus-visit-scheduler' ),
            5 => __( 'Friday', 'campus-visit-scheduler' ),
            6 => __( 'Saturday', 'campus-visit-scheduler' ),
        );

        return isset( $days[ $day_number ] ) ? $days[ $day_number ] : '';
    }

    /**
     * Get year level options
     *
     * @return array Year level options.
     */
    public static function get_year_levels() {
        return array(
            ''           => __( 'Select year level', 'campus-visit-scheduler' ),
            'prep'       => __( 'Prep / Foundation', 'campus-visit-scheduler' ),
            'year1'      => __( 'Year 1', 'campus-visit-scheduler' ),
            'year2'      => __( 'Year 2', 'campus-visit-scheduler' ),
            'year3'      => __( 'Year 3', 'campus-visit-scheduler' ),
            'year4'      => __( 'Year 4', 'campus-visit-scheduler' ),
            'year5'      => __( 'Year 5', 'campus-visit-scheduler' ),
            'year6'      => __( 'Year 6', 'campus-visit-scheduler' ),
            'year7'      => __( 'Year 7', 'campus-visit-scheduler' ),
            'year8'      => __( 'Year 8', 'campus-visit-scheduler' ),
            'year9'      => __( 'Year 9', 'campus-visit-scheduler' ),
            'year10'     => __( 'Year 10', 'campus-visit-scheduler' ),
            'year11'     => __( 'Year 11', 'campus-visit-scheduler' ),
            'year12'     => __( 'Year 12', 'campus-visit-scheduler' ),
            'undecided'  => __( 'Undecided', 'campus-visit-scheduler' ),
        );
    }

    /**
     * Get booking status options
     *
     * @return array Status options.
     */
    public static function get_booking_statuses() {
        return array(
            'confirmed' => __( 'Confirmed', 'campus-visit-scheduler' ),
            'cancelled' => __( 'Cancelled', 'campus-visit-scheduler' ),
            'completed' => __( 'Completed', 'campus-visit-scheduler' ),
            'no_show'   => __( 'No Show', 'campus-visit-scheduler' ),
        );
    }

    /**
     * Get status badge HTML
     *
     * @param string $status Booking status.
     * @return string HTML badge.
     */
    public static function get_status_badge( $status ) {
        $statuses = self::get_booking_statuses();
        $label = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;

        $class = 'cvs-status-badge cvs-status-' . esc_attr( $status );

        return sprintf(
            '<span class="%s">%s</span>',
            esc_attr( $class ),
            esc_html( $label )
        );
    }

    /**
     * Check if a date is a blackout date
     *
     * @param string $date Date to check (Y-m-d format).
     * @return bool True if blackout date.
     */
    public static function is_blackout_date( $date ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_blackout_dates';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE blackout_date = %s",
                $date
            )
        );

        return $count > 0;
    }

    /**
     * Check if a date falls within an exclusion period
     *
     * @param string $date Date to check (Y-m-d format).
     * @return bool|array False if not excluded, array with period info if excluded.
     */
    public static function is_excluded_date( $date ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_exclusion_periods';
        $check_date = $date;
        $check_year = (int) gmdate( 'Y', strtotime( $date ) );
        $check_month_day = gmdate( 'm-d', strtotime( $date ) );

        // Get all exclusion periods
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $periods = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A );

        if ( empty( $periods ) ) {
            return false;
        }

        foreach ( $periods as $period ) {
            $start_date = $period['start_date'];
            $end_date = $period['end_date'];

            if ( $period['recurring_yearly'] ) {
                // For recurring periods, adjust year to match check date
                $start_month_day = gmdate( 'm-d', strtotime( $start_date ) );
                $end_month_day = gmdate( 'm-d', strtotime( $end_date ) );

                // Check if period spans year boundary (e.g., Dec 20 - Jan 27)
                if ( $start_month_day > $end_month_day ) {
                    // Period spans year boundary
                    // Check if date is in the end-of-year portion (e.g., Dec 20 - Dec 31)
                    $adjusted_start = $check_year . '-' . $start_month_day;
                    $adjusted_end = $check_year . '-12-31';
                    if ( $check_date >= $adjusted_start && $check_date <= $adjusted_end ) {
                        return array(
                            'period_name' => $period['period_name'],
                            'start_date'  => $adjusted_start,
                            'end_date'    => ( $check_year + 1 ) . '-' . $end_month_day,
                        );
                    }

                    // Check if date is in the start-of-year portion (e.g., Jan 1 - Jan 27)
                    $adjusted_start = ( $check_year - 1 ) . '-' . $start_month_day;
                    $adjusted_end = $check_year . '-' . $end_month_day;
                    if ( $check_date >= $check_year . '-01-01' && $check_date <= $adjusted_end ) {
                        return array(
                            'period_name' => $period['period_name'],
                            'start_date'  => $adjusted_start,
                            'end_date'    => $adjusted_end,
                        );
                    }
                } else {
                    // Period within same year
                    $adjusted_start = $check_year . '-' . $start_month_day;
                    $adjusted_end = $check_year . '-' . $end_month_day;

                    // Handle Feb 29 for non-leap years
                    if ( ! checkdate( 2, 29, $check_year ) ) {
                        if ( '02-29' === $start_month_day ) {
                            $adjusted_start = $check_year . '-02-28';
                        }
                        if ( '02-29' === $end_month_day ) {
                            $adjusted_end = $check_year . '-02-28';
                        }
                    }

                    if ( $check_date >= $adjusted_start && $check_date <= $adjusted_end ) {
                        return array(
                            'period_name' => $period['period_name'],
                            'start_date'  => $adjusted_start,
                            'end_date'    => $adjusted_end,
                        );
                    }
                }
            } else {
                // Non-recurring period - direct date comparison
                if ( $check_date >= $start_date && $check_date <= $end_date ) {
                    return array(
                        'period_name' => $period['period_name'],
                        'start_date'  => $start_date,
                        'end_date'    => $end_date,
                    );
                }
            }
        }

        return false;
    }

    /**
     * Check if a date is unavailable (blackout or excluded)
     *
     * @param string $date Date to check (Y-m-d format).
     * @return bool|string False if available, error message if not.
     */
    public static function is_date_unavailable( $date ) {
        // Check blackout dates first
        if ( self::is_blackout_date( $date ) ) {
            return __( 'Tours are not available on this date.', 'campus-visit-scheduler' );
        }

        // Check exclusion periods
        $exclusion = self::is_excluded_date( $date );
        if ( $exclusion ) {
            return sprintf(
                /* translators: %s: holiday period name */
                __( 'This date falls within %s and is not available for bookings.', 'campus-visit-scheduler' ),
                $exclusion['period_name']
            );
        }

        return false;
    }

    /**
     * Get all exclusion periods
     *
     * @return array Array of exclusion periods.
     */
    public static function get_exclusion_periods() {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_exclusion_periods';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results( "SELECT * FROM $table ORDER BY start_date ASC", ARRAY_A );
    }

    /**
     * Get exclusion period by ID
     *
     * @param int $id Period ID.
     * @return array|null Period data or null if not found.
     */
    public static function get_exclusion_period( $id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_exclusion_periods';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ),
            ARRAY_A
        );
    }

    /**
     * Add exclusion period
     *
     * @param array $data Period data.
     * @return int|WP_Error Period ID on success, WP_Error on failure.
     */
    public static function add_exclusion_period( $data ) {
        global $wpdb;

        $period_name = sanitize_text_field( $data['period_name'] );
        $start_date = sanitize_text_field( $data['start_date'] );
        $end_date = sanitize_text_field( $data['end_date'] );
        $recurring_yearly = isset( $data['recurring_yearly'] ) && $data['recurring_yearly'] ? 1 : 0;

        // Validation
        if ( empty( $period_name ) ) {
            return new WP_Error( 'empty_name', __( 'Period name is required.', 'campus-visit-scheduler' ) );
        }

        if ( empty( $start_date ) || empty( $end_date ) ) {
            return new WP_Error( 'empty_dates', __( 'Start and end dates are required.', 'campus-visit-scheduler' ) );
        }

        if ( $start_date > $end_date && ! $recurring_yearly ) {
            return new WP_Error( 'invalid_dates', __( 'Start date must be before or equal to end date.', 'campus-visit-scheduler' ) );
        }

        $table = $wpdb->prefix . 'cvs_exclusion_periods';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert(
            $table,
            array(
                'period_name'      => $period_name,
                'start_date'       => $start_date,
                'end_date'         => $end_date,
                'recurring_yearly' => $recurring_yearly,
            ),
            array( '%s', '%s', '%s', '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Failed to add exclusion period.', 'campus-visit-scheduler' ) );
        }

        return $wpdb->insert_id;
    }

    /**
     * Update exclusion period
     *
     * @param int   $id Period ID.
     * @param array $data Period data.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function update_exclusion_period( $id, $data ) {
        global $wpdb;

        $period_name = sanitize_text_field( $data['period_name'] );
        $start_date = sanitize_text_field( $data['start_date'] );
        $end_date = sanitize_text_field( $data['end_date'] );
        $recurring_yearly = isset( $data['recurring_yearly'] ) && $data['recurring_yearly'] ? 1 : 0;

        // Validation
        if ( empty( $period_name ) ) {
            return new WP_Error( 'empty_name', __( 'Period name is required.', 'campus-visit-scheduler' ) );
        }

        if ( empty( $start_date ) || empty( $end_date ) ) {
            return new WP_Error( 'empty_dates', __( 'Start and end dates are required.', 'campus-visit-scheduler' ) );
        }

        if ( $start_date > $end_date && ! $recurring_yearly ) {
            return new WP_Error( 'invalid_dates', __( 'Start date must be before or equal to end date.', 'campus-visit-scheduler' ) );
        }

        $table = $wpdb->prefix . 'cvs_exclusion_periods';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            $table,
            array(
                'period_name'      => $period_name,
                'start_date'       => $start_date,
                'end_date'         => $end_date,
                'recurring_yearly' => $recurring_yearly,
            ),
            array( 'id' => $id ),
            array( '%s', '%s', '%s', '%d' ),
            array( '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Failed to update exclusion period.', 'campus-visit-scheduler' ) );
        }

        return true;
    }

    /**
     * Delete exclusion period
     *
     * @param int $id Period ID.
     * @return bool True on success.
     */
    public static function delete_exclusion_period( $id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_exclusion_periods';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return false !== $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
    }

    /**
     * Check if exclusion period is currently active
     *
     * @param array $period Period data.
     * @return bool True if currently active.
     */
    public static function is_exclusion_period_active( $period ) {
        $today = self::get_today();
        $check_date = $today;

        if ( $period['recurring_yearly'] ) {
            $check_year = (int) gmdate( 'Y' );
            $start_month_day = gmdate( 'm-d', strtotime( $period['start_date'] ) );
            $end_month_day = gmdate( 'm-d', strtotime( $period['end_date'] ) );

            // Handle year boundary
            if ( $start_month_day > $end_month_day ) {
                $today_month_day = gmdate( 'm-d' );
                if ( $today_month_day >= $start_month_day || $today_month_day <= $end_month_day ) {
                    return true;
                }
            } else {
                $adjusted_start = $check_year . '-' . $start_month_day;
                $adjusted_end = $check_year . '-' . $end_month_day;

                if ( $check_date >= $adjusted_start && $check_date <= $adjusted_end ) {
                    return true;
                }
            }
        } else {
            if ( $check_date >= $period['start_date'] && $check_date <= $period['end_date'] ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if bookings are currently enabled
     *
     * @return bool True if bookings are enabled.
     */
    public static function bookings_enabled() {
        return (bool) get_option( 'cvs_bookings_enabled', 1 );
    }

    /**
     * Get the maximum advance booking date
     *
     * @return string Date in Y-m-d format.
     */
    public static function get_max_booking_date() {
        $advance_days = (int) get_option( 'cvs_advance_booking_days', 60 );
        return gmdate( 'Y-m-d', strtotime( "+{$advance_days} days" ) );
    }

    /**
     * Get today's date in WordPress timezone
     *
     * @return string Date in Y-m-d format.
     */
    public static function get_today() {
        return wp_date( 'Y-m-d' );
    }

    /**
     * Get current time in WordPress timezone
     *
     * @return string Time in H:i:s format.
     */
    public static function get_current_time() {
        return wp_date( 'H:i:s' );
    }

    /**
     * Sanitize phone number
     *
     * @param string $phone Phone number.
     * @return string Sanitized phone number.
     */
    public static function sanitize_phone( $phone ) {
        // Remove all non-numeric characters except + (for international)
        $phone = preg_replace( '/[^0-9+]/', '', $phone );
        return $phone;
    }

    /**
     * Validate phone number format
     *
     * @param string $phone Phone number.
     * @return bool True if valid.
     */
    public static function validate_phone( $phone ) {
        // Allow Australian format and international format
        // Minimum 8 digits, maximum 15
        $phone = self::sanitize_phone( $phone );
        $length = strlen( preg_replace( '/[^0-9]/', '', $phone ) );
        return $length >= 8 && $length <= 15;
    }

    /**
     * Calculate total group size
     *
     * @param int $adults Number of adults.
     * @param int $children Number of children.
     * @return int Total group size.
     */
    public static function calculate_group_size( $adults, $children ) {
        return (int) $adults + (int) $children;
    }

    /**
     * Check if group size is valid
     *
     * @param int $total_size Total group size.
     * @return bool|string True if valid, error message if not.
     */
    public static function validate_group_size( $total_size ) {
        $min = (int) get_option( 'cvs_min_group_size', 1 );
        $max = (int) get_option( 'cvs_max_group_size', 6 );

        if ( $total_size < $min ) {
            return sprintf(
                /* translators: %d: minimum group size */
                __( 'Group size must be at least %d.', 'campus-visit-scheduler' ),
                $min
            );
        }

        if ( $total_size > $max ) {
            return sprintf(
                /* translators: %d: maximum group size */
                __( 'Group size cannot exceed %d people.', 'campus-visit-scheduler' ),
                $max
            );
        }

        return true;
    }

    /**
     * Generate ICS file content for calendar download
     *
     * @param array $booking Booking data.
     * @return string ICS file content.
     */
    public static function generate_ics( $booking ) {
        $site_name = get_bloginfo( 'name' );
        $tour_datetime = $booking['tour_date'] . ' ' . $booking['tour_time'];
        $start_time = gmdate( 'Ymd\THis', strtotime( $tour_datetime ) );
        $end_time = gmdate( 'Ymd\THis', strtotime( $tour_datetime . ' +1 hour' ) );
        $created = gmdate( 'Ymd\THis\Z' );
        $uid = $booking['booking_reference'] . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

        $description = sprintf(
            __( 'Campus Tour Booking\\nReference: %s\\nGroup Size: %d', 'campus-visit-scheduler' ),
            $booking['booking_reference'],
            $booking['adults'] + $booking['children']
        );

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//" . esc_attr( $site_name ) . "//Campus Visit Scheduler//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . esc_attr( $uid ) . "\r\n";
        $ics .= "DTSTAMP:" . $created . "\r\n";
        $ics .= "DTSTART:" . $start_time . "\r\n";
        $ics .= "DTEND:" . $end_time . "\r\n";
        $ics .= "SUMMARY:" . esc_attr( sprintf( __( 'Campus Tour - %s', 'campus-visit-scheduler' ), $site_name ) ) . "\r\n";
        $ics .= "DESCRIPTION:" . esc_attr( $description ) . "\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Truncate text to a maximum length
     *
     * @param string $text Text to truncate.
     * @param int    $max_length Maximum length.
     * @return string Truncated text.
     */
    public static function truncate_text( $text, $max_length = 255 ) {
        if ( strlen( $text ) <= $max_length ) {
            return $text;
        }
        return substr( $text, 0, $max_length );
    }
}
