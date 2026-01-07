<?php
/**
 * Fired during plugin deactivation
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Deactivator
 *
 * Handles deactivation tasks (does NOT remove data)
 */
class CVS_Deactivator {

    /**
     * Run deactivation tasks
     *
     * Note: We do NOT remove database tables or options on deactivation.
     * Data removal only happens on uninstall (uninstall.php).
     */
    public static function deactivate() {
        // Clear any scheduled cron events
        wp_clear_scheduled_hook( 'cvs_send_reminders' );
        wp_clear_scheduled_hook( 'cvs_cleanup_old_bookings' );

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
