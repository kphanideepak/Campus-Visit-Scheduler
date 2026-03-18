<?php
/**
 * Form fields management for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Form_Fields
 *
 * Central class for managing form field definitions and rendering
 */
class CVS_Form_Fields {

    /**
     * Option key for storing field definitions
     */
    const OPTION_KEY = 'cvs_form_fields';

    /**
     * Get all field definitions sorted by sort_order
     *
     * @return array Array of field definitions.
     */
    public static function get_fields() {
        $fields = get_option( self::OPTION_KEY, array() );

        if ( ! is_array( $fields ) ) {
            $fields = array();
        }

        usort( $fields, function( $a, $b ) {
            return ( (int) $a['sort_order'] ) - ( (int) $b['sort_order'] );
        });

        return $fields;
    }

    /**
     * Get only enabled field definitions
     *
     * @return array Array of enabled field definitions.
     */
    public static function get_enabled_fields() {
        return array_values( array_filter( self::get_fields(), function( $field ) {
            return ! empty( $field['enabled'] );
        }));
    }

    /**
     * Get built-in optional fields
     *
     * @return array Array of builtin_optional field definitions.
     */
    public static function get_builtin_fields() {
        return array_values( array_filter( self::get_fields(), function( $field ) {
            return 'builtin_optional' === $field['type'];
        }));
    }

    /**
     * Get custom fields
     *
     * @return array Array of custom field definitions.
     */
    public static function get_custom_fields() {
        return array_values( array_filter( self::get_fields(), function( $field ) {
            return 'custom' === $field['type'];
        }));
    }

