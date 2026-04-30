<?php
/**
 * Tab intro card — shows a friendly introduction at the top of each settings
 * tab. Set $cvs_tab_intro_title and $cvs_tab_intro_body before include. Optional
 * $cvs_tab_intro_tip for a bordered tip box.
 *
 * @package CampusVisitScheduler
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cvs_tab_intro_title = isset( $cvs_tab_intro_title ) ? $cvs_tab_intro_title : '';
$cvs_tab_intro_body  = isset( $cvs_tab_intro_body ) ? $cvs_tab_intro_body : '';
$cvs_tab_intro_tip   = isset( $cvs_tab_intro_tip ) ? $cvs_tab_intro_tip : '';

if ( '' === $cvs_tab_intro_title && '' === $cvs_tab_intro_body ) {
    return;
}
?>
<div class="cvs-tab-intro">
    <?php if ( '' !== $cvs_tab_intro_title ) : ?>
        <h2 class="cvs-tab-intro__title"><?php echo esc_html( $cvs_tab_intro_title ); ?></h2>
    <?php endif; ?>
    <?php if ( '' !== $cvs_tab_intro_body ) : ?>
        <p class="cvs-tab-intro__body"><?php echo wp_kses_post( $cvs_tab_intro_body ); ?></p>
    <?php endif; ?>
    <?php if ( '' !== $cvs_tab_intro_tip ) : ?>
        <div class="cvs-tab-intro__tip">
            <span class="cvs-tab-intro__tip-icon" aria-hidden="true">💡</span>
            <span><?php echo wp_kses_post( $cvs_tab_intro_tip ); ?></span>
        </div>
    <?php endif; ?>
</div>
