<?php
/**
 * Reports page template
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get date range from request or default to last 30 days
$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : gmdate( 'Y-m-d' );

// Get statistics
$stats = CVS_Booking::get_statistics( $date_from, $date_to );

// Get bookings by month for the chart
global $wpdb;
$table = $wpdb->prefix . 'cvs_bookings';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$monthly_data = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT DATE_FORMAT(tour_date, '%%Y-%%m') as month, COUNT(*) as count, status
         FROM $table
         WHERE tour_date BETWEEN %s AND %s
         GROUP BY DATE_FORMAT(tour_date, '%%Y-%%m'), status
         ORDER BY month ASC",
        $date_from,
        $date_to
    ),
    ARRAY_A
);

// Organize monthly data
$chart_data = array();
foreach ( $monthly_data as $row ) {
    $month = $row['month'];
    if ( ! isset( $chart_data[ $month ] ) ) {
        $chart_data[ $month ] = array(
            'confirmed' => 0,
            'cancelled' => 0,
        );
    }
    if ( 'confirmed' === $row['status'] ) {
        $chart_data[ $month ]['confirmed'] = (int) $row['count'];
    } else {
        $chart_data[ $month ]['cancelled'] = (int) $row['count'];
    }
}
?>

<div class="wrap cvs-reports-wrap">
    <h1><?php esc_html_e( 'Booking Reports', 'campus-visit-scheduler' ); ?></h1>

    <div class="cvs-report-filters">
        <form method="get" class="cvs-filter-form">
            <input type="hidden" name="page" value="cvs-reports">

            <label>
                <?php esc_html_e( 'From:', 'campus-visit-scheduler' ); ?>
                <input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
            </label>

            <label>
                <?php esc_html_e( 'To:', 'campus-visit-scheduler' ); ?>
                <input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
            </label>

            <button type="submit" class="button button-primary"><?php esc_html_e( 'Generate Report', 'campus-visit-scheduler' ); ?></button>
        </form>
    </div>

    <div class="cvs-stats-grid">
        <div class="cvs-stat-card">
            <h3><?php esc_html_e( 'Total Bookings', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_bookings'] ) ); ?></div>
        </div>

        <div class="cvs-stat-card">
            <h3><?php esc_html_e( 'Confirmed', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-stat-value cvs-stat-confirmed"><?php echo esc_html( number_format_i18n( $stats['confirmed_bookings'] ) ); ?></div>
        </div>

        <div class="cvs-stat-card">
            <h3><?php esc_html_e( 'Cancelled', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-stat-value cvs-stat-cancelled"><?php echo esc_html( number_format_i18n( $stats['cancelled_bookings'] ) ); ?></div>
        </div>

        <div class="cvs-stat-card">
            <h3><?php esc_html_e( 'Cancellation Rate', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-stat-value"><?php echo esc_html( $stats['cancellation_rate'] ); ?>%</div>
        </div>

        <div class="cvs-stat-card">
            <h3><?php esc_html_e( 'Average Group Size', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-stat-value"><?php echo esc_html( $stats['avg_group_size'] ); ?></div>
        </div>
    </div>

    <div class="cvs-report-section">
        <h2><?php esc_html_e( 'Most Popular Tour Times', 'campus-visit-scheduler' ); ?></h2>
        <?php if ( empty( $stats['popular_times'] ) ) : ?>
            <p><?php esc_html_e( 'No data available for the selected date range.', 'campus-visit-scheduler' ); ?></p>
        <?php else : ?>
            <table class="widefat cvs-popular-times-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Time', 'campus-visit-scheduler' ); ?></th>
                        <th><?php esc_html_e( 'Bookings', 'campus-visit-scheduler' ); ?></th>
                        <th><?php esc_html_e( 'Percentage', 'campus-visit-scheduler' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $stats['popular_times'] as $time_slot ) : ?>
                        <?php $percentage = $stats['confirmed_bookings'] > 0 ? round( ( $time_slot['count'] / $stats['confirmed_bookings'] ) * 100, 1 ) : 0; ?>
                        <tr>
                            <td><?php echo esc_html( CVS_Helpers::format_time( $time_slot['tour_time'] ) ); ?></td>
                            <td><?php echo esc_html( $time_slot['count'] ); ?></td>
                            <td>
                                <div class="cvs-progress-bar">
                                    <div class="cvs-progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
                                </div>
                                <span><?php echo esc_html( $percentage ); ?>%</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $chart_data ) ) : ?>
        <div class="cvs-report-section">
            <h2><?php esc_html_e( 'Bookings by Month', 'campus-visit-scheduler' ); ?></h2>
            <table class="widefat cvs-monthly-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Month', 'campus-visit-scheduler' ); ?></th>
                        <th><?php esc_html_e( 'Confirmed', 'campus-visit-scheduler' ); ?></th>
                        <th><?php esc_html_e( 'Cancelled', 'campus-visit-scheduler' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'campus-visit-scheduler' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $chart_data as $month => $data ) : ?>
                        <tr>
                            <td><?php echo esc_html( gmdate( 'F Y', strtotime( $month . '-01' ) ) ); ?></td>
                            <td><?php echo esc_html( $data['confirmed'] ); ?></td>
                            <td><?php echo esc_html( $data['cancelled'] ); ?></td>
                            <td><?php echo esc_html( $data['confirmed'] + $data['cancelled'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
