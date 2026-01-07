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
        </div>

        <div class="cvs-form-section">
            <h3><?php esc_html_e( 'Child Information (Optional)', 'campus-visit-scheduler' ); ?></h3>

            <div class="cvs-form-row">
                <div class="cvs-form-field">
                    <label for="cvs-child-name"><?php esc_html_e( 'Child\'s Name', 'campus-visit-scheduler' ); ?></label>
                    <input type="text" id="cvs-child-name" name="child_name" maxlength="255">
                </div>

                <div class="cvs-form-field">
                    <label for="cvs-year-level"><?php esc_html_e( 'Intended Year Level', 'campus-visit-scheduler' ); ?></label>
                    <select id="cvs-year-level" name="year_level">
                        <?php foreach ( $year_levels as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="cvs-form-row">
                <div class="cvs-form-field cvs-full-width">
                    <label for="cvs-special-requirements"><?php esc_html_e( 'Special Requirements or Notes', 'campus-visit-scheduler' ); ?></label>
                    <textarea id="cvs-special-requirements" name="special_requirements" rows="3" maxlength="1000"></textarea>
                </div>
            </div>
        </div>

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
