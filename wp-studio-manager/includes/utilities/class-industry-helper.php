<?php
namespace WSM\Includes\Utilities;

use WSM\Includes\Industry_Configs\Industry_Config;

/**
 * Helper functions for industry-specific logic.
 */
class Industry_Helper {
    /**
     * Get custom fields for an entity based on the current industry.
     *
     * @param string $entity participant|session|instructor
     * @return array
     */
    public static function get_entity_fields($entity) {
        $config = Industry_Config::get_config();
        return $config['custom_fields'][$entity] ?? [];
    }

    /**
     * Render HTML form inputs for a given entity.
     *
     * @param string $entity
     * @param array  $values
     */
    public static function render_fields($entity, array $values = []) {
        $fields = self::get_entity_fields($entity);
        foreach ($fields as $key => $field) {
            $label = esc_html($field['label']);
            $type  = $field['type'] ?? 'text';
            $val   = esc_attr($values[$key] ?? '');
            echo '<p><label>' . $label . '<br />';
            switch ($type) {
                case 'textarea':
                    echo '<textarea name="' . esc_attr($key) . '">' . $val . '</textarea>';
                    break;
                case 'select':
                    echo '<select name="' . esc_attr($key) . '">';
                    foreach ($field['options'] as $opt) {
                        $selected = selected($val, $opt, false);
                        echo '<option value="' . esc_attr($opt) . '" ' . $selected . '>' . esc_html($opt) . '</option>';
                    }
                    echo '</select>';
                    break;
                default:
                    echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($key) . '" value="' . $val . '" />';
            }
            echo '</label></p>';
        }
    }
}
