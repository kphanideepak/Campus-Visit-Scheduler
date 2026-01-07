<?php
/**
 * Booking management for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Booking
 *
 * Handles all booking-related operations
 */
class CVS_Booking {

    /**
     * Get available dates within the booking window
     *
     * @return array Array of available dates with their slots.
     */
    public static function get_available_dates() {
        global $wpdb;

        if ( ! CVS_Helpers::bookings_enabled() ) {
            return array();
        }

        $today = CVS_Helpers::get_today();
        $max_date = CVS_Helpers::get_max_booking_date();
        $schedules_table = $wpdb->prefix . 'cvs_tour_schedules';
        $bookings_table = $wpdb->prefix . 'cvs_bookings';

        // Get all active schedules
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $schedules = $wpdb->get_results(
            "SELECT * FROM $schedules_table WHERE is_active = 1",
            ARRAY_A
        );

        if ( empty( $schedules ) ) {
            return array();
        }

        $available_dates = array();

        // Process recurring schedules
        $recurring_schedules = array_filter( $schedules, function( $s ) {
            return 'recurring' === $s['tour_type'] && null !== $s['day_of_week'];
        });

        // Process one-off schedules
        $oneoff_schedules = array_filter( $schedules, function( $s ) {
            return 'oneoff' === $s['tour_type'] && null !== $s['specific_date'];
        });

        // Generate dates for recurring schedules
        $current_date = new DateTime( $today );
        $end_date = new DateTime( $max_date );

        while ( $current_date <= $end_date ) {
            $date_str = $current_date->format( 'Y-m-d' );
            $day_of_week = (int) $current_date->format( 'w' );

            // Skip blackout dates and exclusion periods
            if ( CVS_Helpers::is_blackout_date( $date_str ) || CVS_Helpers::is_excluded_date( $date_str ) ) {
                $current_date->modify( '+1 day' );
                continue;
            }

            // Check recurring schedules for this day
            foreach ( $recurring_schedules as $schedule ) {
                if ( (int) $schedule['day_of_week'] === $day_of_week ) {
                    $slot_key = $date_str . '_' . $schedule['time_slot'];
                    $booked_count = self::get_booking_count( $date_str, $schedule['time_slot'] );
                    $remaining = (int) $schedule['max_groups'] - $booked_count;

                    if ( ! isset( $available_dates[ $date_str ] ) ) {
                        $available_dates[ $date_str ] = array(
                            'date'  => $date_str,
                            'slots' => array(),
                        );
                    }

                    $available_dates[ $date_str ]['slots'][] = array(
                        'schedule_id' => $schedule['id'],
                        'time'        => $schedule['time_slot'],
                        'max_groups'  => (int) $schedule['max_groups'],
                        'booked'      => $booked_count,
                        'remaining'   => max( 0, $remaining ),
                        'available'   => $remaining > 0,
                    );
                }
            }

            $current_date->modify( '+1 day' );
        }

        // Add one-off schedules
        foreach ( $oneoff_schedules as $schedule ) {
            $date_str = $schedule['specific_date'];

            // Skip if outside booking window
            if ( $date_str < $today || $date_str > $max_date ) {
                continue;
            }

            // Skip blackout dates and exclusion periods
            if ( CVS_Helpers::is_blackout_date( $date_str ) || CVS_Helpers::is_excluded_date( $date_str ) ) {
                continue;
            }

            $booked_count = self::get_booking_count( $date_str, $schedule['time_slot'] );
            $remaining = (int) $schedule['max_groups'] - $booked_count;

            if ( ! isset( $available_dates[ $date_str ] ) ) {
                $available_dates[ $date_str ] = array(
                    'date'  => $date_str,
                    'slots' => array(),
                );
            }

            $available_dates[ $date_str ]['slots'][] = array(
                'schedule_id' => $schedule['id'],
                'time'        => $schedule['time_slot'],
                'max_groups'  => (int) $schedule['max_groups'],
                'booked'      => $booked_count,
                'remaining'   => max( 0, $remaining ),
                'available'   => $remaining > 0,
            );
        }

        // Sort dates and time slots
        ksort( $available_dates );
        foreach ( $available_dates as &$date_data ) {
            usort( $date_data['slots'], function( $a, $b ) {
                return strcmp( $a['time'], $b['time'] );
            });
        }

        return $available_dates;
    }

