<?php

namespace Fkwd\Plugin\Wcrfc\Admin\Settings;

use Fkwd\Plugin\Wcrfc\Utils\Traits\Strings;

/**
 * Class Renderer
 *
 * @package fkwdwcrfc/src
 */
class Renderer
{
    use Strings;

    /**
     * Renders a field based on provided arguments and type.
     *
     * This function accepts an array of arguments that define the field's
     * properties, such as id, label, description, type, default value, and
     * database ID. It fetches the current value of the field from the database
     * if a database ID is provided. Depending on the field type (text, checkbox,
     * radio, select), it includes the appropriate template for rendering the field.
     * It also handles the rendering of field descriptions and wraps the field in
     * a styled div for consistent presentation.
     *
     * @param array $args An array of arguments for the field configuration.
     * @param boolean $disabled Optional. If true, the field is rendered as disabled.
     */
    public function render_field($args, $disabled = false)
    {
        if (empty($args)) {
            return;
        }

        $field_vars = [
            'page_id'          => $args['page_id'],
            'page_database_id' => $args['db_id'],
            'parent_page_id'   => $args['parent_page_id'],
            'page_title'       => $args['page_title'],
            'id'               => $args['id'] ?? ($args['label_for'] ?? null),
            'label'            => $args['title'] ?? '',
            'description'      => $args['description'] ?? '',
            'type'             => $args['type'] ?? 'text',
            'default'          => $args['default'] ?? '',
            'options'          => $args['options'] ?? [],
            'disabled'         => $disabled,
            'renderer'         => self::class ?? null
        ];

        extract($field_vars);

        if (! $id) {
            return;
        }

        // get option value if database id is provided
        $values = $page_database_id ? get_option($page_database_id) : null;
        $value  = isset($values[$id]) ? $values[$id] : $default;

        if ($type === 'multi-input') {
            // ensure value is array for multi-input
            if (!is_array($value)) {
                $value = !empty($value) ? [$value] : [];
            }

            // filter out empty values for performance
            $value = array_filter($value, function ($v) {
                return !empty(trim($v));
            });

            // reindex array to ensure consecutive keys
            $value = array_values($value);
        }

        // for checkbox/radio, cast to int
        if (! is_array($value) && ($type === 'checkbox' || $type === 'radio')) {
            $value = (int) $value;
        }

        // configure select dropdown fields
        if ($type === 'select') {
            $selected = '';

            if (!empty($options) && is_array($options)) {
                // use saved value only if it exists in options
                if (!empty($value) && array_key_exists($value, $options)) {
                    $selected = $value;
                } else {
                    // fallback to default or first option
                    $selected = !empty($default) && array_key_exists($default, $options) 
                        ? $default 
                        : array_key_first($options);
                }
            }
        }

        $html = '';

        // wrap field in a div for styling
        $html .= '<div class="' . $this->clean_string(FKWD_PLUGIN_WCRFC_NAMESPACE . '-fields', ['type' => 'attribute']) . ' fields-group ' . $this->clean_string(str_replace('_', '-', $id), ['type' => 'attribute']) . '">';

        // render field type
        $field_template = FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/fields/' . $type . '.php';

        if (file_exists($field_template)) {
            include($field_template);
        } else {
            // fallback to generic input template
            include(FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/fields/input.php');
        }

        // render field description if available
        if (! empty($description)) {
            $html .= '<p class="description">' . esc_html($description) . '</p>';
        }

        $html .= '</div>';

        echo $this->clean_string($html, ['type' => 'html']);
    }

    /**
     * Render the admin settings form template based on provided template id
     *
     * @param string $template_id The id of the template to render, e.g. 'settings-page'
     * @param string $class_name If provided, the class name of the data class to provide to the template
     *
     * @return void
     */
    public function render_form($template_id, $class_name = null, $page_id = null, $page_options = null)
    {
        $template_path = FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/';

        // build the template filename, e.g., settings-page.php
        $template_file = $template_path . $template_id . '.php';

        // provide data class if required by the template
        if (! empty($class_name) && class_exists($class_name)) {
            $data_class = $class_name::get_instance();
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- There is no need for nonce verification here because it is not a form submission, but a page load.
        $template_override_id = sanitize_file_name(wp_unslash($_GET['template']) ?? '');

        // check if template override is requested
        if ($template_override_id) {
            $template_file = $template_path . $template_override_id . '.php';
            $template_id = $template_override_id;
        }

        if (file_exists($template_file)) {
            include_once($template_file);
        } else {
            // fallback to generic template
            include_once($template_path . 'settings.php');
        }
    }

    /**
     * Renders content after a section, if provided.
     *
     * This method accepts an associative array with a single key, 'content', which
     * contains the content to be rendered. The content is sanitized using wp_kses_post()
     * before being echoed.
     *
     * @param array $after_section An associative array with a single key, 'content', which
     * contains the content to be rendered.
     */
    public function render_after_section($after_section)
    {
        if (empty($after_section['content'])) {
            return;
        }

        // output the after section content with sanitization
        echo wp_kses_post($after_section['content']);
    }
}
