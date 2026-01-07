<?php
/**
 * General settings tab
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<form method="post" action="options.php">
    <?php settings_fields( 'cvs_general_settings' ); ?>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="cvs_bookings_enabled"><?php esc_html_e( 'Enable Bookings', 'campus-visit-scheduler' ); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="cvs_bookings_enabled" id="cvs_bookings_enabled" value="1"
                        <?php checked( get_option( 'cvs_bookings_enabled', 1 ), 1 ); ?>>
                    <?php esc_html_e( 'Allow parents to make new bookings', 'campus-visit-scheduler' ); ?>
                </label>
                <p class="description">
                    <?php esc_html_e( 'Uncheck to temporarily disable the booking form.', 'campus-visit-scheduler' ); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="cvs_advance_booking_days"><?php esc_html_e( 'Advance Booking Window', 'campus-visit-scheduler' ); ?></label>
            </th>
            <td>
                <input type="number" name="cvs_advance_booking_days" id="cvs_advance_booking_days"
                       value="<?php echo esc_attr( get_option( 'cvs_advance_booking_days', 60 ) ); ?>"
                       min="7" max="365" class="small-text">
                <?php esc_html_e( 'days', 'campus-visit-scheduler' ); ?>
                <p class="description">
                    <?php esc_html_e( 'How far in advance can parents book tours.', 'campus-visit-scheduler' ); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="cvs_min_group_size"><?php esc_html_e( 'Minimum Group Size', 'campus-visit-scheduler' ); ?></label>
            </th>
            <td>
                <input type="number" name="cvs_min_group_size" id="cvs_min_group_size"
                       value="<?php echo esc_attr( get_option( 'cvs_min_group_size', 1 ) ); ?>"
                       min="1" max="20" class="small-text">
                <?php esc_html_e( 'people', 'campus-visit-scheduler' ); ?>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="cvs_max_group_size"><?php esc_html_e( 'Maximum Group Size', 'campus-visit-scheduler' ); ?></label>
            </th>
            <td>
                <input type="number" name="cvs_max_group_size" id="cvs_max_group_size"
                       value="<?php echo esc_attr( get_option( 'cvs_max_group_size', 6 ) ); ?>"
                       min="1" max="50" class="small-text">
                <?php esc_html_e( 'people', 'campus-visit-scheduler' ); ?>
                <p class="description">
                    <?php esc_html_e( 'Maximum number of people per booking (family group).', 'campus-visit-scheduler' ); ?>
                </p>
            </td>
        </tr>
    </table>

    <h2><?php esc_html_e( 'Shortcode Usage', 'campus-visit-scheduler' ); ?></h2>
    <p><?php esc_html_e( 'Use the following shortcode to display the booking form on any page:', 'campus-visit-scheduler' ); ?></p>
    <code>[campus_visit_scheduler]</code>

    <?php submit_button(); ?>
</form>
