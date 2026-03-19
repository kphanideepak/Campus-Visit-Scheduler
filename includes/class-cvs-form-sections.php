<?php
/**
 * Form sections management for Campus Visit Scheduler
 *
 * @package CampusVisitScheduler
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CVS_Form_Sections
 *
 * Central class for managing form section definitions
 */
class CVS_Form_Sections {

    /**
     * Option key for storing section definitions
     */
    const OPTION_KEY = 'cvs_form_sections';

    /**
     * Get all section definitions sorted by sort_order
     *
     * @return array Array of section definitions.
     */
    public static function get_sections() {
        $sections = get_option( self::OPTION_KEY, array() );

        if ( ! is_array( $sections ) || empty( $sections ) ) {
            // Auto-initialize defaults if not set
            $sections = self::get_default_sections();
            update_option( self::OPTION_KEY, $sections );
        } else {
            // Auto-merge core sections if missing (migration from older versions).
            $existing_ids = wp_list_pluck( $sections, 'id' );
            $defaults     = self::get_default_sections();
            $updated      = false;

            foreach ( $defaults as $default ) {
                if ( 'core' === $default['type'] && ! in_array( $default['id'], $existing_ids, true ) ) {
                    $sections[] = $default;
                    $updated    = true;
                }
            }

            if ( $updated ) {
                update_option( self::OPTION_KEY, $sections );
            }
        }

        usort( $sections, function( $a, $b ) {
            return ( (int) $a['sort_order'] ) - ( (int) $b['sort_order'] );
        });

        return $sections;
    }

    /**
     * Get sections that have at least one enabled field
     *
     * @return array Array of sections with enabled fields.
     */
    public static function get_enabled_sections() {
        $sections = self::get_sections();
        $fields   = CVS_Form_Fields::get_enabled_fields();

        // Build a set of section IDs that have at least one enabled field
        $active_section_ids = array();
        foreach ( $fields as $field ) {
            if ( ! empty( $field['section'] ) ) {
                $active_section_ids[ $field['section'] ] = true;
            }
        }

        return array_values( array_filter( $sections, function( $section ) use ( $active_section_ids ) {
            return isset( $active_section_ids[ $section['id'] ] );
        }));
    }

