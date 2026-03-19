<?php
/**
 * Form Fields settings tab — Visual Form Builder
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$sections    = CVS_Form_Sections::get_sections();
$all_fields  = CVS_Form_Fields::get_fields();
$field_types = CVS_Form_Fields::get_field_types();

// Group fields by section
$fields_by_section = array();
foreach ( $sections as $section ) {
    $fields_by_section[ $section['id'] ] = array();
}
foreach ( $all_fields as $field ) {
    $section_id = isset( $field['section'] ) ? $field['section'] : 'additional';
    if ( ! isset( $fields_by_section[ $section_id ] ) ) {
        $fields_by_section[ $section_id ] = array();
    }
    $fields_by_section[ $section_id ][] = $field;
}
?>

<div class="cvs-form-builder-wrap">
    <h2><?php esc_html_e( 'Form Builder', 'campus-visit-scheduler' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Drag sections and fields to reorder. Core fields (date, time, group size, name, email, phone) are always shown and cannot be moved here.', 'campus-visit-scheduler' ); ?>
    </p>

    <div class="cvs-form-builder-status" id="cvs-builder-status" style="display: none;">
        <span class="cvs-status-text"></span>
    </div>

    <!-- Visual Form Builder -->
    <div id="cvs-sections-list" class="cvs-sections-list">
        <?php foreach ( $sections as $section ) : ?>
            <div class="cvs-section-card" data-section-id="<?php echo esc_attr( $section['id'] ); ?>" data-section-type="<?php echo esc_attr( $section['type'] ); ?>">
                <div class="cvs-section-header">
                    <span class="cvs-drag-handle cvs-section-drag" title="<?php esc_attr_e( 'Drag to reorder section', 'campus-visit-scheduler' ); ?>">&#9776;</span>
                    <div class="cvs-section-title-wrap">
                        <h3 class="cvs-section-title"><?php echo esc_html( $section['label'] ); ?></h3>
                        <?php if ( ! empty( $section['description'] ) ) : ?>
                            <span class="cvs-section-desc"><?php echo esc_html( $section['description'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="cvs-section-field-count">
                        <?php
                        $count = count( $fields_by_section[ $section['id'] ] );
                        printf(
                            /* translators: %d: number of fields */
                            esc_html( _n( '%d field', '%d fields', $count, 'campus-visit-scheduler' ) ),
                            esc_html( $count )
                        );
                        ?>
                    </span>
                    <span class="cvs-section-actions">
                        <button type="button" class="button button-small cvs-edit-section"
                                data-section-id="<?php echo esc_attr( $section['id'] ); ?>"
                                data-label="<?php echo esc_attr( $section['label'] ); ?>"
                                data-description="<?php echo esc_attr( $section['description'] ); ?>"
                                title="<?php esc_attr_e( 'Edit Section', 'campus-visit-scheduler' ); ?>">
                            <span class="dashicons dashicons-edit" style="font-size: 14px; line-height: 26px; width: 14px; height: 14px;"></span>
                        </button>
                        <?php if ( 'custom' === $section['type'] ) : ?>
                            <button type="button" class="button button-small button-link-delete cvs-delete-section"
                                    data-section-id="<?php echo esc_attr( $section['id'] ); ?>"
                                    title="<?php esc_attr_e( 'Delete Section', 'campus-visit-scheduler' ); ?>">
                                <span class="dashicons dashicons-trash" style="font-size: 14px; line-height: 26px; width: 14px; height: 14px;"></span>
                            </button>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="cvs-fields-list" data-section-id="<?php echo esc_attr( $section['id'] ); ?>">
                    <?php if ( ! empty( $fields_by_section[ $section['id'] ] ) ) : ?>
                        <?php foreach ( $fields_by_section[ $section['id'] ] as $field ) : ?>
                            <div class="cvs-field-item <?php echo empty( $field['enabled'] ) ? 'cvs-field-disabled' : ''; ?>"
                                 data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
                                 data-field-type-key="<?php echo esc_attr( $field['type'] ); ?>">
                                <span class="cvs-drag-handle cvs-field-drag" title="<?php esc_attr_e( 'Drag to reorder or move to another section', 'campus-visit-scheduler' ); ?>">&#9776;</span>
                                <span class="cvs-field-label"><?php echo esc_html( $field['label'] ); ?></span>
                                <span class="cvs-field-type-badge"><?php echo esc_html( isset( $field_types[ $field['field_type'] ] ) ? $field_types[ $field['field_type'] ] : $field['field_type'] ); ?></span>
                                <?php if ( ! empty( $field['required'] ) ) : ?>
                                    <span class="cvs-field-required-badge"><?php esc_html_e( 'Required', 'campus-visit-scheduler' ); ?></span>
                                <?php endif; ?>
                                <span class="cvs-field-item-actions">
                                    <label class="cvs-field-toggle" title="<?php esc_attr_e( 'Toggle field visibility', 'campus-visit-scheduler' ); ?>">
                                        <input type="checkbox"
                                               class="cvs-toggle-field-enabled"
                                               data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
                                               data-field-type-key="<?php echo esc_attr( $field['type'] ); ?>"
                                               <?php checked( ! empty( $field['enabled'] ) ); ?>>
                                    </label>
                                    <button type="button" class="button button-small cvs-edit-field"
                                            data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
                                            data-label="<?php echo esc_attr( $field['label'] ); ?>"
                                            data-field-type="<?php echo esc_attr( $field['field_type'] ); ?>"
                                            data-field-type-key="<?php echo esc_attr( $field['type'] ); ?>"
                                            data-required="<?php echo esc_attr( ! empty( $field['required'] ) ? '1' : '0' ); ?>"
                                            data-placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                                            data-max-length="<?php echo esc_attr( $field['max_length'] ); ?>"
                                            data-options="<?php echo esc_attr( CVS_Form_Fields::options_to_string( $field['options'] ) ); ?>"
                                            data-section="<?php echo esc_attr( isset( $field['section'] ) ? $field['section'] : 'additional' ); ?>"
                                            title="<?php esc_attr_e( 'Edit Field', 'campus-visit-scheduler' ); ?>">
                                        <span class="dashicons dashicons-edit" style="font-size: 14px; line-height: 26px; width: 14px; height: 14px;"></span>
                                    </button>
                                    <?php if ( 'custom' === $field['type'] ) : ?>
                                        <button type="button" class="button button-small button-link-delete cvs-delete-field"
                                                data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
                                                title="<?php esc_attr_e( 'Delete Field', 'campus-visit-scheduler' ); ?>">
                                            <span class="dashicons dashicons-trash" style="font-size: 14px; line-height: 26px; width: 14px; height: 14px;"></span>
                                        </button>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="cvs-fields-empty-state" <?php echo ! empty( $fields_by_section[ $section['id'] ] ) ? 'style="display:none;"' : ''; ?>>
                        <?php esc_html_e( 'Drag fields here or add a new field', 'campus-visit-scheduler' ); ?>
                    </div>
                </div>

                <div class="cvs-section-footer">
                    <button type="button" class="button button-small cvs-add-field-btn" data-section="<?php echo esc_attr( $section['id'] ); ?>">
                        + <?php esc_html_e( 'Add Field', 'campus-visit-scheduler' ); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" id="cvs-add-section-btn" class="button button-secondary">
        + <?php esc_html_e( 'Add Section', 'campus-visit-scheduler' ); ?>
    </button>
