<?php
/**
 * Plugin Name: Campus Visit Scheduler
 * Plugin URI: https://kalluriit.com.au/plugins/campus-visit-scheduler
 * Description: A comprehensive school tour booking system for managing parent visits and campus tours. Built and maintained by KalluriIT.
 * Version: 1.3.1
 * Author: KalluriIT
 * Author URI: https://kalluriit.com.au
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: campus-visit-scheduler
 * Domain Path: /languages
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin version
define( 'CVS_VERSION', '1.3.1' );

// Plugin directory path
define( 'CVS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Plugin directory URL
define( 'CVS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename
define( 'CVS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Activation hook — runs when the plugin is activated.
 * Creates DB tables (CVS_Activator) and grants custom capabilities to roles.
 */
function cvs_activate() {
    require_once CVS_PLUGIN_DIR . 'includes/class-cvs-activator.php';
    require_once CVS_PLUGIN_DIR . 'includes/class-cvs-capabilities.php';
    CVS_Activator::activate();
    CVS_Capabilities::grant_defaults();
}
register_activation_hook( __FILE__, 'cvs_activate' );

/**
 * Deactivation hook — strips the plugin's custom capabilities so they don't
 * linger on roles when the plugin is removed. DB tables are kept (we don't
 * want to lose booking history on a deactivation/reactivation cycle).
 */
function cvs_deactivate() {
    require_once CVS_PLUGIN_DIR . 'includes/class-cvs-deactivator.php';
    require_once CVS_PLUGIN_DIR . 'includes/class-cvs-capabilities.php';
    CVS_Deactivator::deactivate();
    CVS_Capabilities::revoke_all();
}
register_deactivation_hook( __FILE__, 'cvs_deactivate' );

/**
 * Load plugin text domain for translations
 */
function cvs_load_textdomain() {
    load_plugin_textdomain(
        'campus-visit-scheduler',
        false,
        dirname( CVS_PLUGIN_BASENAME ) . '/languages'
    );
}
add_action( 'plugins_loaded', 'cvs_load_textdomain' );

// Include required files
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-helpers.php';
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-capabilities.php';
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-booking.php';
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-notifications.php';
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-form-fields.php';
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-form-sections.php';

// Load admin functionality
if ( is_admin() ) {
    require_once CVS_PLUGIN_DIR . 'admin/class-cvs-admin.php';
    new CVS_Admin();
}

// Load public functionality
require_once CVS_PLUGIN_DIR . 'public/class-cvs-public.php';
new CVS_Public();

/**
 * Check for database updates on plugin load. Also re-grants custom caps so
 * existing installs picking up a new version (via the WP admin "Replace
 * current with uploaded" path) get role caps without needing a deactivate
 * /reactivate cycle.
 */
function cvs_check_db_updates() {
    $current_db_version = get_option( 'cvs_db_version', '1.0.0' );

    if ( version_compare( $current_db_version, CVS_VERSION, '<' ) ) {
        require_once CVS_PLUGIN_DIR . 'includes/class-cvs-activator.php';
        CVS_Activator::activate();
        CVS_Capabilities::grant_defaults();
    }
}
add_action( 'plugins_loaded', 'cvs_check_db_updates', 5 );
