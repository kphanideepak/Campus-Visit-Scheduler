<?php
/**
 * Holiday exclusion periods settings tab
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$exclusion_periods = CVS_Helpers::get_exclusion_periods();
?>

<div class="cvs-holiday-periods">
    <h2><?php esc_html_e( 'Holiday Exclusion Periods', 'campus-visit-scheduler' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Define date ranges when campus tours are not available (school holidays, term breaks, etc.). Unlike single blackout dates, these define entire periods.', 'campus-visit-scheduler' ); ?></p>

    <table class="widefat cvs-exclusion-periods-table" id="exclusion-periods-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Period Name', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Start Date', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'End Date', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Recurring', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Status', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $exclusion_periods ) ) : ?>
                <tr class="no-items">
                    <td colspan="6"><?php esc_html_e( 'No holiday exclusion periods configured.', 'campus-visit-scheduler' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $exclusion_periods as $period ) : ?>
                    <?php $is_active = CVS_Helpers::is_exclusion_period_active( $period ); ?>
                    <tr data-id="<?php echo esc_attr( $period['id'] ); ?>" class="<?php echo $is_active ? 'cvs-period-active' : ''; ?>">
                        <td>
                            <strong><?php echo esc_html( $period['period_name'] ); ?></strong>
                        </td>
                        <td><?php echo esc_html( CVS_Helpers::format_date( $period['start_date'] ) ); ?></td>
                        <td><?php echo esc_html( CVS_Helpers::format_date( $period['end_date'] ) ); ?></td>
                        <td>
                            <?php if ( $period['recurring_yearly'] ) : ?>
                                <span class="cvs-recurring-badge"><?php esc_html_e( 'Yearly', 'campus-visit-scheduler' ); ?></span>
                            <?php else : ?>
                                <span class="cvs-one-time-badge"><?php esc_html_e( 'One-time', 'campus-visit-scheduler' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $is_active ) : ?>
                                <span class="cvs-status-active"><?php esc_html_e( 'Active', 'campus-visit-scheduler' ); ?></span>
                            <?php else : ?>
                                <span class="cvs-status-inactive"><?php esc_html_e( 'Inactive', 'campus-visit-scheduler' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="button button-small cvs-edit-exclusion"
                                data-id="<?php echo esc_attr( $period['id'] ); ?>"
                                data-name="<?php echo esc_attr( $period['period_name'] ); ?>"
                                data-start="<?php echo esc_attr( $period['start_date'] ); ?>"
                                data-end="<?php echo esc_attr( $period['end_date'] ); ?>"
                                data-recurring="<?php echo esc_attr( $period['recurring_yearly'] ); ?>">
                                <?php esc_html_e( 'Edit', 'campus-visit-scheduler' ); ?>
                            </button>
                            <button type="button" class="button button-small cvs-delete-exclusion" data-id="<?php echo esc_attr( $period['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'campus-visit-scheduler' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div id="cvs-exclusion-form-container">
        <h3 id="cvs-exclusion-form-title"><?php esc_html_e( 'Add Holiday Period', 'campus-visit-scheduler' ); ?></h3>
        <form id="cvs-exclusion-period-form" class="cvs-exclusion-period-form">
            <input type="hidden" name="exclusion_id" id="exclusion_id" value="">

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="period_name"><?php esc_html_e( 'Period Name', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="period_name" id="period_name" class="regular-text" required
                            placeholder="<?php esc_attr_e( 'e.g., Christmas Holidays', 'campus-visit-scheduler' ); ?>">
                        <p class="description"><?php esc_html_e( 'A descriptive name for this exclusion period.', 'campus-visit-scheduler' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="start_date"><?php esc_html_e( 'Start Date', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <input type="date" name="start_date" id="start_date" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="end_date"><?php esc_html_e( 'End Date', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <input type="date" name="end_date" id="end_date" required>
                        <p class="description"><?php esc_html_e( 'End date is inclusive. For year-spanning periods (e.g., Dec 20 - Jan 27), the system will handle the year boundary automatically.', 'campus-visit-scheduler' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="recurring_yearly"><?php esc_html_e( 'Recurring Yearly', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="recurring_yearly" id="recurring_yearly" value="1">
                            <?php esc_html_e( 'Repeat this exclusion period every year', 'campus-visit-scheduler' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'When enabled, the month and day will be used to block dates in any year. For recurring periods, only the month/day portion is used.', 'campus-visit-scheduler' ); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary" id="cvs-exclusion-submit-btn">
                    <?php esc_html_e( 'Add Period', 'campus-visit-scheduler' ); ?>
                </button>
                <button type="button" class="button" id="cvs-exclusion-cancel-btn" style="display: none;">
                    <?php esc_html_e( 'Cancel', 'campus-visit-scheduler' ); ?>
                </button>
            </p>
        </form>
    </div>

    <div class="cvs-exclusion-info">
        <h4><?php esc_html_e( 'How Exclusion Periods Work', 'campus-visit-scheduler' ); ?></h4>
        <ul>
            <li><?php esc_html_e( 'Dates within an exclusion period will not appear as available for booking.', 'campus-visit-scheduler' ); ?></li>
            <li><?php esc_html_e( 'Recurring periods repeat every year on the same dates (useful for annual school holidays).', 'campus-visit-scheduler' ); ?></li>
            <li><?php esc_html_e( 'Year-spanning periods (e.g., Dec 20 to Jan 27) are handled automatically.', 'campus-visit-scheduler' ); ?></li>
            <li><?php esc_html_e( 'Existing bookings on excluded dates are not automatically cancelled.', 'campus-visit-scheduler' ); ?></li>
            <li><?php esc_html_e( 'Use single-day Blackout Dates for one-off closures like public holidays.', 'campus-visit-scheduler' ); ?></li>
        </ul>
    </div>
</div>
