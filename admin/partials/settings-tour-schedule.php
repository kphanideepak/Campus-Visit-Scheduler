<?php
/**
 * Tour schedule settings tab
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$recurring_schedules = CVS_Admin::get_tour_schedules( 'recurring' );
$oneoff_schedules = CVS_Admin::get_tour_schedules( 'oneoff' );
$days_of_week = array(
    0 => __( 'Sunday', 'campus-visit-scheduler' ),
    1 => __( 'Monday', 'campus-visit-scheduler' ),
    2 => __( 'Tuesday', 'campus-visit-scheduler' ),
    3 => __( 'Wednesday', 'campus-visit-scheduler' ),
    4 => __( 'Thursday', 'campus-visit-scheduler' ),
    5 => __( 'Friday', 'campus-visit-scheduler' ),
    6 => __( 'Saturday', 'campus-visit-scheduler' ),
);
?>

<div class="cvs-tour-schedule">
    <h2><?php esc_html_e( 'Recurring Tours (Weekly Schedule)', 'campus-visit-scheduler' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Set up regular tour times that repeat every week.', 'campus-visit-scheduler' ); ?></p>

    <table class="widefat cvs-schedule-table" id="recurring-schedule-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Day', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Time', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Max Groups', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $recurring_schedules ) ) : ?>
                <tr class="no-items">
                    <td colspan="4"><?php esc_html_e( 'No recurring tours configured.', 'campus-visit-scheduler' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $recurring_schedules as $schedule ) : ?>
                    <tr data-id="<?php echo esc_attr( $schedule['id'] ); ?>">
                        <td><?php echo esc_html( $days_of_week[ $schedule['day_of_week'] ] ); ?></td>
                        <td><?php echo esc_html( CVS_Helpers::format_time( $schedule['time_slot'] ) ); ?></td>
                        <td><?php echo esc_html( $schedule['max_groups'] ); ?></td>
                        <td>
                            <button type="button" class="button button-small cvs-delete-slot" data-id="<?php echo esc_attr( $schedule['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'campus-visit-scheduler' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3><?php esc_html_e( 'Add Recurring Tour Slot', 'campus-visit-scheduler' ); ?></h3>
    <form id="add-recurring-slot-form" class="cvs-add-slot-form">
        <input type="hidden" name="tour_type" value="recurring">

        <label>
            <?php esc_html_e( 'Day:', 'campus-visit-scheduler' ); ?>
            <select name="day_of_week" required>
                <?php foreach ( $days_of_week as $num => $name ) : ?>
                    <option value="<?php echo esc_attr( $num ); ?>"><?php echo esc_html( $name ); ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <?php esc_html_e( 'Time:', 'campus-visit-scheduler' ); ?>
            <input type="time" name="time_slot" required>
        </label>

        <label>
            <?php esc_html_e( 'Max Groups:', 'campus-visit-scheduler' ); ?>
            <input type="number" name="max_groups" value="5" min="1" max="100" required class="small-text">
        </label>

        <button type="submit" class="button button-primary"><?php esc_html_e( 'Add Slot', 'campus-visit-scheduler' ); ?></button>
    </form>

    <hr>

    <h2><?php esc_html_e( 'One-Off / Special Event Tours', 'campus-visit-scheduler' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Set up tours for specific dates (open days, special events, etc.).', 'campus-visit-scheduler' ); ?></p>

    <table class="widefat cvs-schedule-table" id="oneoff-schedule-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Date', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Time', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Max Groups', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $oneoff_schedules ) ) : ?>
                <tr class="no-items">
                    <td colspan="4"><?php esc_html_e( 'No one-off tours configured.', 'campus-visit-scheduler' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $oneoff_schedules as $schedule ) : ?>
                    <tr data-id="<?php echo esc_attr( $schedule['id'] ); ?>">
                        <td><?php echo esc_html( CVS_Helpers::format_date( $schedule['specific_date'] ) ); ?></td>
                        <td><?php echo esc_html( CVS_Helpers::format_time( $schedule['time_slot'] ) ); ?></td>
                        <td><?php echo esc_html( $schedule['max_groups'] ); ?></td>
                        <td>
                            <button type="button" class="button button-small cvs-delete-slot" data-id="<?php echo esc_attr( $schedule['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'campus-visit-scheduler' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3><?php esc_html_e( 'Add One-Off Tour Slot', 'campus-visit-scheduler' ); ?></h3>
    <form id="add-oneoff-slot-form" class="cvs-add-slot-form">
        <input type="hidden" name="tour_type" value="oneoff">

        <label>
            <?php esc_html_e( 'Date:', 'campus-visit-scheduler' ); ?>
            <input type="date" name="specific_date" required min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
        </label>

        <label>
            <?php esc_html_e( 'Time:', 'campus-visit-scheduler' ); ?>
            <input type="time" name="time_slot" required>
        </label>

        <label>
            <?php esc_html_e( 'Max Groups:', 'campus-visit-scheduler' ); ?>
            <input type="number" name="max_groups" value="5" min="1" max="100" required class="small-text">
        </label>

        <button type="submit" class="button button-primary"><?php esc_html_e( 'Add Slot', 'campus-visit-scheduler' ); ?></button>
    </form>
</div>