    /**
     * Get a single section by ID
     *
     * @param string $id Section ID.
     * @return array|null Section definition or null if not found.
     */
    public static function get_section( $id ) {
        $sections = self::get_sections();

        foreach ( $sections as $section ) {
            if ( $section['id'] === $id ) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Add a new section
     *
     * @param array $data Section data (label, description).
     * @return string|WP_Error Section ID on success, WP_Error on failure.
     */
    public static function add_section( $data ) {
        $sections = self::get_sections();

        $label = isset( $data['label'] ) ? sanitize_text_field( $data['label'] ) : '';
        if ( empty( $label ) ) {
            return new WP_Error( 'missing_label', __( 'Section label is required.', 'campus-visit-scheduler' ) );
        }

        // Generate slug from label
        $slug = sanitize_title( $label );
        $slug = str_replace( '-', '_', $slug );

        // Ensure unique slug
        $existing_ids = wp_list_pluck( $sections, 'id' );
        $original_slug = $slug;
        $counter = 2;
        while ( in_array( $slug, $existing_ids, true ) ) {
            $slug = $original_slug . '_' . $counter;
            $counter++;
        }

        // Determine sort_order (after last section)
        $max_sort = 0;
        foreach ( $sections as $section ) {
            if ( (int) $section['sort_order'] > $max_sort ) {
                $max_sort = (int) $section['sort_order'];
            }
        }

        $new_section = array(
            'id'          => $slug,
            'label'       => $label,
            'description' => isset( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '',
            'sort_order'  => $max_sort + 10,
            'type'        => 'custom',
        );

        $sections[] = $new_section;
        update_option( self::OPTION_KEY, $sections );

        return $slug;
    }

    /**
     * Update an existing section's properties
     *
     * @param string $id   Section ID.
     * @param array  $data Updated section data.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function update_section( $id, $data ) {
        $sections = self::get_sections();
        $found    = false;

        foreach ( $sections as &$section ) {
            if ( $section['id'] === $id ) {
                $found = true;

                // Core sections cannot be renamed.
                if ( 'core' === $section['type'] ) {
                    return new WP_Error( 'cannot_edit_core', __( 'Core sections cannot be edited.', 'campus-visit-scheduler' ) );
                }

                if ( isset( $data['label'] ) ) {
                    $section['label'] = sanitize_text_field( $data['label'] );
                }
                if ( isset( $data['description'] ) ) {
                    $section['description'] = sanitize_text_field( $data['description'] );
                }

                break;
            }
        }
        unset( $section );

        if ( ! $found ) {
            return new WP_Error( 'not_found', __( 'Section not found.', 'campus-visit-scheduler' ) );
        }

        update_option( self::OPTION_KEY, $sections );

        return true;
    }

    /**
     * Delete a section (only custom sections with no fields)
     *
     * @param string $id Section ID.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function delete_section( $id ) {
        $sections     = self::get_sections();
        $new_sections = array();
        $found        = false;

        foreach ( $sections as $section ) {
            if ( $section['id'] === $id ) {
                $found = true;

                if ( in_array( $section['type'], array( 'builtin', 'core' ), true ) ) {
                    return new WP_Error( 'cannot_delete', __( 'Built-in sections cannot be deleted.', 'campus-visit-scheduler' ) );
                }

                // Check if section has fields
                $fields = CVS_Form_Fields::get_fields();
                foreach ( $fields as $field ) {
                    if ( isset( $field['section'] ) && $field['section'] === $id ) {
                        return new WP_Error(
                            'has_fields',
                            __( 'Cannot delete section that contains fields. Move or delete the fields first.', 'campus-visit-scheduler' )
                        );
                    }
                }

                continue; // Skip this section (delete it)
            }
            $new_sections[] = $section;
        }

        if ( ! $found ) {
            return new WP_Error( 'not_found', __( 'Section not found.', 'campus-visit-scheduler' ) );
        }

        update_option( self::OPTION_KEY, $new_sections );

        return true;
    }

    /**
     * Save section order from drag-and-drop
     *
     * @param array $section_order Array of section IDs in new order.
     * @return bool True on success.
     */
    public static function save_order( $section_order ) {
        $sections = self::get_sections();

        foreach ( $sections as &$section ) {
            // Core sections keep their fixed sort_order and cannot be reordered.
            if ( 'core' === $section['type'] ) {
                continue;
            }

            $index = array_search( $section['id'], $section_order, true );
            if ( false !== $index ) {
                // Offset by 100 so custom/builtin sections always sort after core.
                $section['sort_order'] = 100 + ( $index + 1 ) * 10;
            }
        }
        unset( $section );

        update_option( self::OPTION_KEY, $sections );

        return true;
    }

    /**
     * Get default section definitions
     *
     * @return array Default sections.
     */
    public static function get_default_sections() {
        return array(
            array(
                'id'          => 'date_time',
                'label'       => __( 'Select Tour Date & Time', 'campus-visit-scheduler' ),
                'description' => '',
                'sort_order'  => 1,
                'type'        => 'core',
            ),
            array(
                'id'          => 'group_size',
                'label'       => __( 'Group Size', 'campus-visit-scheduler' ),
                'description' => '',
                'sort_order'  => 2,
                'type'        => 'core',
            ),
            array(
                'id'          => 'your_details',
                'label'       => __( 'Your Details', 'campus-visit-scheduler' ),
                'description' => '',
                'sort_order'  => 3,
                'type'        => 'core',
            ),
            array(
                'id'          => 'child_info',
                'label'       => __( 'Child Information (Optional)', 'campus-visit-scheduler' ),
                'description' => '',
                'sort_order'  => 10,
                'type'        => 'builtin',
            ),
            array(
                'id'          => 'additional',
                'label'       => __( 'Additional Information', 'campus-visit-scheduler' ),
                'description' => '',
                'sort_order'  => 20,
                'type'        => 'builtin',
            ),
        );
    }
}
