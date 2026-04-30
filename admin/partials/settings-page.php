<?php
/**
 * Settings page template — branded header, tab nav, tab content, footer.
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Tab catalogue. The visible subset for the current user is filtered below
// based on capabilities — Editors with cvs_manage_dates only see Blackout
// Dates and Holiday Periods; Admins with cvs_manage_settings see everything.
$all_tabs = array(
    'general'       => __( 'General', 'campus-visit-scheduler' ),
    'form_fields'   => __( 'Form Builder', 'campus-visit-scheduler' ),
    'form_preview'  => __( 'Form Preview', 'campus-visit-scheduler' ),
    'tour_schedule' => __( 'Tour Schedule', 'campus-visit-scheduler' ),
    'blackout'      => __( 'Blackout Dates', 'campus-visit-scheduler' ),
    'holidays'      => __( 'Holiday Periods', 'campus-visit-scheduler' ),
    'notifications' => __( 'Notifications', 'campus-visit-scheduler' ),
    'emails'        => __( 'Email Templates', 'campus-visit-scheduler' ),
);
$visible_tab_ids = CVS_Capabilities::visible_settings_tabs();
$tabs            = array_intersect_key( $all_tabs, array_flip( $visible_tab_ids ) );

// Determine current tab. If the requested tab isn't in the visible set
// (e.g. an Editor following a deep-link to ?tab=general), fall back to
// the first allowed tab so they land somewhere they can actually use.
$requested_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
if ( '' !== $requested_tab && isset( $tabs[ $requested_tab ] ) ) {
    $current_tab = $requested_tab;
} else {
    $current_tab = ! empty( $visible_tab_ids ) ? reset( $visible_tab_ids ) : 'general';
}

// Friendly per-tab intro copy. Title + body + optional tip.
$tab_intros = array(
    'general' => array(
        'title' => __( 'General settings', 'campus-visit-scheduler' ),
        'body'  => __( 'Set the overall behaviour of the booking form — how far ahead families can book, the maximum group size, and whether the form is currently accepting bookings. These values apply across the whole site.', 'campus-visit-scheduler' ),
        'tip'   => __( 'Turning bookings off here disables the front-end form immediately. Use it for short pauses (e.g. during a busy enrolment week) without removing the page from your site.', 'campus-visit-scheduler' ),
    ),
    'form_fields' => array(
        'title' => __( 'Form Builder', 'campus-visit-scheduler' ),
        'body'  => __( 'Drag fields between sections, add custom questions, and choose which built-in fields to show or hide. Changes here update both the visitor-facing booking form and the booking detail screen for staff.', 'campus-visit-scheduler' ),
        'tip'   => __( 'Hover the small grip handle on a field to drag it. Switch to the Form Preview tab any time to see the live result without leaving WordPress.', 'campus-visit-scheduler' ),
    ),
    'form_preview' => array(
        'title' => __( 'Form Preview', 'campus-visit-scheduler' ),
        'body'  => __( 'A read-only view of exactly what visitors will see on the booking page. Every field is disabled — this is for sanity-checking layout, copy, and section order before sharing the page.', 'campus-visit-scheduler' ),
    ),
    'tour_schedule' => array(
        'title' => __( 'Tour schedule', 'campus-visit-scheduler' ),
        'body'  => __( 'Define which days and times tours run, plus how many families can attend each slot. Most schools settle on a single weekday — for example, Fridays at 9:30am led by a Principal Class team member.', 'campus-visit-scheduler' ),
        'tip'   => __( 'Capacity is per slot, not per day. Set it to match how many families a guide can comfortably take through at once (4–8 is typical).', 'campus-visit-scheduler' ),
    ),
    'blackout' => array(
        'title' => __( 'Blackout dates', 'campus-visit-scheduler' ),
        'body'  => __( 'Block specific dates from being available — perfect for one-off events, staff training days, or any single date you can\'t host tours.', 'campus-visit-scheduler' ),
        'tip'   => __( 'For multi-day stretches like school holidays or term breaks, use Holiday Periods instead — it\'s less repetitive than adding each date individually.', 'campus-visit-scheduler' ),
    ),
    'holidays' => array(
        'title' => __( 'Holiday periods', 'campus-visit-scheduler' ),
        'body'  => __( 'Set up recurring or one-off date ranges where tours don\'t run — school holidays, public-holiday long weekends, end-of-year shutdowns. Visitors won\'t see these dates in the date picker.', 'campus-visit-scheduler' ),
        'tip'   => __( 'Holiday periods are evaluated alongside Blackout Dates. If a date is covered by either, it\'s unavailable.', 'campus-visit-scheduler' ),
    ),
    'notifications' => array(
        'title' => __( 'Notification recipients', 'campus-visit-scheduler' ),
        'body'  => __( 'Choose who at the school gets notified when a new booking comes in or when a booking is cancelled. Add multiple recipients (Principal, Office Manager, etc.) — they\'ll all receive the same alert.', 'campus-visit-scheduler' ),
        'tip'   => __( 'These addresses only see internal staff alerts. The confirmation email to the parent is configured separately on the Email Templates tab.', 'campus-visit-scheduler' ),
    ),
    'emails' => array(
        'title' => __( 'Email templates', 'campus-visit-scheduler' ),
        'body'  => __( 'Edit the wording of the emails sent to parents (booking confirmation, cancellation, reminder) and to staff (new booking alert). Use placeholders like {{family_name}} and {{tour_date}} to personalise each message.', 'campus-visit-scheduler' ),
        'tip'   => __( 'Send a test email to yourself before saving a major change so you can see exactly how the placeholders render in a real inbox.', 'campus-visit-scheduler' ),
    ),
);

$cvs_page_title    = __( 'Settings', 'campus-visit-scheduler' );
$cvs_page_subtitle = __( 'Configure how your school manages tour bookings.', 'campus-visit-scheduler' );
include CVS_PLUGIN_DIR . 'admin/partials/_branded-header.php';
?>

<div class="wrap cvs-settings-wrap">

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
        // Render the warm intro card for the current tab.
        if ( isset( $tab_intros[ $current_tab ] ) ) {
            $intro                = $tab_intros[ $current_tab ];
            $cvs_tab_intro_title  = isset( $intro['title'] ) ? $intro['title'] : '';
            $cvs_tab_intro_body   = isset( $intro['body'] ) ? $intro['body'] : '';
            $cvs_tab_intro_tip    = isset( $intro['tip'] ) ? $intro['tip'] : '';
            include CVS_PLUGIN_DIR . 'admin/partials/_tab-intro.php';
            unset( $cvs_tab_intro_title, $cvs_tab_intro_body, $cvs_tab_intro_tip );
        }

        switch ( $current_tab ) {
            case 'form_fields':
                include 'settings-form-fields.php';
                break;
            case 'form_preview':
                include 'settings-form-preview.php';
                break;
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

<?php include CVS_PLUGIN_DIR . 'admin/partials/_branded-footer.php'; ?>
