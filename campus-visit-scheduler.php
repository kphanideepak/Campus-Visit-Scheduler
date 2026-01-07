<?php
/**
 * Plugin Name: Campus Visit Scheduler
 * Plugin URI: https://kalluriit.com.au/plugins/campus-visit-scheduler
 * Description: A comprehensive school tour booking system for managing parent visits and campus tours.
 * Version: 1.1.0
 * Author: Phil Kalluri
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
define( 'CVS_VERSION', '1.1.0' );

// Plugin directory path
define( 'CVS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Plugin directory URL
define( 'CVS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename
define( 'CVS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Activation hook
 */
function cvs_activate() {
    require_once CVS_PLUGIN_DIR . 'includes/class-cvs-activator.php';
    CVS_Activator::activate();
}
register_activation_hook( __FILE__, 'cvs_activate' );

/**
 * Deactivation hook
 */
function cvs_deactivate() {
    require_once CVS_PLUGIN_DIR . 'includes/class-cvs-deactivator.php';
    CVS_Deactivator::deactivate();
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
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-booking.php';
require_once CVS_PLUGIN_DIR . 'includes/class-cvs-notifications.php';

// Load admin functionality
if ( is_admin() ) {
    require_once CVS_PLUGIN_DIR . 'admin/class-cvs-admin.php';
    new CVS_Admin();
}

// Load public functionality
require_once CVS_PLUGIN_DIR . 'public/class-cvs-public.php';
new CVS_Public();

/**
 * Check for database updates on plugin load
 */
function cvs_check_db_updates() {
    $current_db_version = get_option( 'cvs_db_version', '1.0.0' );

    if ( version_compare( $current_db_version, CVS_VERSION, '<' ) ) {
        require_once CVS_PLUGIN_DIR . 'includes/class-cvs-activator.php';
        CVS_Activator::activate();
    }
}
add_action( 'plugins_loaded', 'cvs_check_db_updates', 5 );
