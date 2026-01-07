<?php
/**
 * Email templates settings tab
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="cvs-email-settings">
    <h2><?php esc_html_e( 'Email Templates', 'campus-visit-scheduler' ); ?></h2>

    <div class="cvs-placeholder-help">
        <h3><?php esc_html_e( 'Available Placeholders', 'campus-visit-scheduler' ); ?></h3>
        <p><?php esc_html_e( 'Use these placeholders in your email templates. They will be replaced with actual booking data.', 'campus-visit-scheduler' ); ?></p>
        <code>{parent_name}</code>
        <code>{tour_date}</code>
        <code>{tour_time}</code>
        <code>{group_size}</code>
        <code>{booking_reference}</code>
        <code>{email}</code>
        <code>{phone}</code>
        <code>{adults}</code>
        <code>{children}</code>
        <code>{child_name}</code>
        <code>{year_level}</code>
        <code>{special_requirements}</code>
        <code>{admin_url}</code>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields( 'cvs_email_settings' ); ?>

        <h3><?php esc_html_e( 'Confirmation Email (sent to parent)', 'campus-visit-scheduler' ); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cvs_confirmation_subject"><?php esc_html_e( 'Subject', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <input type="text" name="cvs_confirmation_subject" id="cvs_confirmation_subject"
                           value="<?php echo esc_attr( get_option( 'cvs_confirmation_subject' ) ); ?>"
                           class="large-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cvs_confirmation_body"><?php esc_html_e( 'Message', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <textarea name="cvs_confirmation_body" id="cvs_confirmation_body"
                              rows="12" class="large-text"><?php echo esc_textarea( get_option( 'cvs_confirmation_body' ) ); ?></textarea>
                </td>
            </tr>
        </table>

        <hr>

        <h3><?php esc_html_e( 'Cancellation Email (sent to parent)', 'campus-visit-scheduler' ); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cvs_cancellation_subject"><?php esc_html_e( 'Subject', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <input type="text" name="cvs_cancellation_subject" id="cvs_cancellation_subject"
                           value="<?php echo esc_attr( get_option( 'cvs_cancellation_subject' ) ); ?>"
                           class="large-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cvs_cancellation_body"><?php esc_html_e( 'Message', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <textarea name="cvs_cancellation_body" id="cvs_cancellation_body"
                              rows="10" class="large-text"><?php echo esc_textarea( get_option( 'cvs_cancellation_body' ) ); ?></textarea>
                </td>
            </tr>
        </table>

        <hr>

        <h3><?php esc_html_e( 'Admin Notification Email', 'campus-visit-scheduler' ); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cvs_admin_notification_subject"><?php esc_html_e( 'Subject', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <input type="text" name="cvs_admin_notification_subject" id="cvs_admin_notification_subject"
                           value="<?php echo esc_attr( get_option( 'cvs_admin_notification_subject' ) ); ?>"
                           class="large-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cvs_admin_notification_body"><?php esc_html_e( 'Message', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <textarea name="cvs_admin_notification_body" id="cvs_admin_notification_body"
                              rows="12" class="large-text"><?php echo esc_textarea( get_option( 'cvs_admin_notification_body' ) ); ?></textarea>
                </td>
            </tr>
        </table>

        <hr>

        <h3><?php esc_html_e( 'Reminder Email (sent to parent)', 'campus-visit-scheduler' ); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cvs_reminder_subject"><?php esc_html_e( 'Subject', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <input type="text" name="cvs_reminder_subject" id="cvs_reminder_subject"
                           value="<?php echo esc_attr( get_option( 'cvs_reminder_subject' ) ); ?>"
                           class="large-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cvs_reminder_body"><?php esc_html_e( 'Message', 'campus-visit-scheduler' ); ?></label>
                </th>
                <td>
                    <textarea name="cvs_reminder_body" id="cvs_reminder_body"
                              rows="12" class="large-text"><?php echo esc_textarea( get_option( 'cvs_reminder_body' ) ); ?></textarea>
                </td>
            </tr>
        </table>

        <p class="submit">
            <?php submit_button( __( 'Save Changes', 'campus-visit-scheduler' ), 'primary', 'submit', false ); ?>
            <button type="button" id="cvs-reset-email-templates" class="button button-secondary" style="margin-left: 10px;">
                <?php esc_html_e( 'Reset to Defaults', 'campus-visit-scheduler' ); ?>
            </button>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#cvs-reset-email-templates').on('click', function() {
        if (!confirm('<?php echo esc_js( __( 'Are you sure you want to reset all email templates to their default values? This cannot be undone.', 'campus-visit-scheduler' ) ); ?>')) {
            return;
        }

        var $button = $(this);
        $button.prop('disabled', true).text('<?php echo esc_js( __( 'Resetting...', 'campus-visit-scheduler' ) ); ?>');

        $.ajax({
            url: cvs_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'cvs_reset_email_templates',
                nonce: cvs_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php echo esc_js( __( 'Email templates have been reset to defaults. Reloading page...', 'campus-visit-scheduler' ) ); ?>');
                    location.reload();
                } else {
                    alert(response.data || '<?php echo esc_js( __( 'An error occurred.', 'campus-visit-scheduler' ) ); ?>');
                    $button.prop('disabled', false).text('<?php echo esc_js( __( 'Reset to Defaults', 'campus-visit-scheduler' ) ); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js( __( 'An error occurred. Please try again.', 'campus-visit-scheduler' ) ); ?>');
                $button.prop('disabled', false).text('<?php echo esc_js( __( 'Reset to Defaults', 'campus-visit-scheduler' ) ); ?>');
            }
        });
    });
});
</script>
