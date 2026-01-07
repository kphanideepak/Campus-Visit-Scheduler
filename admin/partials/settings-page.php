<?php
/**
 * Settings page template
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current tab
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
$tabs = array(
    'general'       => __( 'General', 'campus-visit-scheduler' ),
    'tour_schedule' => __( 'Tour Schedule', 'campus-visit-scheduler' ),
    'blackout'      => __( 'Blackout Dates', 'campus-visit-scheduler' ),
    'holidays'      => __( 'Holiday Periods', 'campus-visit-scheduler' ),
    'notifications' => __( 'Notifications', 'campus-visit-scheduler' ),
    'emails'        => __( 'Email Templates', 'campus-visit-scheduler' ),
);
?>

<div class="wrap cvs-settings-wrap">
    <h1><?php esc_html_e( 'Campus Visit Scheduler Settings', 'campus-visit-scheduler' ); ?></h1>

    <nav class="nav-tab-wrapper">
        <?php foreach ( $tabs as $tab_id => $tab_name ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=cvs-settings&tab=' . $tab_id ) ); ?>"
               class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html( $tab_name ); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="cvs-settings-content">
        <?php
        switch ( $current_tab ) {
            case 'tour_schedule':
                include 'settings-tour-schedule.php';
                break;
            case 'blackout':
                include 'settings-blackout.php';
                break;
            case 'holidays':
                include 'settings-holidays.php';
                break;
            case 'notifications':
                include 'settings-notifications.php';
                break;
            case 'emails':
                include 'settings-emails.php';
                break;
            default:
                include 'settings-general.php';
                break;
        }
        ?>
    </div>
</div>
