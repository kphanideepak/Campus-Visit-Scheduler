<?php
/**
 * Form Fields settings tab
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$builtin_fields = CVS_Form_Fields::get_builtin_fields();
$custom_fields  = CVS_Form_Fields::get_custom_fields();
$field_types    = CVS_Form_Fields::get_field_types();
?>

<div class="cvs-form-fields-settings">
    <h2><?php esc_html_e( 'Form Fields', 'campus-visit-scheduler' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Configure which fields appear on the booking form. Core fields (date, time, group size, name, email, phone) are always shown.', 'campus-visit-scheduler' ); ?>
    </p>

    <!-- Section A: Built-in Optional Fields -->
    <h3><?php esc_html_e( 'Built-in Optional Fields', 'campus-visit-scheduler' ); ?></h3>
    <p class="description">
        <?php esc_html_e( 'Toggle these fields on or off. They use existing database columns.', 'campus-visit-scheduler' ); ?>
    </p>

    <table class="widefat striped cvs-builtin-fields-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Field', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Type', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Enabled', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $builtin_fields as $field ) : ?>
                <tr>
                    <td><?php echo esc_html( $field['label'] ); ?></td>
                    <td><?php echo esc_html( isset( $field_types[ $field['field_type'] ] ) ? $field_types[ $field['field_type'] ] : $field['field_type'] ); ?></td>
                    <td>
                        <label class="cvs-toggle-switch">
                            <input type="checkbox"
                                   class="cvs-toggle-builtin-field"
                                   data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
                                   <?php checked( ! empty( $field['enabled'] ) ); ?>>
                            <span class="cvs-toggle-label">
                                <?php echo ! empty( $field['enabled'] ) ? esc_html__( 'On', 'campus-visit-scheduler' ) : esc_html__( 'Off', 'campus-visit-scheduler' ); ?>
                            </span>
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <!-- Section B: Custom Fields -->
    <h3><?php esc_html_e( 'Custom Fields', 'campus-visit-scheduler' ); ?></h3>
    <p class="description">
        <?php esc_html_e( 'Add your own fields to the booking form. Custom field values are stored separately and available in email templates.', 'campus-visit-scheduler' ); ?>
    </p>

    <table class="widefat striped cvs-custom-fields-table" id="cvs-custom-fields-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Label', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Type', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Required', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Order', 'campus-visit-scheduler' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'campus-visit-scheduler' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $custom_fields ) ) : ?>
                <tr class="no-items">
                    <td colspan="5"><?php esc_html_e( 'No custom fields yet. Add one below.', 'campus-visit-scheduler' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $custom_fields as $field ) : ?>
                    <tr data-field-id="<?php echo esc_attr( $field['id'] ); ?>">
                        <td class="cvs-field-label"><?php echo esc_html( $field['label'] ); ?></td>
                        <td><?php echo esc_html( isset( $field_types[ $field['field_type'] ] ) ? $field_types[ $field['field_type'] ] : $field['field_type'] ); ?></td>
                        <td>
                            <?php if ( ! empty( $field['required'] ) ) : ?>
                                <span class="cvs-required-badge"><?php esc_html_e( 'Required', 'campus-visit-scheduler' ); ?></span>
                            <?php else : ?>
                                <span class="cvs-optional-badge"><?php esc_html_e( 'Optional', 'campus-visit-scheduler' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="button button-small cvs-reorder-field" data-field-id="<?php echo esc_attr( $field['id'] ); ?>" data-direction="up" title="<?php esc_attr_e( 'Move Up', 'campus-visit-scheduler' ); ?>">&#9650;</button>
                            <button type="button" class="button button-small cvs-reorder-field" data-field-id="<?php echo esc_attr( $field['id'] ); ?>" data-direction="down" title="<?php esc_attr_e( 'Move Down', 'campus-visit-scheduler' ); ?>">&#9660;</button>
                        </td>
                        <td>
                            <button type="button" class="button button-small cvs-edit-custom-field"
                                    data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
                                    data-label="<?php echo esc_attr( $field['label'] ); ?>"
                                    data-field-type="<?php echo esc_attr( $field['field_type'] ); ?>"
                                    data-required="<?php echo esc_attr( ! empty( $field['required'] ) ? '1' : '0' ); ?>"
                                    data-placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                                    data-max-length="<?php echo esc_attr( $field['max_length'] ); ?>"
                                    data-options="<?php echo esc_attr( CVS_Form_Fields::options_to_string( $field['options'] ) ); ?>">
                                <?php esc_html_e( 'Edit', 'campus-visit-scheduler' ); ?>
                            </button>
                            <button type="button" class="button button-small button-link-delete cvs-delete-custom-field" data-field-id="<?php echo esc_attr( $field['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'campus-visit-scheduler' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Add Custom Field Form -->
    <div id="cvs-custom-field-form-container" class="cvs-custom-field-form">
        <h4 id="cvs-custom-field-form-title"><?php esc_html_e( 'Add Custom Field', 'campus-visit-scheduler' ); ?></h4>
        <form id="cvs-custom-field-form">
            <input type="hidden" id="cvs-edit-field-id" value="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cvs-cf-label"><?php esc_html_e( 'Label', 'campus-visit-scheduler' ); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="cvs-cf-label" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cvs-cf-field-type"><?php esc_html_e( 'Field Type', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <select id="cvs-cf-field-type">
                            <?php foreach ( $field_types as $type_value => $type_label ) : ?>
                                <option value="<?php echo esc_attr( $type_value ); ?>"><?php echo esc_html( $type_label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cvs-cf-required"><?php esc_html_e( 'Required', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="cvs-cf-required">
                            <?php esc_html_e( 'This field is required', 'campus-visit-scheduler' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cvs-cf-placeholder"><?php esc_html_e( 'Placeholder', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="cvs-cf-placeholder" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cvs-cf-max-length"><?php esc_html_e( 'Max Length', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="cvs-cf-max-length" value="255" min="1" max="10000" class="small-text">
                    </td>
                </tr>
                <tr id="cvs-cf-options-row" style="display: none;">
                    <th scope="row">
                        <label for="cvs-cf-options"><?php esc_html_e( 'Options', 'campus-visit-scheduler' ); ?></label>
                    </th>
                    <td>
                        <textarea id="cvs-cf-options" rows="5" class="large-text"></textarea>
                        <p class="description">
                            <?php esc_html_e( 'One option per line. Use "value|Label" format or just "Label" (label becomes value).', 'campus-visit-scheduler' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" id="cvs-cf-submit-btn" class="button button-primary">
                    <?php esc_html_e( 'Add Field', 'campus-visit-scheduler' ); ?>
                </button>
                <button type="button" id="cvs-cf-cancel-btn" class="button" style="display: none;">
                    <?php esc_html_e( 'Cancel', 'campus-visit-scheduler' ); ?>
                </button>
            </p>
        </form>
    </div>
</div>

<style>
.cvs-form-fields-settings {
    max-width: 900px;
}
.cvs-builtin-fields-table,
.cvs-custom-fields-table {
    margin-top: 15px;
}
.cvs-builtin-fields-table .no-items td,
.cvs-custom-fields-table .no-items td {
    text-align: center;
    padding: 20px;
    color: #666;
}
.cvs-toggle-switch {
    cursor: pointer;
}
.cvs-toggle-label {
    margin-left: 5px;
}
.cvs-required-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
    background: #fef3cd;
    color: #856404;
}
.cvs-optional-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
    background: #e9ecef;
    color: #6c757d;
}
.cvs-custom-field-form {
    margin-top: 20px;
    padding: 20px;
    background: #f6f7f7;
    border: 1px solid #dcdcde;
}
.cvs-custom-field-form h4 {
    margin-top: 0;
}
.cvs-custom-field-form .form-table th {
    width: 120px;
    padding: 10px 10px 10px 0;
}
.cvs-custom-field-form .form-table td {
    padding: 10px 0;
}
.cvs-reorder-field {
    padding: 0 6px !important;
    min-height: 24px !important;
    line-height: 22px !important;
}
</style>
