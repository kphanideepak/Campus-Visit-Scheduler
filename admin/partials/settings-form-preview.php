<?php
/**
 * Form Preview tab — shows a read-only preview of the complete booking form
 *
 * Uses the same class names and structure as the front-end booking form
 * (public/partials/booking-form.php) and enqueues cvs-public.css so the
 * preview is genuinely "how visitors see it". A small admin-context
 * override block in cvs-admin.css scopes a couple of WP-admin-only fixes
 * (e.g. neutralising the .wp-core-ui select chevron) to .cvs-form-preview-wrap.
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$max_group_size     = (int) get_option( 'cvs_max_group_size', 6 );
$all_enabled_fields = CVS_Form_Fields::get_enabled_fields();
$sections           = CVS_Form_Sections::get_sections();

// Group custom fields by section
$fields_by_section = array();
foreach ( $all_enabled_fields as $f ) {
    $sec = isset( $f['section'] ) ? $f['section'] : 'additional';
    if ( ! isset( $fields_by_section[ $sec ] ) ) {
        $fields_by_section[ $sec ] = array();
    }
    $fields_by_section[ $sec ][] = $f;
}
?>

<div class="cvs-form-preview-wrap">
    <div class="cvs-booking-wrapper">

        <!-- Core: Date & Time -->
        <div class="cvs-form-section">
            <h3><?php esc_html_e( 'Select Tour Date & Time', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-form-row">
                <div class="cvs-form-field">
                    <label><?php esc_html_e( 'Tour Date', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <select disabled>
                        <option><?php esc_html_e( 'Select a date...', 'campus-visit-scheduler' ); ?></option>
                    </select>
                </div>
                <div class="cvs-form-field">
                    <label><?php esc_html_e( 'Tour Time', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <p class="cvs-select-date-prompt"><?php esc_html_e( 'Please select a date first.', 'campus-visit-scheduler' ); ?></p>
                </div>
            </div>
            <?php if ( ! empty( $fields_by_section['date_time'] ) ) : ?>
                <?php foreach ( $fields_by_section['date_time'] as $field ) : ?>
                    <?php cvs_render_preview_field( $field ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Core: Group Size -->
        <div class="cvs-form-section">
            <h3><?php esc_html_e( 'Group Size', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-form-row">
                <div class="cvs-form-field">
                    <label><?php esc_html_e( 'Number of Adults', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <select disabled>
                        <option>1</option>
                    </select>
                </div>
                <div class="cvs-form-field">
                    <label><?php esc_html_e( 'Number of Children', 'campus-visit-scheduler' ); ?></label>
                    <select disabled>
                        <option>0</option>
                    </select>
                </div>
                <div class="cvs-form-field cvs-total-field">
                    <label><?php esc_html_e( 'Total Group Size', 'campus-visit-scheduler' ); ?></label>
                    <div class="cvs-total-group">1</div>
                    <p class="cvs-field-note">
                        <?php
                        printf(
                            /* translators: %d: maximum group size */
                            esc_html__( 'Maximum %d people per booking', 'campus-visit-scheduler' ),
                            esc_html( $max_group_size )
                        );
                        ?>
                    </p>
                </div>
            </div>
            <?php if ( ! empty( $fields_by_section['group_size'] ) ) : ?>
                <?php foreach ( $fields_by_section['group_size'] as $field ) : ?>
                    <?php cvs_render_preview_field( $field ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Core: Your Details -->
        <div class="cvs-form-section">
            <h3><?php esc_html_e( 'Your Details', 'campus-visit-scheduler' ); ?></h3>
            <div class="cvs-form-row">
                <div class="cvs-form-field cvs-full-width">
                    <label><?php esc_html_e( 'Full Name', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <input type="text" disabled>
                </div>
            </div>
            <div class="cvs-form-row">
                <div class="cvs-form-field">
                    <label><?php esc_html_e( 'Email Address', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <input type="email" disabled>
                </div>
                <div class="cvs-form-field">
                    <label><?php esc_html_e( 'Phone Number', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <input type="tel" disabled>
                </div>
            </div>
            <?php if ( ! empty( $fields_by_section['your_details'] ) ) : ?>
                <?php foreach ( $fields_by_section['your_details'] as $field ) : ?>
                    <?php cvs_render_preview_field( $field ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php
        // Non-core sections with enabled custom fields
        foreach ( $sections as $section ) :
            if ( 'core' === $section['type'] ) :
                continue;
            endif;

            $section_fields = isset( $fields_by_section[ $section['id'] ] ) ? $fields_by_section[ $section['id'] ] : array();
            if ( empty( $section_fields ) ) :
                continue;
            endif;
        ?>
            <div class="cvs-form-section">
                <h3><?php echo esc_html( $section['label'] ); ?></h3>
                <?php if ( ! empty( $section['description'] ) ) : ?>
                    <p class="cvs-field-note"><?php echo esc_html( $section['description'] ); ?></p>
                <?php endif; ?>
                <?php foreach ( $section_fields as $field ) : ?>
                    <?php cvs_render_preview_field( $field ); ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="cvs-form-actions">
            <button type="button" class="cvs-btn cvs-btn-primary" disabled>
                <?php esc_html_e( 'Book Tour', 'campus-visit-scheduler' ); ?>
            </button>
        </div>

    </div>
</div>

<?php
/**
 * Render a disabled preview of a custom form field using front-end class names
 * so cvs-public.css styles it identically to the visitor-facing form.
 *
 * @param array $field Field definition.
 */
function cvs_render_preview_field( $field ) {
    $required_span = ! empty( $field['required'] ) ? ' <span class="required">*</span>' : '';
    $placeholder   = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
    $is_textarea   = ( 'textarea' === $field['field_type'] );
    $field_class   = 'cvs-form-field' . ( $is_textarea ? ' cvs-full-width' : '' );
    ?>
    <div class="cvs-form-row">
        <div class="<?php echo esc_attr( $field_class ); ?>">
            <?php if ( 'checkbox' !== $field['field_type'] ) : ?>
                <label><?php echo esc_html( $field['label'] ); ?><?php echo $required_span; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
            <?php endif; ?>

            <?php switch ( $field['field_type'] ) :
                case 'textarea': ?>
                    <textarea disabled rows="3" placeholder="<?php echo esc_attr( $placeholder ); ?>"></textarea>
                    <?php break;
                case 'select': ?>
                    <select disabled>
                        <option><?php echo esc_html( $placeholder ? $placeholder : __( 'Select...', 'campus-visit-scheduler' ) ); ?></option>
                        <?php if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) : ?>
                            <?php foreach ( $field['options'] as $val => $label ) : ?>
                                <option><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php break;
                case 'checkbox': ?>
                    <label class="cvs-preview-checkbox-inline">
                        <input type="checkbox" disabled>
                        <span><?php echo esc_html( $field['label'] ); ?><?php echo $required_span; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    </label>
                    <?php break;
                case 'number': ?>
                    <input type="number" disabled placeholder="<?php echo esc_attr( $placeholder ? $placeholder : '0' ); ?>">
                    <?php break;
                default: ?>
                    <input type="text" disabled placeholder="<?php echo esc_attr( $placeholder ); ?>">
                    <?php break;
            endswitch; ?>
        </div>
    </div>
    <?php
}
?>
