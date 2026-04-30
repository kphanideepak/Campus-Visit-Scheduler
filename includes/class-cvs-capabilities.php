<?php
/**
 * Custom capabilities for the Campus Visit Scheduler.
 *
 * Splits "day-to-day staff actions" from "agency/admin configuration":
 *
 *   cvs_view_bookings   — read-only access to bookings list, calendar, reports
 *   cvs_manage_bookings — write access to a booking (cancel, confirm, edit notes)
 *   cvs_manage_settings — full settings access (general, form builder, schedule,
 *                         blackout dates, holidays, notifications, email templates)
 *
 * Granted on plugin activation:
 *   administrator → all three caps
 *   editor        → cvs_view_bookings + cvs_manage_bookings (no settings)
 *
 * That mapping lets school office staff manage the booking pipeline with an
 * Editor account while keeping the configuration tabs as the agency zone.
 *
 * @package CampusVisitScheduler
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CVS_Capabilities {

    const VIEW_BOOKINGS   = 'cvs_view_bookings';
    const MANAGE_BOOKINGS = 'cvs_manage_bookings';
    const MANAGE_SETTINGS = 'cvs_manage_settings';

    /**
     * All capabilities introduced by this plugin.
     *
     * @return string[]
     */
    public static function all() {
        return array( self::VIEW_BOOKINGS, self::MANAGE_BOOKINGS, self::MANAGE_SETTINGS );
    }

    /**
     * Default role-to-cap matrix used at activation time.
     *
     * @return array<string, string[]>
     */
    public static function default_role_grants() {
        return array(
            'administrator' => array( self::VIEW_BOOKINGS, self::MANAGE_BOOKINGS, self::MANAGE_SETTINGS ),
            'editor'        => array( self::VIEW_BOOKINGS, self::MANAGE_BOOKINGS ),
        );
    }

    /**
     * Grant the default caps. Idempotent — safe to call on every activation
     * and on the version-bump self-heal path in the bootstrap file.
     */
    public static function grant_defaults() {
        foreach ( self::default_role_grants() as $role_name => $caps ) {
            $role = get_role( $role_name );
            if ( ! $role ) {
                continue;
            }
            foreach ( $caps as $cap ) {
                if ( ! $role->has_cap( $cap ) ) {
                    $role->add_cap( $cap );
                }
            }
        }
    }

    /**
     * Strip every CVS cap from every role. Called from the deactivator.
     */
    public static function revoke_all() {
        global $wp_roles;
        if ( ! isset( $wp_roles ) ) {
            return;
        }
        foreach ( $wp_roles->roles as $role_name => $info ) {
            $role = get_role( $role_name );
            if ( ! $role ) {
                continue;
            }
            foreach ( self::all() as $cap ) {
                if ( $role->has_cap( $cap ) ) {
                    $role->remove_cap( $cap );
                }
            }
        }
    }

    /**
     * Convenience guard for AJAX handlers and page renderers — replaces the
     * scattered `current_user_can( 'manage_options' )` checks. Calls wp_die
     * with a translated message when the current user lacks the cap.
     *
     * @param string $cap One of the constants on this class.
     */
    public static function require_cap( $cap ) {
        if ( ! current_user_can( $cap ) ) {
            wp_die(
                esc_html__( 'You do not have permission to perform this action.', 'campus-visit-scheduler' ),
                esc_html__( 'Permission denied', 'campus-visit-scheduler' ),
                array( 'response' => 403 )
            );
        }
    }
}
