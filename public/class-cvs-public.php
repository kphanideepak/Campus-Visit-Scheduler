<?php
/**
 * Public-facing functionality for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Public
 *
 * Handles all public-facing functionality
 */
class CVS_Public {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Register shortcodes
        add_shortcode( 'campus_visit_scheduler', array( $this, 'render_booking_form' ) );
        add_shortcode( 'campus_visit_calendar', array( $this, 'render_calendar' ) );

        // AJAX handlers (both for logged in and non-logged in users)
        add_action( 'wp_ajax_cvs_get_available_slots', array( $this, 'ajax_get_available_slots' ) );
        add_action( 'wp_ajax_nopriv_cvs_get_available_slots', array( $this, 'ajax_get_available_slots' ) );
        add_action( 'wp_ajax_cvs_submit_booking', array( $this, 'ajax_submit_booking' ) );
        add_action( 'wp_ajax_nopriv_cvs_submit_booking', array( $this, 'ajax_submit_booking' ) );
        add_action( 'wp_ajax_cvs_download_ics', array( $this, 'ajax_download_ics' ) );
        add_action( 'wp_ajax_nopriv_cvs_download_ics', array( $this, 'ajax_download_ics' ) );
    }

    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on pages with our shortcode
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ( ! has_shortcode( $post->post_content, 'campus_visit_scheduler' ) && ! has_shortcode( $post->post_content, 'campus_visit_calendar' ) ) ) {
            return;
        }

        wp_enqueue_style(
            'cvs-public',
            CVS_PLUGIN_URL . 'public/css/cvs-public.css',
            array(),
            CVS_VERSION
        );

        wp_enqueue_script(
            'cvs-public',
            CVS_PLUGIN_URL . 'public/js/cvs-public.js',
            array( 'jquery' ),
            CVS_VERSION,
            true
        );

        wp_localize_script( 'cvs-public', 'cvs_public', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cvs_public_nonce' ),
            'strings'  => array(
                'select_date'       => __( 'Please select a date', 'campus-visit-scheduler' ),
                'select_time'       => __( 'Please select a time slot', 'campus-visit-scheduler' ),
                'loading'           => __( 'Loading...', 'campus-visit-scheduler' ),
                'error'             => __( 'An error occurred. Please try again.', 'campus-visit-scheduler' ),
                'no_slots'          => __( 'No time slots available for this date.', 'campus-visit-scheduler' ),
                'booking_success'   => __( 'Your booking has been confirmed!', 'campus-visit-scheduler' ),
                'spots_remaining'   => __( 'spots remaining', 'campus-visit-scheduler' ),
                'spot_remaining'    => __( 'spot remaining', 'campus-visit-scheduler' ),
                'fully_booked'      => __( 'Fully booked', 'campus-visit-scheduler' ),
            ),
            'min_group_size' => (int) get_option( 'cvs_min_group_size', 1 ),
            'max_group_size' => (int) get_option( 'cvs_max_group_size', 6 ),
        ) );
    }

    /**
     * Render the booking form shortcode
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_booking_form( $atts ) {
        // Check if bookings are enabled
        if ( ! CVS_Helpers::bookings_enabled() ) {
            return '<div class="cvs-notice cvs-notice-info">' .
                   esc_html__( 'Tour bookings are currently unavailable. Please check back later.', 'campus-visit-scheduler' ) .
                   '</div>';
        }

        // Get available dates
        $available_dates = CVS_Booking::get_available_dates();

        if ( empty( $available_dates ) ) {
            return '<div class="cvs-notice cvs-notice-info">' .
                   esc_html__( 'No tour dates are currently available. Please check back later.', 'campus-visit-scheduler' ) .
                   '</div>';
        }

        ob_start();
        include CVS_PLUGIN_DIR . 'public/partials/booking-form.php';
        return ob_get_clean();
    }

    /**
     * Render the calendar shortcode
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_calendar( $atts ) {
        $available_dates = CVS_Booking::get_available_dates();

        ob_start();
        include CVS_PLUGIN_DIR . 'public/partials/calendar-display.php';
        return ob_get_clean();
    }

    /**
     * AJAX: Get available time slots for a date
     */
    public function ajax_get_available_slots() {
        check_ajax_referer( 'cvs_public_nonce', 'nonce' );

        $date = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date'] ) : '';

        if ( empty( $date ) ) {
            wp_send_json_error( __( 'Please select a date.', 'campus-visit-scheduler' ) );
        }

        // Validate date format
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
            wp_send_json_error( __( 'Invalid date format.', 'campus-visit-scheduler' ) );
        }

        $available_dates = CVS_Booking::get_available_dates();

        if ( ! isset( $available_dates[ $date ] ) ) {
            wp_send_json_error( __( 'No tours available on this date.', 'campus-visit-scheduler' ) );
        }

        $slots = array();
        foreach ( $available_dates[ $date ]['slots'] as $slot ) {
            $slots[] = array(
                'time'        => $slot['time'],
                'time_display'=> CVS_Helpers::format_time( $slot['time'] ),
                'remaining'   => $slot['remaining'],
                'available'   => $slot['available'],
            );
        }

        wp_send_json_success( $slots );
    }

    /**
     * AJAX: Submit a booking
     */
    public function ajax_submit_booking() {
        check_ajax_referer( 'cvs_public_nonce', 'nonce' );

        // Check if bookings are enabled
        if ( ! CVS_Helpers::bookings_enabled() ) {
            wp_send_json_error( __( 'Bookings are currently disabled.', 'campus-visit-scheduler' ) );
        }

        // Collect form data
        $data = array(
            'tour_date'            => isset( $_POST['tour_date'] ) ? sanitize_text_field( $_POST['tour_date'] ) : '',
            'tour_time'            => isset( $_POST['tour_time'] ) ? sanitize_text_field( $_POST['tour_time'] ) : '',
            'parent_name'          => isset( $_POST['parent_name'] ) ? sanitize_text_field( $_POST['parent_name'] ) : '',
            'email'                => isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '',
            'phone'                => isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '',
            'adults'               => isset( $_POST['adults'] ) ? absint( $_POST['adults'] ) : 1,
            'children'             => isset( $_POST['children'] ) ? absint( $_POST['children'] ) : 0,
            'child_name'           => isset( $_POST['child_name'] ) ? sanitize_text_field( $_POST['child_name'] ) : '',
            'year_level'           => isset( $_POST['year_level'] ) ? sanitize_text_field( $_POST['year_level'] ) : '',
            'special_requirements' => isset( $_POST['special_requirements'] ) ? sanitize_textarea_field( $_POST['special_requirements'] ) : '',
        );

        // Create the booking
        $result = CVS_Booking::create_booking( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        // Return success with booking details for confirmation page
        wp_send_json_success( array(
            'booking_reference' => $result['booking_reference'],
            'tour_date'         => CVS_Helpers::format_date( $result['tour_date'] ),
            'tour_time'         => CVS_Helpers::format_time( $result['tour_time'] ),
            'parent_name'       => $result['parent_name'],
            'group_size'        => $result['adults'] + $result['children'],
            'booking_id'        => $result['id'],
        ) );
    }

    /**
     * AJAX: Download ICS file
     */
    public function ajax_download_ics() {
        $reference = isset( $_GET['reference'] ) ? sanitize_text_field( $_GET['reference'] ) : '';

        if ( empty( $reference ) ) {
            wp_die( esc_html__( 'Invalid booking reference.', 'campus-visit-scheduler' ) );
        }

        $booking = CVS_Booking::get_booking_by_reference( $reference );

        if ( ! $booking ) {
            wp_die( esc_html__( 'Booking not found.', 'campus-visit-scheduler' ) );
        }

        $ics_content = CVS_Helpers::generate_ics( $booking );

        header( 'Content-Type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=campus-tour-' . sanitize_file_name( $reference ) . '.ics' );

        echo $ics_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }
}
