<?php
/**
 * Booking confirmation template
 *
 * Note: This file is primarily used as a reference.
 * The confirmation content is generated via JavaScript after successful booking.
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="cvs-confirmation-wrapper">
    <div class="cvs-confirmation-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
    </div>

    <h2><?php esc_html_e( 'Booking Confirmed!', 'campus-visit-scheduler' ); ?></h2>

    <p class="cvs-confirmation-message">
        <?php esc_html_e( 'Thank you for booking a campus tour. A confirmation email has been sent to your email address.', 'campus-visit-scheduler' ); ?>
    </p>

    <div class="cvs-booking-details">
        <h3><?php esc_html_e( 'Booking Details', 'campus-visit-scheduler' ); ?></h3>

        <table class="cvs-details-table">
            <tr>
                <th><?php esc_html_e( 'Reference Number', 'campus-visit-scheduler' ); ?></th>
                <td><strong class="cvs-reference">{booking_reference}</strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Date', 'campus-visit-scheduler' ); ?></th>
                <td class="cvs-date">{tour_date}</td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Time', 'campus-visit-scheduler' ); ?></th>
                <td class="cvs-time">{tour_time}</td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Name', 'campus-visit-scheduler' ); ?></th>
                <td class="cvs-name">{parent_name}</td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Group Size', 'campus-visit-scheduler' ); ?></th>
                <td class="cvs-group-size">{group_size} <?php esc_html_e( 'people', 'campus-visit-scheduler' ); ?></td>
            </tr>
        </table>
    </div>

    <div class="cvs-confirmation-actions">
        <a href="#" class="cvs-btn cvs-btn-secondary cvs-download-ics">
            <?php esc_html_e( 'Add to Calendar', 'campus-visit-scheduler' ); ?>
        </a>
    </div>

    <div class="cvs-confirmation-note">
        <p><?php esc_html_e( 'Please arrive 10 minutes before your scheduled tour time and report to the main reception area.', 'campus-visit-scheduler' ); ?></p>
        <p><?php esc_html_e( 'If you need to cancel or modify your booking, please contact us as soon as possible.', 'campus-visit-scheduler' ); ?></p>
    </div>
</div>