    /**
     * Get booking count for a specific date and time
     *
     * @param string $date Tour date (Y-m-d).
     * @param string $time Tour time (H:i:s).
     * @return int Number of bookings.
     */
    public static function get_booking_count( $date, $time ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_bookings';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE tour_date = %s AND tour_time = %s AND status = 'confirmed'",
                $date,
                $time
            )
        );

        return (int) $count;
    }

    /**
     * Check if a slot is available for booking
     *
     * @param string $date Tour date (Y-m-d).
     * @param string $time Tour time (H:i:s).
     * @return bool|string True if available, error message if not.
     */
    public static function check_slot_availability( $date, $time ) {
        global $wpdb;

        // Check if bookings are enabled
        if ( ! CVS_Helpers::bookings_enabled() ) {
            return __( 'Bookings are currently disabled.', 'campus-visit-scheduler' );
        }

        // Check date is not in the past
        $today = CVS_Helpers::get_today();
        if ( $date < $today ) {
            return __( 'Cannot book tours in the past.', 'campus-visit-scheduler' );
        }

        // Check if today, ensure time hasn't passed
        if ( $date === $today ) {
            $current_time = CVS_Helpers::get_current_time();
            if ( $time <= $current_time ) {
                return __( 'This time slot has already passed.', 'campus-visit-scheduler' );
            }
        }

        // Check date is within booking window
        $max_date = CVS_Helpers::get_max_booking_date();
        if ( $date > $max_date ) {
            return __( 'This date is outside the advance booking window.', 'campus-visit-scheduler' );
        }

        // Check blackout date
        if ( CVS_Helpers::is_blackout_date( $date ) ) {
            return __( 'Tours are not available on this date.', 'campus-visit-scheduler' );
        }

        // Check exclusion periods
        $exclusion = CVS_Helpers::is_excluded_date( $date );
        if ( $exclusion ) {
            return sprintf(
                /* translators: %s: holiday period name */
                __( 'This date falls within %s and is not available for bookings.', 'campus-visit-scheduler' ),
                $exclusion['period_name']
            );
        }

        // Get schedule for this slot
        $schedule = self::get_schedule_for_slot( $date, $time );
        if ( ! $schedule ) {
            return __( 'This time slot is not available.', 'campus-visit-scheduler' );
        }

        // Check capacity
        $booked_count = self::get_booking_count( $date, $time );
        if ( $booked_count >= (int) $schedule['max_groups'] ) {
            return __( 'This time slot is fully booked.', 'campus-visit-scheduler' );
        }

        return true;
    }

    /**
     * Get schedule configuration for a specific slot
     *
     * @param string $date Tour date (Y-m-d).
     * @param string $time Tour time (H:i:s).
     * @return array|null Schedule data or null if not found.
     */
    public static function get_schedule_for_slot( $date, $time ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_tour_schedules';
        $day_of_week = (int) gmdate( 'w', strtotime( $date ) );

        // Check for one-off schedule first
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $schedule = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE tour_type = 'oneoff' AND specific_date = %s AND time_slot = %s AND is_active = 1",
                $date,
                $time
            ),
            ARRAY_A
        );

        if ( $schedule ) {
            return $schedule;
        }

        // Check for recurring schedule
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $schedule = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE tour_type = 'recurring' AND day_of_week = %d AND time_slot = %s AND is_active = 1",
                $day_of_week,
                $time
            ),
            ARRAY_A
        );

        return $schedule;
    }

    /**
     * Create a new booking
     *
     * @param array $data Booking data.
     * @return array|WP_Error Booking data on success, WP_Error on failure.
     */
    public static function create_booking( $data ) {
        global $wpdb;

        // Validate required fields
        $required_fields = array( 'tour_date', 'tour_time', 'parent_name', 'email', 'phone', 'adults' );
        foreach ( $required_fields as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error(
                    'missing_field',
                    sprintf(
                        /* translators: %s: field name */
                        __( 'Required field missing: %s', 'campus-visit-scheduler' ),
                        $field
                    )
                );
            }
        }

        // Sanitize data
        $tour_date = sanitize_text_field( $data['tour_date'] );
        $tour_time = sanitize_text_field( $data['tour_time'] );
        $parent_name = CVS_Helpers::truncate_text( sanitize_text_field( $data['parent_name'] ), 255 );
        $email = sanitize_email( $data['email'] );
        $phone = CVS_Helpers::sanitize_phone( $data['phone'] );
        $adults = absint( $data['adults'] );
        $children = isset( $data['children'] ) ? absint( $data['children'] ) : 0;
        $child_name = isset( $data['child_name'] ) ? CVS_Helpers::truncate_text( sanitize_text_field( $data['child_name'] ), 255 ) : '';
        $year_level = isset( $data['year_level'] ) ? sanitize_text_field( $data['year_level'] ) : '';
        $special_requirements = isset( $data['special_requirements'] ) ? CVS_Helpers::truncate_text( sanitize_textarea_field( $data['special_requirements'] ), 1000 ) : '';

        // Validate email
        if ( ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'campus-visit-scheduler' ) );
        }

        // Validate phone
        if ( ! CVS_Helpers::validate_phone( $phone ) ) {
            return new WP_Error( 'invalid_phone', __( 'Please enter a valid phone number.', 'campus-visit-scheduler' ) );
        }

        // Validate group size
        $total_size = CVS_Helpers::calculate_group_size( $adults, $children );
        $size_validation = CVS_Helpers::validate_group_size( $total_size );
        if ( true !== $size_validation ) {
            return new WP_Error( 'invalid_group_size', $size_validation );
        }

        // Use transaction for race condition handling
        $wpdb->query( 'START TRANSACTION' );

        // Check slot availability (with lock)
        $availability = self::check_slot_availability( $tour_date, $tour_time );
        if ( true !== $availability ) {
            $wpdb->query( 'ROLLBACK' );
            return new WP_Error( 'slot_unavailable', $availability );
        }

        // Get schedule for this slot
        $schedule = self::get_schedule_for_slot( $tour_date, $tour_time );

        // Generate unique booking reference
        $booking_reference = CVS_Helpers::generate_booking_reference();

        // Insert booking
        $table = $wpdb->prefix . 'cvs_bookings';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert(
            $table,
            array(
                'booking_reference'     => $booking_reference,
                'schedule_id'           => $schedule ? $schedule['id'] : null,
                'tour_date'             => $tour_date,
                'tour_time'             => $tour_time,
                'parent_name'           => $parent_name,
                'email'                 => $email,
                'phone'                 => $phone,
                'adults'                => $adults,
                'children'              => $children,
                'child_name'            => $child_name,
                'year_level'            => $year_level,
                'special_requirements'  => $special_requirements,
                'status'                => 'confirmed',
            ),
            array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s' )
        );

        if ( false === $result ) {
            $wpdb->query( 'ROLLBACK' );
            return new WP_Error( 'db_error', __( 'Failed to create booking. Please try again.', 'campus-visit-scheduler' ) );
        }

        $booking_id = $wpdb->insert_id;

        $wpdb->query( 'COMMIT' );

        // Get the full booking data
        $booking = self::get_booking( $booking_id );

        // Send notifications
        CVS_Notifications::send_confirmation_email( $booking );
        CVS_Notifications::send_admin_notification( $booking );

        return $booking;
    }

    /**
     * Get a single booking by ID
     *
     * @param int $booking_id Booking ID.
     * @return array|null Booking data or null if not found.
     */
    public static function get_booking( $booking_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_bookings';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $booking_id ),
            ARRAY_A
        );
    }

    /**
     * Get a booking by reference number
     *
     * @param string $reference Booking reference.
     * @return array|null Booking data or null if not found.
     */
    public static function get_booking_by_reference( $reference ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_bookings';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE booking_reference = %s", $reference ),
            ARRAY_A
        );
    }

    /**
     * Get bookings with filters
     *
     * @param array $args Query arguments.
     * @return array Array of bookings.
     */
    public static function get_bookings( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status'      => '',
            'date_from'   => '',
            'date_to'     => '',
            'search'      => '',
            'orderby'     => 'tour_date',
            'order'       => 'ASC',
            'per_page'    => 20,
            'page'        => 1,
        );

        $args = wp_parse_args( $args, $defaults );
        $table = $wpdb->prefix . 'cvs_bookings';

        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ( ! empty( $args['date_from'] ) ) {
            $where[] = 'tour_date >= %s';
            $values[] = $args['date_from'];
        }

        if ( ! empty( $args['date_to'] ) ) {
            $where[] = 'tour_date <= %s';
            $values[] = $args['date_to'];
        }

        if ( ! empty( $args['search'] ) ) {
            $search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where[] = '(parent_name LIKE %s OR email LIKE %s OR booking_reference LIKE %s)';
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }

        $where_clause = implode( ' AND ', $where );

        // Sanitize orderby
        $allowed_orderby = array( 'tour_date', 'created_at', 'parent_name', 'status', 'booking_reference' );
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'tour_date';
        $order = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

        $offset = ( (int) $args['page'] - 1 ) * (int) $args['per_page'];

        // Get total count
        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $where_clause", $values ) );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $where_clause" );
        }

        // Get bookings
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $values[] = (int) $args['per_page'];
        $values[] = $offset;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
        $bookings = $wpdb->get_results( $wpdb->prepare( $query, $values ), ARRAY_A );

        return array(
            'bookings' => $bookings,
            'total'    => (int) $total,
            'pages'    => ceil( (int) $total / (int) $args['per_page'] ),
        );
    }

    /**
     * Cancel a booking
     *
     * @param int  $booking_id Booking ID.
     * @param bool $notify Whether to send notification email.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function cancel_booking( $booking_id, $notify = true ) {
        global $wpdb;

        $booking = self::get_booking( $booking_id );

        if ( ! $booking ) {
            return new WP_Error( 'not_found', __( 'Booking not found.', 'campus-visit-scheduler' ) );
        }

        if ( 'cancelled' === $booking['status'] ) {
            return new WP_Error( 'already_cancelled', __( 'This booking is already cancelled.', 'campus-visit-scheduler' ) );
        }

        $table = $wpdb->prefix . 'cvs_bookings';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            $table,
            array(
                'status'       => 'cancelled',
                'cancelled_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $booking_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Failed to cancel booking.', 'campus-visit-scheduler' ) );
        }

        if ( $notify ) {
            $booking['status'] = 'cancelled';
            CVS_Notifications::send_cancellation_email( $booking );
        }

        return true;
    }

    /**
     * Update booking admin notes
     *
     * @param int    $booking_id Booking ID.
     * @param string $notes Admin notes.
     * @return bool True on success.
     */
    public static function update_admin_notes( $booking_id, $notes ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_bookings';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return false !== $wpdb->update(
            $table,
            array( 'admin_notes' => sanitize_textarea_field( $notes ) ),
            array( 'id' => $booking_id ),
            array( '%s' ),
            array( '%d' )
        );
    }

    /**
     * Get bookings for a specific date
     *
     * @param string $date Date in Y-m-d format.
     * @return array Array of bookings.
     */
    public static function get_bookings_for_date( $date ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_bookings';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE tour_date = %s AND status = 'confirmed' ORDER BY tour_time ASC",
                $date
            ),
            ARRAY_A
        );
    }

    /**
     * Get booking statistics
     *
     * @param string $date_from Start date (Y-m-d).
     * @param string $date_to End date (Y-m-d).
     * @return array Statistics data.
     */
    public static function get_statistics( $date_from = '', $date_to = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cvs_bookings';

        $where = '1=1';
        $values = array();

        if ( ! empty( $date_from ) ) {
            $where .= ' AND tour_date >= %s';
            $values[] = $date_from;
        }

        if ( ! empty( $date_to ) ) {
            $where .= ' AND tour_date <= %s';
            $values[] = $date_to;
        }

        // Total bookings
        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $where", $values ) );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $where" );
        }

        // Confirmed bookings
        $confirmed_where = $where . " AND status = 'confirmed'";
        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $confirmed = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $confirmed_where", $values ) );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $confirmed = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $confirmed_where" );
        }

        // Cancelled bookings
        $cancelled_where = $where . " AND status = 'cancelled'";
        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $cancelled = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $cancelled_where", $values ) );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $cancelled = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $cancelled_where" );
        }

        // Average group size
        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $avg_group_size = $wpdb->get_var( $wpdb->prepare( "SELECT AVG(adults + children) FROM $table WHERE $where AND status = 'confirmed'", $values ) );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $avg_group_size = $wpdb->get_var( "SELECT AVG(adults + children) FROM $table WHERE $where AND status = 'confirmed'" );
        }

        // Popular time slots
        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $popular_times = $wpdb->get_results( $wpdb->prepare( "SELECT tour_time, COUNT(*) as count FROM $table WHERE $where AND status = 'confirmed' GROUP BY tour_time ORDER BY count DESC LIMIT 5", $values ), ARRAY_A );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $popular_times = $wpdb->get_results( "SELECT tour_time, COUNT(*) as count FROM $table WHERE $where AND status = 'confirmed' GROUP BY tour_time ORDER BY count DESC LIMIT 5", ARRAY_A );
        }

        return array(
            'total_bookings'     => (int) $total,
            'confirmed_bookings' => (int) $confirmed,
            'cancelled_bookings' => (int) $cancelled,
            'cancellation_rate'  => $total > 0 ? round( ( $cancelled / $total ) * 100, 1 ) : 0,
            'avg_group_size'     => round( (float) $avg_group_size, 1 ),
            'popular_times'      => $popular_times,
        );
    }

    /**
     * Export bookings to CSV
     *
     * @param array $args Filter arguments.
     * @return string CSV content.
     */
    public static function export_to_csv( $args = array() ) {
        $args['per_page'] = 9999;
        $args['page'] = 1;
        $result = self::get_bookings( $args );

        $csv = array();

        // Headers
        $csv[] = array(
            __( 'Reference', 'campus-visit-scheduler' ),
            __( 'Date', 'campus-visit-scheduler' ),
            __( 'Time', 'campus-visit-scheduler' ),
            __( 'Parent Name', 'campus-visit-scheduler' ),
            __( 'Email', 'campus-visit-scheduler' ),
            __( 'Phone', 'campus-visit-scheduler' ),
            __( 'Adults', 'campus-visit-scheduler' ),
            __( 'Children', 'campus-visit-scheduler' ),
            __( 'Child Name', 'campus-visit-scheduler' ),
            __( 'Year Level', 'campus-visit-scheduler' ),
            __( 'Special Requirements', 'campus-visit-scheduler' ),
            __( 'Status', 'campus-visit-scheduler' ),
            __( 'Created', 'campus-visit-scheduler' ),
        );

        foreach ( $result['bookings'] as $booking ) {
            $csv[] = array(
                $booking['booking_reference'],
                $booking['tour_date'],
                $booking['tour_time'],
                $booking['parent_name'],
                $booking['email'],
                $booking['phone'],
                $booking['adults'],
                $booking['children'],
                $booking['child_name'],
                $booking['year_level'],
                $booking['special_requirements'],
                $booking['status'],
                $booking['created_at'],
            );
        }

        $output = '';
        foreach ( $csv as $row ) {
            $output .= '"' . implode( '","', array_map( function( $field ) {
                return str_replace( '"', '""', $field );
            }, $row ) ) . '"' . "\n";
        }

        return $output;
    }
}
