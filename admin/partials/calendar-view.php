<?php
/**
 * Calendar view page template
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current month/year
$current_month = isset( $_GET['month'] ) ? absint( $_GET['month'] ) : (int) gmdate( 'n' );
$current_year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : (int) gmdate( 'Y' );

// Validate month/year
if ( $current_month < 1 || $current_month > 12 ) {
    $current_month = (int) gmdate( 'n' );
}
if ( $current_year < 2020 || $current_year > 2100 ) {
    $current_year = (int) gmdate( 'Y' );
}

// Calculate prev/next month
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ( $prev_month < 1 ) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ( $next_month > 12 ) {
    $next_month = 1;
    $next_year++;
}

// Get first day of month and number of days
$first_day_timestamp = mktime( 0, 0, 0, $current_month, 1, $current_year );
$first_day_of_week = (int) gmdate( 'w', $first_day_timestamp );
$days_in_month = (int) gmdate( 't', $first_day_timestamp );
$month_name = gmdate( 'F Y', $first_day_timestamp );

// Get all bookings for this month
global $wpdb;
$table = $wpdb->prefix . 'cvs_bookings';
$start_date = sprintf( '%04d-%02d-01', $current_year, $current_month );
$end_date = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $days_in_month );

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$bookings_raw = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT tour_date, tour_time, COUNT(*) as count, status FROM $table WHERE tour_date BETWEEN %s AND %s GROUP BY tour_date, tour_time, status",
        $start_date,
        $end_date
    ),
    ARRAY_A
);

// Organize bookings by date
$bookings_by_date = array();
foreach ( $bookings_raw as $booking ) {
    $date = $booking['tour_date'];
    if ( ! isset( $bookings_by_date[ $date ] ) ) {
        $bookings_by_date[ $date ] = array(
            'confirmed' => 0,
            'cancelled' => 0,
            'total'     => 0,
        );
    }
    if ( 'confirmed' === $booking['status'] ) {
        $bookings_by_date[ $date ]['confirmed'] += (int) $booking['count'];
    } else {
        $bookings_by_date[ $date ]['cancelled'] += (int) $booking['count'];
    }
    $bookings_by_date[ $date ]['total'] += (int) $booking['count'];
}

// Get blackout dates for this month
$blackout_table = $wpdb->prefix . 'cvs_blackout_dates';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$blackout_dates_raw = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT blackout_date FROM $blackout_table WHERE blackout_date BETWEEN %s AND %s",
        $start_date,
        $end_date
    )
);
$blackout_dates = array_flip( $blackout_dates_raw );

// Pre-calculate excluded dates for this month
$excluded_dates = array();
for ( $d = 1; $d <= $days_in_month; $d++ ) {
    $check_date = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $d );
    if ( CVS_Helpers::is_excluded_date( $check_date ) ) {
        $excluded_dates[ $check_date ] = true;
    }
}

$today = gmdate( 'Y-m-d' );
?>

<div class="wrap cvs-calendar-wrap">
    <h1><?php esc_html_e( 'Booking Calendar', 'campus-visit-scheduler' ); ?></h1>

    <div class="cvs-calendar-nav">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-calendar&month=' . $prev_month . '&year=' . $prev_year ) ); ?>" class="button">
            &laquo; <?php esc_html_e( 'Previous', 'campus-visit-scheduler' ); ?>
        </a>
        <span class="cvs-calendar-title"><?php echo esc_html( $month_name ); ?></span>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-calendar&month=' . $next_month . '&year=' . $next_year ) ); ?>" class="button">
            <?php esc_html_e( 'Next', 'campus-visit-scheduler' ); ?> &raquo;
        </a>
    </div>

    <div class="cvs-calendar-legend">
        <span class="legend-item"><span class="legend-dot confirmed"></span> <?php esc_html_e( 'Confirmed', 'campus-visit-scheduler' ); ?></span>
        <span class="legend-item"><span class="legend-dot blackout"></span> <?php esc_html_e( 'Blackout', 'campus-visit-scheduler' ); ?></span>
        <span class="legend-item"><span class="legend-dot excluded"></span> <?php esc_html_e( 'Holiday Period', 'campus-visit-scheduler' ); ?></span>
        <span class="legend-item"><span class="legend-dot today"></span> <?php esc_html_e( 'Today', 'campus-visit-scheduler' ); ?></span>
    </div>

    <table class="cvs-calendar">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Sun', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Mon', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Tue', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Wed', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Thu', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Fri', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Sat', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $day = 1;
            $cell = 0;

            while ( $day <= $days_in_month ) :
                ?>
                <tr>
                    <?php
                    for ( $i = 0; $i < 7; $i++ ) :
                        if ( ( $cell < $first_day_of_week ) || ( $day > $days_in_month ) ) :
                            ?>
                            <td class="cvs-calendar-empty"></td>
                            <?php
                        else :
                            $date_str = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $day );
                            $is_today = ( $date_str === $today );
                            $is_blackout = isset( $blackout_dates[ $date_str ] );
                            $is_excluded = isset( $excluded_dates[ $date_str ] );
                            $has_bookings = isset( $bookings_by_date[ $date_str ] );
                            $confirmed_count = $has_bookings ? $bookings_by_date[ $date_str ]['confirmed'] : 0;

                            $classes = array( 'cvs-calendar-day' );
                            if ( $is_today ) {
                                $classes[] = 'today';
                            }
                            if ( $is_blackout ) {
                                $classes[] = 'blackout';
                            }
                            if ( $is_excluded ) {
                                $classes[] = 'excluded';
                            }
                            if ( $confirmed_count > 0 ) {
                                $classes[] = 'has-bookings';
                            }
                            ?>
                            <td class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
                                <div class="cvs-day-number"><?php echo esc_html( $day ); ?></div>
                                <?php if ( $confirmed_count > 0 ) : ?>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-bookings&date_from=' . $date_str . '&date_to=' . $date_str ) ); ?>" class="cvs-booking-count">
                                        <?php
                                        printf(
                                            /* translators: %d: number of bookings */
                                            esc_html( _n( '%d booking', '%d bookings', $confirmed_count, 'campus-visit-scheduler' ) ),
                                            esc_html( $confirmed_count )
                                        );
                                        ?>
                                    </a>
                                <?php elseif ( $is_blackout ) : ?>
                                    <span class="cvs-blackout-label"><?php esc_html_e( 'Closed', 'campus-visit-scheduler' ); ?></span>
                                <?php elseif ( $is_excluded ) : ?>
                                    <span class="cvs-exclusion-label"><?php esc_html_e( 'Holiday', 'campus-visit-scheduler' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <?php
                            $day++;
                        endif;
                        $cell++;
                    endfor;
                    ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
