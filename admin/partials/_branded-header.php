<?php
/**
 * Branded header — included at the top of every plugin admin page.
 * Renders the KalluriIT horizontal logo, the page title, an optional
 * subtitle (set $cvs_page_subtitle before include), and a soft accent bar.
 *
 * @package CampusVisitScheduler
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cvs_page_title    = isset( $cvs_page_title ) ? $cvs_page_title : __( 'Campus Visits', 'campus-visit-scheduler' );
$cvs_page_subtitle = isset( $cvs_page_subtitle ) ? $cvs_page_subtitle : '';
$cvs_logo_url      = CVS_PLUGIN_URL . 'assets/images/kalluriit-logo-horizontal.png';
?>
<div class="cvs-brand-header">
    <div class="cvs-brand-header__inner">
        <div class="cvs-brand-header__title">
            <h1 class="cvs-brand-header__h1"><?php echo esc_html( $cvs_page_title ); ?></h1>
            <?php if ( ! empty( $cvs_page_subtitle ) ) : ?>
                <p class="cvs-brand-header__subtitle"><?php echo esc_html( $cvs_page_subtitle ); ?></p>
            <?php endif; ?>
        </div>
        <a href="https://kalluriit.com.au" target="_blank" rel="noopener noreferrer" class="cvs-brand-header__logo" aria-label="<?php esc_attr_e( 'KalluriIT — Your Lifeline in IT', 'campus-visit-scheduler' ); ?>">
            <img src="<?php echo esc_url( $cvs_logo_url ); ?>" alt="KalluriIT" />
        </a>
    </div>
    <div class="cvs-brand-header__accent"></div>
</div>
