<?php
/**
 * Booking detail page template
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$booking_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$booking = CVS_Booking::get_booking( $booking_id );

if ( ! $booking ) {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Booking Not Found', 'campus-visit-scheduler' ); ?></h1>
        <p><?php esc_html_e( 'The requested booking could not be found.', 'campus-visit-scheduler' ); ?></p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-bookings' ) ); ?>" class="button">
            <?php esc_html_e( 'Back to Bookings', 'campus-visit-scheduler' ); ?>
        </a>
    </div>
    <?php
    return;
}

$year_levels = CVS_Helpers::get_year_levels();
?>

<div class="wrap cvs-booking-detail-wrap">
    <h1>
        <?php
        printf(
            /* translators: %s: booking reference */
            esc_html__( 'Booking: %s', 'campus-visit-scheduler' ),
            esc_html( $booking['booking_reference'] )
        );
        ?>
        <?php echo CVS_Helpers::get_status_badge( $booking['status'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </h1>

    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-bookings' ) ); ?>" class="page-title-action">
        <?php esc_html_e( '&larr; Back to Bookings', 'campus-visit-scheduler' ); ?>
    </a>

    <hr class="wp-header-end">

    <div class="cvs-booking-detail">
        <div class="cvs-detail-columns">
            <div class="cvs-detail-main">
                <div class="cvs-detail-card">
                    <h2><?php esc_html_e( 'Tour Details', 'campus-visit-scheduler' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Date', 'campus-visit-scheduler' ); ?></th>
                            <td><?php echo esc_html( CVS_Helpers::format_date( $booking['tour_date'] ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Time', 'campus-visit-scheduler' ); ?></th>
                            <td><?php echo esc_html( CVS_Helpers::format_time( $booking['tour_time'] ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Group Size', 'campus-visit-scheduler' ); ?></th>
                            <td>
                                <?php
                                printf(
                                    /* translators: 1: total people, 2: adults, 3: children */
                                    esc_html__( '%1$d people (%2$d adults, %3$d children)', 'campus-visit-scheduler' ),
                                    (int) $booking['adults'] + (int) $booking['children'],
                                    (int) $booking['adults'],
                                    (int) $booking['children']
                                );
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="cvs-detail-card">
                    <h2><?php esc_html_e( 'Contact Information', 'campus-visit-scheduler' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Parent Name', 'campus-visit-scheduler' ); ?></th>
                            <td><?php echo esc_html( $booking['parent_name'] ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Email', 'campus-visit-scheduler' ); ?></th>
                            <td>
                                <a href="mailto:<?php echo esc_attr( $booking['email'] ); ?>">
                                    <?php echo esc_html( $booking['email'] ); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Phone', 'campus-visit-scheduler' ); ?></th>
                            <td>
                                <a href="tel:<?php echo esc_attr( $booking['phone'] ); ?>">
                                    <?php echo esc_html( $booking['phone'] ); ?>
                                </a>
                            </td>
                        </tr>
                        <?php if ( ! empty( $booking['child_name'] ) ) : ?>
                            <tr>
                                <th><?php esc_html_e( 'Child\'s Name', 'campus-visit-scheduler' ); ?></th>
                                <td><?php echo esc_html( $booking['child_name'] ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ( ! empty( $booking['year_level'] ) ) : ?>
                            <tr>
                                <th><?php esc_html_e( 'Year Level', 'campus-visit-scheduler' ); ?></th>
                                <td><?php echo esc_html( isset( $year_levels[ $booking['year_level'] ] ) ? $year_levels[ $booking['year_level'] ] : $booking['year_level'] ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <?php if ( ! empty( $booking['special_requirements'] ) ) : ?>
                    <div class="cvs-detail-card">
                        <h2><?php esc_html_e( 'Special Requirements', 'campus-visit-scheduler' ); ?></h2>
                        <p><?php echo esc_html( $booking['special_requirements'] ); ?></p>
                    </div>
                <?php endif; ?>

                <div class="cvs-detail-card">
                    <h2><?php esc_html_e( 'Admin Notes', 'campus-visit-scheduler' ); ?></h2>
                    <form id="admin-notes-form">
                        <textarea id="admin-notes" name="notes" rows="4" class="large-text"><?php echo esc_textarea( $booking['admin_notes'] ); ?></textarea>
                        <p>
                            <button type="button" id="save-admin-notes" class="button" data-id="<?php echo esc_attr( $booking['id'] ); ?>">
                                <?php esc_html_e( 'Save Notes', 'campus-visit-scheduler' ); ?>
                            </button>
                            <span id="notes-saved-message" style="display: none; color: green; margin-left: 10px;">
                                <?php esc_html_e( 'Notes saved!', 'campus-visit-scheduler' ); ?>
                            </span>
                        </p>
                    </form>
                </div>
            </div>

            <div class="cvs-detail-sidebar">
                <div class="cvs-detail-card">
                    <h2><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></h2>
                    <div class="cvs-actions-list">
                        <?php if ( 'confirmed' === $booking['status'] ) : ?>
                            <button type="button" class="button cvs-resend-confirmation" data-id="<?php echo esc_attr( $booking['id'] ); ?>">
                                <?php esc_html_e( 'Resend Confirmation Email', 'campus-visit-scheduler' ); ?>
                            </button>
                            <button type="button" class="button button-link-delete cvs-cancel-booking" data-id="<?php echo esc_attr( $booking['id'] ); ?>">
                                <?php esc_html_e( 'Cancel Booking', 'campus-visit-scheduler' ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="cvs-detail-card">
                    <h2><?php esc_html_e( 'Booking Information', 'campus-visit-scheduler' ); ?></h2>
                    <table class="cvs-meta-table">
                        <tr>
                            <th><?php esc_html_e( 'Reference', 'campus-visit-scheduler' ); ?></th>
                            <td><code><?php echo esc_html( $booking['booking_reference'] ); ?></code></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Status', 'campus-visit-scheduler' ); ?></th>
                            <td><?php echo CVS_Helpers::get_status_badge( $booking['status'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Created', 'campus-visit-scheduler' ); ?></th>
                            <td><?php echo esc_html( CVS_Helpers::format_datetime( $booking['created_at'] ) ); ?></td>
                        </tr>
                        <?php if ( ! empty( $booking['cancelled_at'] ) ) : ?>
                            <tr>
                                <th><?php esc_html_e( 'Cancelled', 'campus-visit-scheduler' ); ?></th>
                                <td><?php echo esc_html( CVS_Helpers::format_datetime( $booking['cancelled_at'] ) ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
