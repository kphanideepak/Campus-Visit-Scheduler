<?php
/**
 * Admin functionality for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Admin
 *
 * Handles all admin functionality
 */
class CVS_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_notices', array( $this, 'activation_notice' ) );

        // AJAX handlers
        add_action( 'wp_ajax_cvs_add_time_slot', array( $this, 'ajax_add_time_slot' ) );
        add_action( 'wp_ajax_cvs_delete_time_slot', array( $this, 'ajax_delete_time_slot' ) );
        add_action( 'wp_ajax_cvs_add_blackout_date', array( $this, 'ajax_add_blackout_date' ) );
        add_action( 'wp_ajax_cvs_delete_blackout_date', array( $this, 'ajax_delete_blackout_date' ) );
        add_action( 'wp_ajax_cvs_add_recipient', array( $this, 'ajax_add_recipient' ) );
        add_action( 'wp_ajax_cvs_delete_recipient', array( $this, 'ajax_delete_recipient' ) );
        add_action( 'wp_ajax_cvs_cancel_booking', array( $this, 'ajax_cancel_booking' ) );
        add_action( 'wp_ajax_cvs_resend_confirmation', array( $this, 'ajax_resend_confirmation' ) );
        add_action( 'wp_ajax_cvs_save_admin_notes', array( $this, 'ajax_save_admin_notes' ) );
        add_action( 'wp_ajax_cvs_export_bookings', array( $this, 'ajax_export_bookings' ) );
        add_action( 'wp_ajax_cvs_add_exclusion_period', array( $this, 'ajax_add_exclusion_period' ) );
        add_action( 'wp_ajax_cvs_update_exclusion_period', array( $this, 'ajax_update_exclusion_period' ) );
        add_action( 'wp_ajax_cvs_delete_exclusion_period', array( $this, 'ajax_delete_exclusion_period' ) );
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __( 'Campus Visit Scheduler', 'campus-visit-scheduler' ),
            __( 'Campus Visits', 'campus-visit-scheduler' ),
            'manage_options',
            'cvs-bookings',
            array( $this, 'render_bookings_page' ),
            'dashicons-calendar-alt',
            30
        );

        // Bookings submenu
        add_submenu_page(
            'cvs-bookings',
            __( 'Bookings', 'campus-visit-scheduler' ),
            __( 'Bookings', 'campus-visit-scheduler' ),
            'manage_options',
            'cvs-bookings',
            array( $this, 'render_bookings_page' )
        );

        // Calendar view submenu
        add_submenu_page(
            'cvs-bookings',
            __( 'Calendar View', 'campus-visit-scheduler' ),
            __( 'Calendar', 'campus-visit-scheduler' ),
            'manage_options',
            'cvs-calendar',
            array( $this, 'render_calendar_page' )
        );

        // Reports submenu
        add_submenu_page(
            'cvs-bookings',
            __( 'Reports', 'campus-visit-scheduler' ),
            __( 'Reports', 'campus-visit-scheduler' ),
            'manage_options',
            'cvs-reports',
            array( $this, 'render_reports_page' )
        );

        // Settings submenu
        add_submenu_page(
            'cvs-bookings',
            __( 'Settings', 'campus-visit-scheduler' ),
            __( 'Settings', 'campus-visit-scheduler' ),
            'manage_options',
            'cvs-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // General settings
        register_setting( 'cvs_general_settings', 'cvs_advance_booking_days', 'absint' );
        register_setting( 'cvs_general_settings', 'cvs_max_group_size', 'absint' );
        register_setting( 'cvs_general_settings', 'cvs_min_group_size', 'absint' );
        register_setting( 'cvs_general_settings', 'cvs_bookings_enabled', 'absint' );

        // Email settings
        register_setting( 'cvs_email_settings', 'cvs_confirmation_subject', 'sanitize_text_field' );
        register_setting( 'cvs_email_settings', 'cvs_confirmation_body', 'sanitize_textarea_field' );
        register_setting( 'cvs_email_settings', 'cvs_cancellation_subject', 'sanitize_text_field' );
        register_setting( 'cvs_email_settings', 'cvs_cancellation_body', 'sanitize_textarea_field' );
        register_setting( 'cvs_email_settings', 'cvs_admin_notification_subject', 'sanitize_text_field' );
        register_setting( 'cvs_email_settings', 'cvs_admin_notification_body', 'sanitize_textarea_field' );
        register_setting( 'cvs_email_settings', 'cvs_send_reminder', 'absint' );
        register_setting( 'cvs_email_settings', 'cvs_reminder_days', 'absint' );
        register_setting( 'cvs_email_settings', 'cvs_reminder_subject', 'sanitize_text_field' );
        register_setting( 'cvs_email_settings', 'cvs_reminder_body', 'sanitize_textarea_field' );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_scripts( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'cvs-' ) === false && 'toplevel_page_cvs-bookings' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'cvs-admin',
            CVS_PLUGIN_URL . 'admin/css/cvs-admin.css',
            array(),
            CVS_VERSION
        );

        wp_enqueue_script(
            'cvs-admin',
            CVS_PLUGIN_URL . 'admin/js/cvs-admin.js',
            array( 'jquery' ),
            CVS_VERSION,
            true
        );

        wp_localize_script( 'cvs-admin', 'cvs_admin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cvs_admin_nonce' ),
            'strings'  => array(
                'confirm_delete'     => __( 'Are you sure you want to delete this?', 'campus-visit-scheduler' ),
                'confirm_cancel'     => __( 'Are you sure you want to cancel this booking?', 'campus-visit-scheduler' ),
                'error'              => __( 'An error occurred. Please try again.', 'campus-visit-scheduler' ),
                'success'            => __( 'Operation completed successfully.', 'campus-visit-scheduler' ),
                'email_sent'         => __( 'Confirmation email has been resent.', 'campus-visit-scheduler' ),
                'notes_saved'        => __( 'Notes saved.', 'campus-visit-scheduler' ),
            ),
        ) );
    }

    /**
     * Show activation notice
     */
    public function activation_notice() {
        if ( get_transient( 'cvs_activation_notice' ) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: %s: settings page URL */
                        esc_html__( 'Campus Visit Scheduler is now active. Please %s to configure your tour settings.', 'campus-visit-scheduler' ),
                        '<a href="' . esc_url( admin_url( 'admin.php?page=cvs-settings' ) ) . '">' . esc_html__( 'visit the settings page', 'campus-visit-scheduler' ) . '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
            delete_transient( 'cvs_activation_notice' );
        }
    }

    /**
     * Render bookings list page
     */
    public function render_bookings_page() {
        // Check for view action
        if ( isset( $_GET['action'] ) && 'view' === $_GET['action'] && isset( $_GET['id'] ) ) {
            $this->render_booking_detail_page();
            return;
        }

        include CVS_PLUGIN_DIR . 'admin/partials/bookings-list.php';
    }

    /**
     * Render booking detail page
     */
    public function render_booking_detail_page() {
        include CVS_PLUGIN_DIR . 'admin/partials/booking-detail.php';
    }

    /**
     * Render calendar page
     */
    public function render_calendar_page() {
        include CVS_PLUGIN_DIR . 'admin/partials/calendar-view.php';
    }

    /**
     * Render reports page
     */
    public function render_reports_page() {
        include CVS_PLUGIN_DIR . 'admin/partials/reports-page.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        include CVS_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    /**
     * AJAX: Add time slot
     */
    public function ajax_add_time_slot() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cvs_tour_schedules';

        $tour_type = sanitize_text_field( $_POST['tour_type'] );
        $time_slot = sanitize_text_field( $_POST['time_slot'] );
        $max_groups = absint( $_POST['max_groups'] );

        if ( empty( $time_slot ) || $max_groups < 1 ) {
            wp_send_json_error( __( 'Invalid data provided.', 'campus-visit-scheduler' ) );
        }

        $data = array(
            'tour_type'  => $tour_type,
            'time_slot'  => $time_slot,
            'max_groups' => $max_groups,
            'is_active'  => 1,
        );
        $format = array( '%s', '%s', '%d', '%d' );

        if ( 'recurring' === $tour_type ) {
            $day_of_week = absint( $_POST['day_of_week'] );
            $data['day_of_week'] = $day_of_week;
            $format[] = '%d';
        } else {
            $specific_date = sanitize_text_field( $_POST['specific_date'] );
            if ( empty( $specific_date ) ) {
                wp_send_json_error( __( 'Please select a date.', 'campus-visit-scheduler' ) );
            }
            $data['specific_date'] = $specific_date;
            $format[] = '%s';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert( $table, $data, $format );

        if ( $result ) {
            wp_send_json_success( array(
                'id'      => $wpdb->insert_id,
                'message' => __( 'Time slot added successfully.', 'campus-visit-scheduler' ),
            ) );
        } else {
            wp_send_json_error( __( 'Failed to add time slot.', 'campus-visit-scheduler' ) );
        }
    }

    /**
     * AJAX: Delete time slot
     */
    public function ajax_delete_time_slot() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cvs_tour_schedules';
        $id = absint( $_POST['id'] );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

        if ( $result ) {
            wp_send_json_success( __( 'Time slot deleted.', 'campus-visit-scheduler' ) );
        } else {
            wp_send_json_error( __( 'Failed to delete time slot.', 'campus-visit-scheduler' ) );
        }
    }

    /**
     * AJAX: Add blackout date
     */
    public function ajax_add_blackout_date() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cvs_blackout_dates';

        $blackout_date = sanitize_text_field( $_POST['blackout_date'] );
        $reason = sanitize_text_field( $_POST['reason'] );

        if ( empty( $blackout_date ) ) {
            wp_send_json_error( __( 'Please select a date.', 'campus-visit-scheduler' ) );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert(
            $table,
            array(
                'blackout_date' => $blackout_date,
                'reason'        => $reason,
            ),
            array( '%s', '%s' )
        );

        if ( $result ) {
            wp_send_json_success( array(
                'id'      => $wpdb->insert_id,
                'message' => __( 'Blackout date added.', 'campus-visit-scheduler' ),
            ) );
        } else {
            wp_send_json_error( __( 'Failed to add blackout date. It may already exist.', 'campus-visit-scheduler' ) );
        }
    }

    /**
     * AJAX: Delete blackout date
     */
    public function ajax_delete_blackout_date() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cvs_blackout_dates';
        $id = absint( $_POST['id'] );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

        if ( $result ) {
            wp_send_json_success( __( 'Blackout date deleted.', 'campus-visit-scheduler' ) );
        } else {
            wp_send_json_error( __( 'Failed to delete blackout date.', 'campus-visit-scheduler' ) );
        }
    }

    /**
     * AJAX: Add notification recipient
     */
    public function ajax_add_recipient() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $email = sanitize_email( $_POST['email'] );
        $notify_new_booking = isset( $_POST['notify_new_booking'] ) && '1' === $_POST['notify_new_booking'];
        $notify_cancellation = isset( $_POST['notify_cancellation'] ) && '1' === $_POST['notify_cancellation'];

        $result = CVS_Notifications::add_recipient( $email, $notify_new_booking, $notify_cancellation );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( __( 'Recipient added.', 'campus-visit-scheduler' ) );
    }

    /**
     * AJAX: Delete notification recipient
     */
    public function ajax_delete_recipient() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $id = absint( $_POST['id'] );
        $result = CVS_Notifications::remove_recipient( $id );

        if ( $result ) {
            wp_send_json_success( __( 'Recipient deleted.', 'campus-visit-scheduler' ) );
        } else {
            wp_send_json_error( __( 'Failed to delete recipient.', 'campus-visit-scheduler' ) );
        }
    }

    /**
     * AJAX: Cancel booking
     */
    public function ajax_cancel_booking() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $id = absint( $_POST['id'] );
        $notify = isset( $_POST['notify'] ) && '1' === $_POST['notify'];

        $result = CVS_Booking::cancel_booking( $id, $notify );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( __( 'Booking cancelled.', 'campus-visit-scheduler' ) );
    }

    /**
     * AJAX: Resend confirmation email
     */
    public function ajax_resend_confirmation() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $id = absint( $_POST['id'] );
        $booking = CVS_Booking::get_booking( $id );

        if ( ! $booking ) {
            wp_send_json_error( __( 'Booking not found.', 'campus-visit-scheduler' ) );
        }

        $result = CVS_Notifications::send_confirmation_email( $booking );

        if ( $result ) {
            wp_send_json_success( __( 'Confirmation email sent.', 'campus-visit-scheduler' ) );
        } else {
            wp_send_json_error( __( 'Failed to send email.', 'campus-visit-scheduler' ) );
        }
    }

    /**
     * AJAX: Save admin notes
     */
    public function ajax_save_admin_notes() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $id = absint( $_POST['id'] );
        $notes = sanitize_textarea_field( $_POST['notes'] );

        $result = CVS_Booking::update_admin_notes( $id, $notes );

        if ( $result ) {
            wp_send_json_success( __( 'Notes saved.', 'campus-visit-scheduler' ) );
        } else {
            wp_send_json_error( __( 'Failed to save notes.', 'campus-visit-scheduler' ) );
        }
    }

    /**
     * AJAX: Export bookings to CSV
     */
    public function ajax_export_bookings() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $args = array();

        if ( ! empty( $_GET['status'] ) ) {
            $args['status'] = sanitize_text_field( $_GET['status'] );
        }

        if ( ! empty( $_GET['date_from'] ) ) {
            $args['date_from'] = sanitize_text_field( $_GET['date_from'] );
        }

        if ( ! empty( $_GET['date_to'] ) ) {
            $args['date_to'] = sanitize_text_field( $_GET['date_to'] );
        }

        $csv = CVS_Booking::export_to_csv( $args );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=campus-visit-bookings-' . gmdate( 'Y-m-d' ) . '.csv' );

        echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    /**
     * Get tour schedules for display
     *
     * @param string $tour_type Type of tour (recurring or oneoff).
     * @return array Array of schedules.
     */
    public static function get_tour_schedules( $tour_type = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cvs_tour_schedules';

        $where = '';
        if ( ! empty( $tour_type ) ) {
            $where = $wpdb->prepare( ' WHERE tour_type = %s', $tour_type );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results( "SELECT * FROM $table $where ORDER BY day_of_week ASC, specific_date ASC, time_slot ASC", ARRAY_A );
    }

    /**
     * Get blackout dates for display
     *
     * @return array Array of blackout dates.
     */
    public static function get_blackout_dates() {
        global $wpdb;
        $table = $wpdb->prefix . 'cvs_blackout_dates';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results( "SELECT * FROM $table ORDER BY blackout_date ASC", ARRAY_A );
    }

    /**
     * AJAX: Add exclusion period
     */
    public function ajax_add_exclusion_period() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $data = array(
            'period_name'      => isset( $_POST['period_name'] ) ? $_POST['period_name'] : '',
            'start_date'       => isset( $_POST['start_date'] ) ? $_POST['start_date'] : '',
            'end_date'         => isset( $_POST['end_date'] ) ? $_POST['end_date'] : '',
            'recurring_yearly' => isset( $_POST['recurring_yearly'] ) && '1' === $_POST['recurring_yearly'],
        );

        $result = CVS_Helpers::add_exclusion_period( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( array(
            'id'      => $result,
            'message' => __( 'Holiday period added successfully.', 'campus-visit-scheduler' ),
        ) );
    }

    /**
     * AJAX: Update exclusion period
     */
    public function ajax_update_exclusion_period() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( __( 'Invalid period ID.', 'campus-visit-scheduler' ) );
        }

        $data = array(
            'period_name'      => isset( $_POST['period_name'] ) ? $_POST['period_name'] : '',
            'start_date'       => isset( $_POST['start_date'] ) ? $_POST['start_date'] : '',
            'end_date'         => isset( $_POST['end_date'] ) ? $_POST['end_date'] : '',
            'recurring_yearly' => isset( $_POST['recurring_yearly'] ) && '1' === $_POST['recurring_yearly'],
        );

        $result = CVS_Helpers::update_exclusion_period( $id, $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( __( 'Holiday period updated successfully.', 'campus-visit-scheduler' ) );
    }

    /**
     * AJAX: Delete exclusion period
     */
    public function ajax_delete_exclusion_period() {
        check_ajax_referer( 'cvs_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'campus-visit-scheduler' ) );
        }

        $id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( __( 'Invalid period ID.', 'campus-visit-scheduler' ) );
        }

        $result = CVS_Helpers::delete_exclusion_period( $id );

        if ( $result ) {
            wp_send_json_success( __( 'Holiday period deleted.', 'campus-visit-scheduler' ) );
        } else {
            wp_send_json_error( __( 'Failed to delete holiday period.', 'campus-visit-scheduler' ) );
        }
    }
}
