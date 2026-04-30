<?php
/**
 * Branded footer — small "Powered by KalluriIT" attribution shown at the
 * bottom of plugin pages. Linked to kalluriit.com.au, opens in a new tab.
 *
 * @package CampusVisitScheduler
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="cvs-brand-footer">
    <span class="cvs-brand-footer__chip">
        <?php
        printf(
            /* translators: 1: KalluriIT brand link */
            esc_html__( 'Powered by %s — Your Lifeline in IT', 'campus-visit-scheduler' ),
            '<a href="https://kalluriit.com.au" target="_blank" rel="noopener noreferrer">KalluriIT</a>'
        );
        ?>
    </span>
    <span class="cvs-brand-footer__version">
        <?php
        printf(
            /* translators: %s: plugin version */
            esc_html__( 'Campus Visit Scheduler v%s', 'campus-visit-scheduler' ),
            esc_html( CVS_VERSION )
        );
        ?>
    </span>
</div>
