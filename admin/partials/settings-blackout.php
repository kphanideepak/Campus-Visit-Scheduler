<?php
/**
 * Blackout dates settings tab
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$blackout_dates = CVS_Admin::get_blackout_dates();
?>

<div class="cvs-blackout-dates">
    <h2><?php esc_html_e( 'Blackout Dates', 'campus-visit-scheduler' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Add dates when tours are not available (holidays, school closures, etc.).', 'campus-visit-scheduler' ); ?></p>

    <table class="widefat cvs-blackout-table" id="blackout-dates-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Date', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Reason', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $blackout_dates ) ) : ?>
                <tr class="no-items">
                    <td colspan="3"><?php esc_html_e( 'No blackout dates configured.', 'campus-visit-scheduler' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $blackout_dates as $blackout ) : ?>
                    <tr data-id="<?php echo esc_attr( $blackout['id'] ); ?>">
                        <td><?php echo esc_html( CVS_Helpers::format_date( $blackout['blackout_date'] ) ); ?></td>
                        <td><?php echo esc_html( $blackout['reason'] ? $blackout['reason'] : '-' ); ?></td>
                        <td>
                            <button type="button" class="button button-small cvs-delete-blackout" data-id="<?php echo esc_attr( $blackout['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'campus-visit-scheduler' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3><?php esc_html_e( 'Add Blackout Date', 'campus-visit-scheduler' ); ?></h3>
    <form id="add-blackout-form" class="cvs-add-blackout-form">
        <label>
            <?php esc_html_e( 'Date:', 'campus-visit-scheduler' ); ?>
            <input type="date" name="blackout_date" required>
        </label>

        <label>
            <?php esc_html_e( 'Reason (optional):', 'campus-visit-scheduler' ); ?>
            <input type="text" name="reason" placeholder="<?php esc_attr_e( 'e.g., Public Holiday', 'campus-visit-scheduler' ); ?>">
        </label>

        <button type="submit" class="button button-primary"><?php esc_html_e( 'Add Blackout Date', 'campus-visit-scheduler' ); ?></button>
    </form>
</div>
