<?php
/**
 * Calendar display template (for [campus_visit_calendar] shortcode)
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current month/year
$current_month = (int) gmdate( 'n' );
$current_year = (int) gmdate( 'Y' );

// Get first day of month and number of days
$first_day_timestamp = mktime( 0, 0, 0, $current_month, 1, $current_year );
$first_day_of_week = (int) gmdate( 'w', $first_day_timestamp );
$days_in_month = (int) gmdate( 't', $first_day_timestamp );
$month_name = gmdate( 'F Y', $first_day_timestamp );

$today = gmdate( 'Y-m-d' );
?>

<div class="cvs-calendar-display">
    <h3 class="cvs-calendar-month"><?php echo esc_html( $month_name ); ?></h3>

    <div class="cvs-calendar-legend">
        <span class="cvs-legend-item cvs-legend-available"><?php esc_html_e( 'Available', 'campus-visit-scheduler' ); ?></span>
        <span class="cvs-legend-item cvs-legend-limited"><?php esc_html_e( 'Limited', 'campus-visit-scheduler' ); ?></span>
        <span class="cvs-legend-item cvs-legend-full"><?php esc_html_e( 'Full', 'campus-visit-scheduler' ); ?></span>
    </div>

    <table class="cvs-calendar-table">
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
                            <td class="cvs-day-empty"></td>
                            <?php
                        else :
                            $date_str = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $day );
                            $is_today = ( $date_str === $today );
                            $has_tours = isset( $available_dates[ $date_str ] );

                            $status_class = '';
                            if ( $has_tours ) {
                                $available_count = 0;
                                $total_slots = 0;
                                foreach ( $available_dates[ $date_str ]['slots'] as $slot ) {
                                    $total_slots++;
                                    if ( $slot['available'] ) {
                                        $available_count += $slot['remaining'];
                                    }
                                }
                                if ( $available_count > 3 ) {
                                    $status_class = 'cvs-day-available';
                                } elseif ( $available_count > 0 ) {
                                    $status_class = 'cvs-day-limited';
                                } else {
                                    $status_class = 'cvs-day-full';
                                }
                            }

                            $classes = array( 'cvs-day' );
                            if ( $is_today ) {
                                $classes[] = 'cvs-day-today';
                            }
                            if ( ! empty( $status_class ) ) {
                                $classes[] = $status_class;
                            }
                            ?>
                            <td class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
                                <span class="cvs-day-number"><?php echo esc_html( $day ); ?></span>
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

    <p class="cvs-calendar-note">
        <?php esc_html_e( 'Contact us for tour availability and bookings.', 'campus-visit-scheduler' ); ?>
    </p>
</div>
