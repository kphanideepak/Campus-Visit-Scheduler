<?php
/**
 * Booking form template
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$year_levels = CVS_Helpers::get_year_levels();
$max_group_size = (int) get_option( 'cvs_max_group_size', 6 );

// Get enabled custom fields grouped by section for core section injection.
$all_enabled_fields    = CVS_Form_Fields::get_enabled_fields();
$core_section_fields   = array(
    'date_time'    => array(),
    'group_size'   => array(),
    'your_details' => array(),
);
foreach ( $all_enabled_fields as $f ) {
    $sec = isset( $f['section'] ) ? $f['section'] : 'additional';
    if ( isset( $core_section_fields[ $sec ] ) ) {
        $core_section_fields[ $sec ][] = $f;
    }
}
?>

<div class="cvs-booking-wrapper">
    <form id="cvs-booking-form" class="cvs-booking-form">
        <?php wp_nonce_field( 'cvs_public_nonce', 'cvs_nonce' ); ?>

        <div class="cvs-form-section">
            <h3><?php esc_html_e( 'Select Tour Date & Time', 'campus-visit-scheduler' ); ?></h3>

            <div class="cvs-form-row">
                <div class="cvs-form-field cvs-date-picker-field">
                    <label for="cvs-tour-date"><?php esc_html_e( 'Tour Date', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <select id="cvs-tour-date" name="tour_date" required>
                        <option value=""><?php esc_html_e( 'Select a date...', 'campus-visit-scheduler' ); ?></option>
                        <?php foreach ( $available_dates as $date => $date_data ) : ?>
                            <?php
                            $has_available = false;
                            foreach ( $date_data['slots'] as $slot ) {
                                if ( $slot['available'] ) {
                                    $has_available = true;
                                    break;
                                }
                            }
                            if ( ! $has_available ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo esc_attr( $date ); ?>">
                                <?php echo esc_html( CVS_Helpers::format_date( $date ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cvs-form-field cvs-time-picker-field">
                    <label for="cvs-tour-time"><?php esc_html_e( 'Tour Time', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <div id="cvs-time-slots" class="cvs-time-slots">
                        <p class="cvs-select-date-prompt"><?php esc_html_e( 'Please select a date first.', 'campus-visit-scheduler' ); ?></p>
                    </div>
                    <input type="hidden" id="cvs-tour-time" name="tour_time" required>
                </div>
            </div>
            <?php foreach ( $core_section_fields['date_time'] as $field ) : ?>
                <?php CVS_Form_Fields::render_field( $field ); ?>
            <?php endforeach; ?>
        </div>

        <div class="cvs-form-section">
            <h3><?php esc_html_e( 'Group Size', 'campus-visit-scheduler' ); ?></h3>

            <div class="cvs-form-row">
                <div class="cvs-form-field">
                    <label for="cvs-adults"><?php esc_html_e( 'Number of Adults', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <select id="cvs-adults" name="adults" required>
                        <?php for ( $i = 1; $i <= $max_group_size; $i++ ) : ?>
                            <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $i, 1 ); ?>><?php echo esc_html( $i ); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="cvs-form-field">
                    <label for="cvs-children"><?php esc_html_e( 'Number of Children', 'campus-visit-scheduler' ); ?></label>
                    <select id="cvs-children" name="children">
                        <?php for ( $i = 0; $i <= $max_group_size; $i++ ) : ?>
                            <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $i, 0 ); ?>><?php echo esc_html( $i ); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="cvs-form-field cvs-total-field">
                    <label><?php esc_html_e( 'Total Group Size', 'campus-visit-scheduler' ); ?></label>
                    <div id="cvs-total-group" class="cvs-total-group">1</div>
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
            <?php foreach ( $core_section_fields['group_size'] as $field ) : ?>
                <?php CVS_Form_Fields::render_field( $field ); ?>
            <?php endforeach; ?>
        </div>

        <div class="cvs-form-section">
            <h3><?php esc_html_e( 'Your Details', 'campus-visit-scheduler' ); ?></h3>

            <div class="cvs-form-row">
                <div class="cvs-form-field">
                    <label for="cvs-parent-name"><?php esc_html_e( 'Full Name', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <input type="text" id="cvs-parent-name" name="parent_name" required maxlength="255">
                </div>
            </div>

            <div class="cvs-form-row">
                <div class="cvs-form-field">
                    <label for="cvs-email"><?php esc_html_e( 'Email Address', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <input type="email" id="cvs-email" name="email" required maxlength="255">
                </div>

                <div class="cvs-form-field">
                    <label for="cvs-phone"><?php esc_html_e( 'Phone Number', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    <input type="tel" id="cvs-phone" name="phone" required maxlength="50">
                </div>
            </div>
            <?php foreach ( $core_section_fields['your_details'] as $field ) : ?>
                <?php CVS_Form_Fields::render_field( $field ); ?>
            <?php endforeach; ?>
        </div>

        <?php
        $enabled_fields = CVS_Form_Fields::get_enabled_fields();
        if ( ! empty( $enabled_fields ) ) :
            $sections = CVS_Form_Sections::get_sections();

            foreach ( $sections as $section ) :
                // Skip core sections — their custom fields are already rendered inline above.
                if ( 'core' === $section['type'] ) :
                    continue;
                endif;

                $section_fields = array_filter( $enabled_fields, function( $f ) use ( $section ) {
                    $field_section = isset( $f['section'] ) ? $f['section'] : 'additional';
                    return $field_section === $section['id'];
                });

                if ( empty( $section_fields ) ) :
                    continue;
                endif;
        ?>
                <div class="cvs-form-section">
                    <h3><?php echo esc_html( $section['label'] ); ?></h3>
                    <?php if ( ! empty( $section['description'] ) ) : ?>
                        <p class="cvs-section-description"><?php echo esc_html( $section['description'] ); ?></p>
                    <?php endif; ?>
                    <?php foreach ( $section_fields as $field ) : ?>
                        <?php CVS_Form_Fields::render_field( $field ); ?>
                    <?php endforeach; ?>
                </div>
        <?php
            endforeach;
        endif;
        ?>

        <div class="cvs-form-actions">
            <button type="submit" id="cvs-submit-booking" class="cvs-btn cvs-btn-primary">
                <?php esc_html_e( 'Book Tour', 'campus-visit-scheduler' ); ?>
            </button>
        </div>

        <div id="cvs-form-messages" class="cvs-form-messages"></div>
    </form>

    <div id="cvs-booking-confirmation" class="cvs-booking-confirmation" style="display: none;">
        <!-- Confirmation content will be inserted here via JavaScript -->
    </div>
</div>
