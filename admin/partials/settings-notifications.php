<?php
/**
 * Notifications settings tab
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$recipients = CVS_Notifications::get_all_recipients();
?>

<div class="cvs-notifications-settings">
    <h2><?php esc_html_e( 'Admin Notification Recipients', 'campus-visit-scheduler' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Add email addresses to receive booking notifications.', 'campus-visit-scheduler' ); ?></p>

    <table class="widefat cvs-recipients-table" id="recipients-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Email', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'New Bookings', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Cancellations', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $recipients ) ) : ?>
                <tr class="no-items">
                    <td colspan="4">
                        <?php
                        printf(
                            /* translators: %s: admin email address */
                            esc_html__( 'No recipients configured. Notifications will be sent to the site admin email (%s).', 'campus-visit-scheduler' ),
                            esc_html( get_option( 'admin_email' ) )
                        );
                        ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $recipients as $recipient ) : ?>
                    <tr data-id="<?php echo esc_attr( $recipient['id'] ); ?>">
                        <td><?php echo esc_html( $recipient['email'] ); ?></td>
                        <td>
                            <?php if ( $recipient['notify_new_booking'] ) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-dismiss" style="color: #999;"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $recipient['notify_cancellation'] ) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-dismiss" style="color: #999;"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="button button-small cvs-delete-recipient" data-id="<?php echo esc_attr( $recipient['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'campus-visit-scheduler' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3><?php esc_html_e( 'Add Recipient', 'campus-visit-scheduler' ); ?></h3>
    <form id="add-recipient-form" class="cvs-add-recipient-form">
        <label>
            <?php esc_html_e( 'Email:', 'campus-visit-scheduler' ); ?>
            <input type="email" name="email" required>
        </label>

        <label>
            <input type="checkbox" name="notify_new_booking" value="1" checked>
            <?php esc_html_e( 'New Bookings', 'campus-visit-scheduler' ); ?>
        </label>

        <label>
            <input type="checkbox" name="notify_cancellation" value="1" checked>
            <?php esc_html_e( 'Cancellations', 'campus-visit-scheduler' ); ?>
        </label>

        <button type="submit" class="button button-primary"><?php esc_html_e( 'Add Recipient', 'campus-visit-scheduler' ); ?></button>
    </form>

    <hr>

    <h2><?php esc_html_e( 'Reminder Settings', 'campus-visit-scheduler' ); ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'cvs_email_settings' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cvs_send_reminder"><?php esc_html_e( 'Send Reminders', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="cvs_send_reminder" id="cvs_send_reminder" value="1"
                            <?php checked( get_option( 'cvs_send_reminder', 1 ), 1 ); ?>>
                        <?php esc_html_e( 'Send reminder emails before scheduled tours', 'campus-visit-scheduler' ); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="cvs_reminder_days"><?php esc_html_e( 'Reminder Timing', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <input type="number" name="cvs_reminder_days" id="cvs_reminder_days"
                           value="<?php echo esc_attr( get_option( 'cvs_reminder_days', 2 ) ); ?>"
                           min="1" max="14" class="small-text">
                    <?php esc_html_e( 'days before tour', 'campus-visit-scheduler' ); ?>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