    /**
     * Get a single field by ID
     *
     * @param string $id Field ID.
     * @return array|null Field definition or null if not found.
     */
    public static function get_field( $id ) {
        $fields = self::get_fields();

        foreach ( $fields as $field ) {
            if ( $field['id'] === $id ) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Add a new custom field
     *
     * @param array $data Field data (label, field_type, required, placeholder, max_length, options).
     * @return string|WP_Error Field ID on success, WP_Error on failure.
     */
    public static function add_field( $data ) {
        $fields = self::get_fields();

        // Generate slug from label
        $label = isset( $data['label'] ) ? sanitize_text_field( $data['label'] ) : '';
        if ( empty( $label ) ) {
            return new WP_Error( 'missing_label', __( 'Field label is required.', 'campus-visit-scheduler' ) );
        }

        $slug = sanitize_title( $label );
        $slug = str_replace( '-', '_', $slug );

        // Ensure unique slug
        $existing_ids = wp_list_pluck( $fields, 'id' );
        $original_slug = $slug;
        $counter = 2;
        while ( in_array( $slug, $existing_ids, true ) ) {
            $slug = $original_slug . '_' . $counter;
            $counter++;
        }

        // Determine sort_order (after last field)
        $max_sort = 0;
        foreach ( $fields as $field ) {
            if ( (int) $field['sort_order'] > $max_sort ) {
                $max_sort = (int) $field['sort_order'];
            }
        }

        $field_type = isset( $data['field_type'] ) ? sanitize_text_field( $data['field_type'] ) : 'text';
        $valid_types = array_keys( self::get_field_types() );
        if ( ! in_array( $field_type, $valid_types, true ) ) {
            $field_type = 'text';
        }

        // Parse options for select fields
        $options = array();
        if ( 'select' === $field_type && ! empty( $data['options'] ) ) {
            $options = self::parse_options_string( $data['options'] );
        }

        $new_field = array(
            'id'          => $slug,
            'type'        => 'custom',
            'field_type'  => $field_type,
            'label'       => $label,
            'placeholder' => isset( $data['placeholder'] ) ? sanitize_text_field( $data['placeholder'] ) : '',
            'required'    => ! empty( $data['required'] ),
            'enabled'     => true,
            'options'     => $options,
            'max_length'  => isset( $data['max_length'] ) ? absint( $data['max_length'] ) : 255,
            'sort_order'  => $max_sort + 10,
            'section'     => 'additional',
        );

        $fields[] = $new_field;
        update_option( self::OPTION_KEY, $fields );

        return $slug;
    }

    /**
     * Update an existing field's properties
     *
     * @param string $id   Field ID.
     * @param array  $data Updated field data.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function update_field( $id, $data ) {
        $fields = self::get_fields();
        $found = false;

        foreach ( $fields as &$field ) {
            if ( $field['id'] === $id ) {
                $found = true;

                if ( isset( $data['label'] ) ) {
                    $field['label'] = sanitize_text_field( $data['label'] );
                }
                if ( isset( $data['field_type'] ) && 'custom' === $field['type'] ) {
                    $field_type = sanitize_text_field( $data['field_type'] );
                    $valid_types = array_keys( self::get_field_types() );
                    if ( in_array( $field_type, $valid_types, true ) ) {
                        $field['field_type'] = $field_type;
                    }
                }
                if ( isset( $data['placeholder'] ) ) {
                    $field['placeholder'] = sanitize_text_field( $data['placeholder'] );
                }
                if ( isset( $data['required'] ) ) {
                    $field['required'] = ! empty( $data['required'] );
                }
                if ( isset( $data['enabled'] ) ) {
                    $field['enabled'] = ! empty( $data['enabled'] );
                }
                if ( isset( $data['options'] ) ) {
                    if ( is_string( $data['options'] ) ) {
                        $field['options'] = self::parse_options_string( $data['options'] );
                    } elseif ( is_array( $data['options'] ) ) {
                        $field['options'] = $data['options'];
                    }
                }
                if ( isset( $data['max_length'] ) ) {
                    $field['max_length'] = absint( $data['max_length'] );
                }

                break;
            }
        }
        unset( $field );

        if ( ! $found ) {
            return new WP_Error( 'not_found', __( 'Field not found.', 'campus-visit-scheduler' ) );
        }

        update_option( self::OPTION_KEY, $fields );

        return true;
    }

    /**
     * Delete a custom field (never delete builtin fields)
     *
     * @param string $id Field ID.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function delete_field( $id ) {
        $fields = self::get_fields();
        $new_fields = array();
        $found = false;

        foreach ( $fields as $field ) {
            if ( $field['id'] === $id ) {
                $found = true;
                if ( 'builtin_optional' === $field['type'] ) {
                    return new WP_Error( 'cannot_delete', __( 'Built-in fields cannot be deleted.', 'campus-visit-scheduler' ) );
                }
                continue; // Skip this field (delete it)
            }
            $new_fields[] = $field;
        }

        if ( ! $found ) {
            return new WP_Error( 'not_found', __( 'Field not found.', 'campus-visit-scheduler' ) );
        }

        update_option( self::OPTION_KEY, $new_fields );

        return true;
    }

    /**
     * Move a field up or down in sort order
     *
     * @param string $id        Field ID.
     * @param string $direction 'up' or 'down'.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function move_field( $id, $direction ) {
        $fields = self::get_fields(); // Already sorted by sort_order
        $index = null;

        foreach ( $fields as $i => $field ) {
            if ( $field['id'] === $id ) {
                $index = $i;
                break;
            }
        }

        if ( null === $index ) {
            return new WP_Error( 'not_found', __( 'Field not found.', 'campus-visit-scheduler' ) );
        }

        $swap_index = null;
        if ( 'up' === $direction && $index > 0 ) {
            $swap_index = $index - 1;
        } elseif ( 'down' === $direction && $index < count( $fields ) - 1 ) {
            $swap_index = $index + 1;
        }

        if ( null === $swap_index ) {
            return true; // Already at the boundary, no-op
        }

        // Swap sort_order values
        $temp_order = $fields[ $index ]['sort_order'];
        $fields[ $index ]['sort_order'] = $fields[ $swap_index ]['sort_order'];
        $fields[ $swap_index ]['sort_order'] = $temp_order;

        update_option( self::OPTION_KEY, $fields );

        return true;
    }

    /**
     * Render HTML for a form field
     *
     * @param array $field Field definition.
     */
    public static function render_field( $field ) {
        // Determine HTML id and name attributes
        if ( 'builtin_optional' === $field['type'] ) {
            // Use original IDs for backward compatibility
            $html_id = 'cvs-' . str_replace( '_', '-', $field['id'] );
            $html_name = $field['id'];
        } else {
            $html_id = 'cvs-custom-' . esc_attr( $field['id'] );
            $html_name = 'cvs_custom_fields[' . esc_attr( $field['id'] ) . ']';
        }

        $required_attr = ! empty( $field['required'] ) ? ' required' : '';
        $required_span = ! empty( $field['required'] ) ? ' <span class="required">*</span>' : '';
        $max_length = ! empty( $field['max_length'] ) ? (int) $field['max_length'] : 255;
        $placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
        $is_full_width = ( 'textarea' === $field['field_type'] );
        $full_width_class = $is_full_width ? ' cvs-full-width' : '';

        echo '<div class="cvs-form-row">';
        echo '<div class="cvs-form-field' . esc_attr( $full_width_class ) . '">';

        switch ( $field['field_type'] ) {
            case 'textarea':
                echo '<label for="' . esc_attr( $html_id ) . '">' . esc_html( $field['label'] ) . $required_span . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '<textarea id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $html_name ) . '" rows="3" maxlength="' . esc_attr( $max_length ) . '"' . $placeholder . $required_attr . '></textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;

            case 'select':
                echo '<label for="' . esc_attr( $html_id ) . '">' . esc_html( $field['label'] ) . $required_span . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '<select id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $html_name ) . '"' . $required_attr . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '<option value="">' . esc_html__( 'Select...', 'campus-visit-scheduler' ) . '</option>';
                if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
                    foreach ( $field['options'] as $value => $label ) {
                        echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
                    }
                }
                echo '</select>';
                break;

            case 'checkbox':
                echo '<label>';
                echo '<input type="checkbox" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $html_name ) . '" value="1"' . $required_attr . '> '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo esc_html( $field['label'] );
                if ( ! empty( $field['required'] ) ) {
                    echo ' <span class="required">*</span>';
                }
                echo '</label>';
                break;

            case 'number':
                echo '<label for="' . esc_attr( $html_id ) . '">' . esc_html( $field['label'] ) . $required_span . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '<input type="number" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $html_name ) . '"' . $placeholder . $required_attr . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;

            case 'text':
            default:
                echo '<label for="' . esc_attr( $html_id ) . '">' . esc_html( $field['label'] ) . $required_span . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '<input type="text" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $html_name ) . '" maxlength="' . esc_attr( $max_length ) . '"' . $placeholder . $required_attr . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Sanitize a field value based on field type
     *
     * @param mixed $value Field value.
     * @param array $field Field definition.
     * @return mixed Sanitized value.
     */
    public static function sanitize_field_value( $value, $field ) {
        switch ( $field['field_type'] ) {
            case 'textarea':
                return sanitize_textarea_field( $value );

            case 'checkbox':
                return (bool) $value;

            case 'number':
                return is_numeric( $value ) ? intval( $value ) : 0;

            case 'text':
            case 'select':
            default:
                return sanitize_text_field( $value );
        }
    }

    /**
     * Validate a field value
     *
     * @param mixed $value Field value.
     * @param array $field Field definition.
     * @return true|string True if valid, error message string if invalid.
     */
    public static function validate_field_value( $value, $field ) {
        // Check required
        if ( ! empty( $field['required'] ) ) {
            if ( 'checkbox' === $field['field_type'] ) {
                if ( empty( $value ) ) {
                    return sprintf(
                        /* translators: %s: field label */
                        __( '%s is required.', 'campus-visit-scheduler' ),
                        $field['label']
                    );
                }
            } elseif ( '' === $value || null === $value ) {
                return sprintf(
                    /* translators: %s: field label */
                    __( '%s is required.', 'campus-visit-scheduler' ),
                    $field['label']
                );
            }
        }

        // Check max_length for text/textarea
        if ( in_array( $field['field_type'], array( 'text', 'textarea' ), true ) && ! empty( $field['max_length'] ) ) {
            if ( strlen( (string) $value ) > (int) $field['max_length'] ) {
                return sprintf(
                    /* translators: 1: field label, 2: max length */
                    __( '%1$s must be %2$d characters or fewer.', 'campus-visit-scheduler' ),
                    $field['label'],
                    (int) $field['max_length']
                );
            }
        }

        // Check valid option for select
        if ( 'select' === $field['field_type'] && ! empty( $value ) && ! empty( $field['options'] ) ) {
            if ( ! array_key_exists( $value, $field['options'] ) ) {
                return sprintf(
                    /* translators: %s: field label */
                    __( 'Invalid option selected for %s.', 'campus-visit-scheduler' ),
                    $field['label']
                );
            }
        }

        return true;
    }

    /**
     * Get available field types
     *
     * @return array Associative array of type => label.
     */
    public static function get_field_types() {
        return array(
            'text'     => __( 'Text', 'campus-visit-scheduler' ),
            'textarea' => __( 'Text Area', 'campus-visit-scheduler' ),
            'select'   => __( 'Dropdown Select', 'campus-visit-scheduler' ),
            'checkbox' => __( 'Checkbox', 'campus-visit-scheduler' ),
            'number'   => __( 'Number', 'campus-visit-scheduler' ),
        );
    }

    /**
     * Get enabled fields as simplified array for JS (wp_localize_script)
     *
     * @return array Array of field data for frontend validation.
     */
    public static function get_enabled_fields_for_js() {
        $fields = self::get_enabled_fields();
        $js_fields = array();

        foreach ( $fields as $field ) {
            // Only custom fields need JS-side validation data
            if ( 'custom' === $field['type'] ) {
                $js_fields[] = array(
                    'id'         => $field['id'],
                    'label'      => $field['label'],
                    'field_type' => $field['field_type'],
                    'required'   => ! empty( $field['required'] ),
                    'max_length' => isset( $field['max_length'] ) ? (int) $field['max_length'] : 255,
                );
            }
        }

        return $js_fields;
    }

    /**
     * Parse an options string (one per line) into key=>value array
     *
     * Supports format: "value|Label" or just "Label" (label used as value)
     *
     * @param string $options_string Options string, one per line.
     * @return array Parsed options.
     */
    private static function parse_options_string( $options_string ) {
        $options = array();
        $lines = explode( "\n", $options_string );

        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( '' === $line ) {
                continue;
            }

            if ( strpos( $line, '|' ) !== false ) {
                list( $value, $label ) = explode( '|', $line, 2 );
                $value = sanitize_text_field( trim( $value ) );
                $label = sanitize_text_field( trim( $label ) );
            } else {
                $label = sanitize_text_field( $line );
                $value = sanitize_title( $line );
                $value = str_replace( '-', '_', $value );
            }

            if ( '' !== $value && '' !== $label ) {
                $options[ $value ] = $label;
            }
        }

        return $options;
    }

    /**
     * Convert options array back to string format for editing
     *
     * @param array $options Options array.
     * @return string Options string, one per line.
     */
    public static function options_to_string( $options ) {
        if ( empty( $options ) || ! is_array( $options ) ) {
            return '';
        }

        $lines = array();
        foreach ( $options as $value => $label ) {
            if ( $value === sanitize_title( $label ) || $value === str_replace( '-', '_', sanitize_title( $label ) ) ) {
                $lines[] = $label;
            } else {
                $lines[] = $value . '|' . $label;
            }
        }

        return implode( "\n", $lines );
    }
}