</div>

<!-- Section Modal -->
<div id="cvs-section-modal" class="cvs-modal" style="display: none;">
    <div class="cvs-modal-overlay"></div>
    <div class="cvs-modal-content">
        <div class="cvs-modal-header">
            <h3 id="cvs-section-modal-title"><?php esc_html_e( 'Add Section', 'campus-visit-scheduler' ); ?></h3>
            <button type="button" class="cvs-modal-close">&times;</button>
        </div>
        <form id="cvs-section-form">
            <input type="hidden" id="cvs-section-edit-id" value="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cvs-section-label"><?php esc_html_e( 'Section Name', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="cvs-section-label" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cvs-section-description"><?php esc_html_e( 'Description', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="cvs-section-description" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Optional subtitle shown below the section heading.', 'campus-visit-scheduler' ); ?></p>
                    </td>
                </tr>
            </table>
            <div class="cvs-modal-footer">
                <button type="submit" id="cvs-section-submit-btn" class="button button-primary">
                    <?php esc_html_e( 'Add Section', 'campus-visit-scheduler' ); ?>
                </button>
                <button type="button" class="button cvs-modal-cancel"><?php esc_html_e( 'Cancel', 'campus-visit-scheduler' ); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Field Modal (Polished with Live Preview) -->
<div id="cvs-field-modal" class="cvs-modal" style="display: none;">
    <div class="cvs-modal-overlay"></div>
    <div class="cvs-modal-content cvs-modal-wide">
        <div class="cvs-modal-header">
            <h3 id="cvs-field-modal-title"><?php esc_html_e( 'Add Field', 'campus-visit-scheduler' ); ?></h3>
            <button type="button" class="cvs-modal-close">&times;</button>
        </div>
        <form id="cvs-field-form">
            <input type="hidden" id="cvs-field-edit-id" value="">
            <input type="hidden" id="cvs-field-section" value="">
            <input type="hidden" id="cvs-field-type-key" value="">

            <div class="cvs-modal-split">
                <!-- Left: Field Settings -->
                <div class="cvs-modal-settings">
                    <div class="cvs-field-group">
                        <div class="cvs-field-group-title"><?php esc_html_e( 'Basic', 'campus-visit-scheduler' ); ?></div>
                        <div class="cvs-field-row">
                            <label for="cvs-field-label" class="cvs-field-row-label"><?php esc_html_e( 'Label', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                            <input type="text" id="cvs-field-label" class="cvs-field-input" required placeholder="<?php esc_attr_e( 'e.g. Dietary Requirements', 'campus-visit-scheduler' ); ?>">
                        </div>
                        <div class="cvs-field-row" id="cvs-field-type-row">
                            <label for="cvs-field-type" class="cvs-field-row-label"><?php esc_html_e( 'Type', 'campus-visit-scheduler' ); ?></label>
                            <div class="cvs-field-type-grid">
                                <?php
                                $type_icons = array(
                                    'text'     => 'Aa',
                                    'textarea' => '&#9776;',
                                    'select'   => '&#9662;',
                                    'checkbox' => '&#9745;',
                                    'number'   => '#',
                                );
                                foreach ( $field_types as $type_value => $type_label ) :
                                ?>
                                    <label class="cvs-type-option" data-type="<?php echo esc_attr( $type_value ); ?>">
                                        <input type="radio" name="cvs-field-type-radio" value="<?php echo esc_attr( $type_value ); ?>" <?php checked( 'text', $type_value ); ?>>
                                        <span class="cvs-type-icon"><?php echo isset( $type_icons[ $type_value ] ) ? $type_icons[ $type_value ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                        <span class="cvs-type-name"><?php echo esc_html( $type_label ); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <!-- Hidden select for backward compatibility -->
                            <select id="cvs-field-type" style="display:none;">
                                <?php foreach ( $field_types as $type_value => $type_label ) : ?>
                                    <option value="<?php echo esc_attr( $type_value ); ?>"><?php echo esc_html( $type_label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="cvs-field-row">
                            <label for="cvs-field-section-select" class="cvs-field-row-label"><?php esc_html_e( 'Section', 'campus-visit-scheduler' ); ?></label>
                            <select id="cvs-field-section-select" class="cvs-field-input">
                                <?php foreach ( $sections as $section ) : ?>
                                    <option value="<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['label'] ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="cvs-field-group">
                        <div class="cvs-field-group-title"><?php esc_html_e( 'Behaviour', 'campus-visit-scheduler' ); ?></div>
                        <div class="cvs-field-row cvs-field-row-inline">
                            <label class="cvs-toggle-modern">
                                <input type="checkbox" id="cvs-field-required">
                                <span class="cvs-toggle-slider"></span>
                            </label>
                            <span class="cvs-field-row-label-inline"><?php esc_html_e( 'Required field', 'campus-visit-scheduler' ); ?></span>
                        </div>
                        <div class="cvs-field-row">
                            <label for="cvs-field-placeholder" class="cvs-field-row-label"><?php esc_html_e( 'Placeholder', 'campus-visit-scheduler' ); ?></label>
                            <input type="text" id="cvs-field-placeholder" class="cvs-field-input" placeholder="<?php esc_attr_e( 'Hint text shown in empty field', 'campus-visit-scheduler' ); ?>">
                        </div>
                        <div class="cvs-field-row">
                            <label for="cvs-field-max-length" class="cvs-field-row-label"><?php esc_html_e( 'Max Length', 'campus-visit-scheduler' ); ?></label>
                            <input type="number" id="cvs-field-max-length" value="255" min="1" max="10000" class="cvs-field-input cvs-field-input-short">
                        </div>
                    </div>

                    <div class="cvs-field-group" id="cvs-field-options-group" style="display: none;">
                        <div class="cvs-field-group-title"><?php esc_html_e( 'Dropdown Options', 'campus-visit-scheduler' ); ?></div>
                        <div class="cvs-field-row">
                            <textarea id="cvs-field-options" rows="4" class="cvs-field-input cvs-field-textarea" placeholder="<?php esc_attr_e( "Option 1\nOption 2\nOption 3", 'campus-visit-scheduler' ); ?>"></textarea>
                            <p class="cvs-field-hint"><?php esc_html_e( 'One per line. Use "value|Label" for custom values.', 'campus-visit-scheduler' ); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Right: Live Preview -->
                <div class="cvs-modal-preview">
                    <div class="cvs-preview-label"><?php esc_html_e( 'Preview', 'campus-visit-scheduler' ); ?></div>
                    <div class="cvs-preview-card">
                        <div id="cvs-field-preview" class="cvs-preview-field">
                            <label class="cvs-preview-field-label">
                                <span id="cvs-preview-label-text"><?php esc_html_e( 'Field Label', 'campus-visit-scheduler' ); ?></span>
                                <span id="cvs-preview-required" class="cvs-preview-required" style="display:none;"> *</span>
                            </label>
                            <div id="cvs-preview-input-wrap">
                                <input type="text" id="cvs-preview-input" class="cvs-preview-input" disabled placeholder="">
                            </div>
                        </div>
                    </div>
                    <p class="cvs-preview-note"><?php esc_html_e( 'This is how the field will appear on the booking form.', 'campus-visit-scheduler' ); ?></p>
                </div>
            </div>

            <div class="cvs-modal-footer">
                <button type="button" class="button cvs-modal-cancel"><?php esc_html_e( 'Cancel', 'campus-visit-scheduler' ); ?></button>
                <button type="submit" id="cvs-field-submit-btn" class="button button-primary button-hero-sm">
                    <?php esc_html_e( 'Add Field', 'campus-visit-scheduler' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>
