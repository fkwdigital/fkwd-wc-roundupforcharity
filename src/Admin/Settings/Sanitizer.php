<?php

namespace Fkwd\Plugin\Wcrfc\Admin\Settings;

/**
 * Class Sanitizer
 *
 * @package Fkwd\Plugin\Wcrfc
 */
class Sanitizer
{
    private $field_configs = [];

    private $allowed_html = [
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        'br' => [],
        'p' => [],
        'span' => ['class' => [], 'style' => []],
        'small' => [],
        'a' => ['href' => [], 'title' => [], 'target' => []],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'div' => ['class' => []],
    ];


    /**
     * Store field configurations for sanitization context
     *
     * @param array $fields Field configurations
     */
    public function set_field_configs($fields)
    {
        $this->field_configs = $fields;
    }

    /**
     * Sanitizes all input fields from the form before they are saved into the database
     *
     * @param array $fields The input fields from the form
     *
     * @return array The sanitized fields
     */
    public function sanitize($fields)
    {
        // do pre-sanitize actions if needed
        do_action(FKWD_PLUGIN_WCRFC_NAMESPACE . '_pre_sanitize_settings_form_inputs', $fields);

        // only process if input is an array
        if (empty($fields) || ! is_array($fields)) {
            return false;
        }

        // handle direct field structure (no sections)
        foreach ($fields as $field_key => $field_value) {
            $fields[$field_key] = $this->sanitize_field_value($field_key, $field_value);
        }

        // do post-sanitize actions if needed
        do_action(FKWD_PLUGIN_WCRFC_NAMESPACE . '_post_sanitize_settings_form_inputs', $fields);

        // return sanitized fields
        return $fields;
    }

    /**
     * Sanitize individual field value based on field type
     *
     * @param string $field_key The field key
     * @param mixed $field_value The field value
     * @return mixed Sanitized value
     */
    private function sanitize_field_value($field_key, $field_value)
    {
        $field_config = $this->get_field_config($field_key);

        if (!$field_config) {
            return $this->sanitize_legacy_field($field_value);
        }

        $field_type = $field_config['type'] ?? 'text';

        switch ($field_type) {
            case 'multi':
                return $this->sanitize_multi_field($field_value, $field_config);

            case 'checkbox':
                return $this->sanitize_checkbox_field($field_value, $field_config);

            case 'email':
                return sanitize_email($field_value);

            case 'url':
                return esc_url_raw($field_value);

            case 'number':
                return is_numeric($field_value) ? floatval($field_value) : 0;

            case 'textarea':
                return wp_kses($field_value, $this->allowed_html);

            case 'html':
                return wp_kses_post($field_value);

            case 'composite':
                return $this->sanitize_composite_field($field_value, $field_config);

            default:
                return wp_kses_post((string) $field_value);
        }
    }

    /**
     * Sanitize multi-input field
     *
     * @param mixed $input The input value
     * @param array $field_config Field configuration
     * @return array Sanitized array
     */
    private function sanitize_multi_input_field($input, $field_config)
    {
        if (!is_array($input)) {
            return [];
        }

        $field_type = $field_config['options']['field_type'] ?? 'text';

        $max_items = $field_config['options']['max_items'] ?? 10;
        $sanitized = [];
        $count = 0;

        ksort($input);

        foreach ($input as $key => $value) {
            if ($count >= $max_items) {
                break;
            }

            $trimmed = trim($value);
            if (empty($trimmed)) {
                continue;
            }

            switch ($field_type) {
                case 'textarea':
                    $sanitized[] = wp_kses($trimmed, $this->allowed_html);
                    break;
                case 'email':
                    $clean_value = sanitize_email($trimmed);
                    if (is_email($clean_value)) {
                        $sanitized[] = $clean_value;
                    }
                    break;
                case 'url':
                    $clean_value = esc_url_raw($trimmed);
                    if (filter_var($clean_value, FILTER_VALIDATE_URL)) {
                        $sanitized[] = $clean_value;
                    }
                    break;
                case 'composite':
                    $sanitized[] = $this->sanitize_composite_field($trimmed, $field_config);
                    break;
                default:
                    $sanitized[] = sanitize_text_field($trimmed);
            }
            $count++;
        }

        return array_values($sanitized);
    }

    /**
     * Sanitize checkbox field (legacy handling)
     *
     * @param mixed $field_value The field value
     * @param array $field_config Field configuration
     * @return mixed Sanitized value
     */
    private function sanitize_checkbox_field($field_value, $field_config)
    {
        if (is_array($field_value)) {
            // handle multiple checkboxes
            $cleaned = [];
            foreach ($field_value as $box_key => $box_value) {
                if ($box_value) {
                    $cleaned[$box_key] = 1;
                }
            }
            return $cleaned;
        } else {
            // single checkbox
            return $field_value ? 1 : 0;
        }
    }

    private function sanitize_composite_field($input, $field_config = [])
    {
        if (!is_array($input)) {
            return [];
        }

        $sanitized = [];
        $fields = $field_config['fields'] ?? [];
        $max_items = $field_config['max_items'] ?? 10;
        $count = 0;

        foreach ($input as $index => $item) {
            if ($count >= $max_items) {
                break;
            }

            if (!is_array($item)) {
                continue;
            }

            $sanitized_item = [];
            $has_content = false;

            foreach ($item as $field_name => $field_value) {
                $field_type = 'text';

                // Get field configuration
                foreach ($fields as $field) {
                    if ($field['name'] === $field_name) {
                        $field_type = $field['type'] ?? 'text';
                        break;
                    }
                }

                $clean_value = is_string($field_value) ? trim($field_value) : '';
                if ($clean_value !== '') {
                    $has_content = true;
                }

                // Only sanitize specific field types that need it, preserve HTML for everything else
                switch ($field_type) {
                    case 'email':
                        $sanitized_item[$field_name] = sanitize_email($clean_value);
                        break;
                    case 'url':
                        $sanitized_item[$field_name] = esc_url_raw($clean_value);
                        break;
                    default:
                        // No HTML sanitization - just trim whitespace
                        $sanitized_item[$field_name] = $clean_value;
                }
            }

            if ($has_content) {
                $sanitized[] = $sanitized_item;
                $count++;
            }
        }

        return $sanitized;
    }

    private function sanitize_multi_field($input, $field_config)
    {
        if (!is_array($input)) {
            return [];
        }

        $options = $field_config['options'] ?? [];
        $field_type = $options['field_type'] ?? 'text';

        if ($field_type === 'composite') {
            return $this->sanitize_composite_field($input, $options);
        }

        return $this->sanitize_multi_input_field($input, $field_config);
    }

    /**
     * Legacy field sanitization for backwards compatibility
     *
     * @param mixed $field_value The field value
     * @return mixed Sanitized value
     */
    private function sanitize_legacy_field($field_value)
    {
        if (is_array($field_value)) {
            // handle when there's multiple values per field selected (like multiselect or checkbox group)
            $cleaned = [];
            foreach ($field_value as $box_key => $box_value) {
                if ($box_value) {
                    $cleaned[$box_key] = 1;
                }
            }
            return $cleaned;
        } elseif (!empty($field_value)) {
            return sanitize_text_field((string) $field_value);
        } else {
            return null;
        }
    }

    /**
     * Get field configuration by key
     *
     * @param string $field_key The field key
     * @return array|null Field configuration or null if not found
     */
    private function get_field_config($field_key)
    {
        foreach ($this->field_configs as $config) {
            if (isset($config['label_for']) && $config['label_for'] === $field_key) {
                return $config;
            }
        }
        return null;
    }
}
