<?php
/**
 * Bookings list page template
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get filter parameters
$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : '';
$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : '';
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'tour_date';
$order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'ASC';

// Get bookings
$result = CVS_Booking::get_bookings( array(
    'status'    => $status,
    'date_from' => $date_from,
    'date_to'   => $date_to,
    'search'    => $search,
    'orderby'   => $orderby,
    'order'     => $order,
    'page'      => $paged,
    'per_page'  => 20,
) );

$bookings = $result['bookings'];
$total = $result['total'];
$total_pages = $result['pages'];

$statuses = CVS_Helpers::get_booking_statuses();
?>

<div class="wrap cvs-bookings-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Bookings', 'campus-visit-scheduler' ); ?></h1>

    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=cvs_export_bookings&status=' . $status . '&date_from=' . $date_from . '&date_to=' . $date_to ), 'cvs_admin_nonce', 'nonce' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Export CSV', 'campus-visit-scheduler' ); ?>
    </a>

    <hr class="wp-header-end">

    <div class="cvs-filters">
        <form method="get" class="cvs-filter-form">
            <input type="hidden" name="page" value="cvs-bookings">

            <label>
                <?php esc_html_e( 'Status:', 'campus-visit-scheduler' ); ?>
                <select name="status">
                    <option value=""><?php esc_html_e( 'All Statuses', 'campus-visit-scheduler' ); ?></option>
                    <?php foreach ( $statuses as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <?php esc_html_e( 'From:', 'campus-visit-scheduler' ); ?>
                <input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
            </label>

            <label>
                <?php esc_html_e( 'To:', 'campus-visit-scheduler' ); ?>
                <input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
            </label>

            <label>
                <?php esc_html_e( 'Search:', 'campus-visit-scheduler' ); ?>
                <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Name, email, or reference', 'campus-visit-scheduler' ); ?>">
            </label>

            <button type="submit" class="button"><?php esc_html_e( 'Filter', 'campus-visit-scheduler' ); ?></button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-bookings' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'campus-visit-scheduler' ); ?></a>
        </form>
    </div>

    <?php if ( $total > 0 ) : ?>
        <p class="cvs-result-count">
            <?php
            printf(
                /* translators: %d: number of bookings */
                esc_html( _n( '%d booking found', '%d bookings found', $total, 'campus-visit-scheduler' ) ),
                esc_html( number_format_i18n( $total ) )
            );
            ?>
        </p>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped cvs-bookings-table">
        <thead>
            <tr>
                <th scope="col" class="column-reference">
                    <?php esc_html_e( 'Reference', 'campus-visit-scheduler' ); ?>
                </th>
                <th scope="col" class="column-date sortable <?php echo 'tour_date' === $orderby ? 'sorted ' . strtolower( $order ) : 'asc'; ?>">
                    <a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'tour_date', 'order' => ( 'tour_date' === $orderby && 'ASC' === $order ) ? 'DESC' : 'ASC' ) ) ); ?>">
                        <span><?php esc_html_e( 'Tour Date', 'campus-visit-scheduler' ); ?></span>
                        <span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                    </a>
                </th>
                <th scope="col" class="column-time"><?php esc_html_e( 'Time', 'campus-visit-scheduler' ); ?></th>
                <th scope="col" class="column-name">
                    <?php esc_html_e( 'Parent Name', 'campus-visit-scheduler' ); ?>
                </th>
                <th scope="col" class="column-email"><?php esc_html_e( 'Email', 'campus-visit-scheduler' ); ?></th>
                <th scope="col" class="column-group"><?php esc_html_e( 'Group', 'campus-visit-scheduler' ); ?></th>
                <th scope="col" class="column-status"><?php esc_html_e( 'Status', 'campus-visit-scheduler' ); ?></th>
                <th scope="col" class="column-actions"><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $bookings ) ) : ?>
                <tr class="no-items">
                    <td colspan="8"><?php esc_html_e( 'No bookings found.', 'campus-visit-scheduler' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $bookings as $booking ) : ?>
                    <tr data-id="<?php echo esc_attr( $booking['id'] ); ?>">
                        <td class="column-reference">
                            <strong>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-bookings&action=view&id=' . $booking['id'] ) ); ?>">
                                    <?php echo esc_html( $booking['booking_reference'] ); ?>
                                </a>
                            </strong>
                        </td>
                        <td class="column-date">
                            <?php echo esc_html( CVS_Helpers::format_date( $booking['tour_date'] ) ); ?>
                        </td>
                        <td class="column-time">
                            <?php echo esc_html( CVS_Helpers::format_time( $booking['tour_time'] ) ); ?>
                        </td>
                        <td class="column-name">
                            <?php echo esc_html( $booking['parent_name'] ); ?>
                        </td>
                        <td class="column-email">
                            <a href="mailto:<?php echo esc_attr( $booking['email'] ); ?>">
                                <?php echo esc_html( $booking['email'] ); ?>
                            </a>
                        </td>
                        <td class="column-group">
                            <?php echo esc_html( $booking['adults'] + $booking['children'] ); ?>
                        </td>
                        <td class="column-status">
                            <?php echo CVS_Helpers::get_status_badge( $booking['status'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-bookings&action=view&id=' . $booking['id'] ) ); ?>" class="button button-small">
                                <?php esc_html_e( 'View', 'campus-visit-scheduler' ); ?>
                            </a>
                            <?php if ( 'confirmed' === $booking['status'] ) : ?>
                                <button type="button" class="button button-small cvs-cancel-booking" data-id="<?php echo esc_attr( $booking['id'] ); ?>">
                                    <?php esc_html_e( 'Cancel', 'campus-visit-scheduler' ); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    'base'      => add_query_arg( 'paged', '%#%' ),
                    'format'    => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total'     => $total_pages,
                    'current'   => $paged,
                ) );
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
