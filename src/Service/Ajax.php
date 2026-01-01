<?php

namespace Fkwd\Plugin\Wcrfc\Service;

use Fkwd\Plugin\Wcrfc\Admin\RoundUpReport;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Security;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Strings;

class Ajax
{
    use Security;
    use Singleton;
    use Strings;

    public function __construct()
    {
    }

    /**
     * Initializes global AJAX functionality to be used on back and frontend pages
     *
     * Hooks into `wp_ajax_{$action}` to register AJAX actions
     *
     * @since 0.1.0
     */
    public function init()
    {
        if (empty($report_controller) || !is_object($report_controller) || !$report_controller instanceof RoundUpReport) {
            $report_controller = new RoundUpReport();
        }

        $ajax_prefix = 'wp_ajax_' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_';
        $handler_prefix = 'handle_';

        // global brand/type search functionality
        add_action($ajax_prefix . 'roundup_report', [$report_controller, $handler_prefix . 'roundup_report']);
    }

    /**
     * Sanitizes $_POST data based on provided arguments.
     *
     * @param array $post The $_POST data to sanitize.
     * @param array $args An array of fields to sanitize and their respective types.
     * @return array The sanitized $_POST data.
     */
    public function sanitize_ajax_dashboard_POST($post, $args = [])
    {
        $post = wp_unslash($post);

        if (!empty($args) && is_array($args)) {
            foreach ($args as $field => $type) {
                if (!isset($post[$field])) {
                    continue;
                }

                switch ($type) {
                    case 'int':
                        $post[$field] = $this->clean_string_type($post[$field], ['type' => 'int']);
                        break;
                    case 'absint':
                        $post[$field] = $this->clean_string_type($post[$field], ['type' => 'absint']);
                        break;
                    case 'float':
                        $post[$field] = $this->clean_string_type($post[$field], ['type' => 'float']);
                        break;
                    case 'string':
                    case 'text':
                        $post[$field] = sanitize_text_field($post[$field]);
                        break;
                    case 'textarea':
                        $post[$field] = sanitize_textarea_field($post[$field]);
                        break;
                    case 'email':
                        $post[$field] = $this->clean_string_type($post[$field], ['type' => 'email']);
                        break;
                    case 'url':
                        $post[$field] = $this->clean_string_type($post[$field], ['type' => 'url']);
                        break;
                    case 'date':
                        $post[$field] = $this->clean_string_type($post[$field], ['type' => 'date']);
                        break;
                    case 'html':
                        $post[$field] = $this->clean_string_type($post[$field], ['type' => 'html']);
                        break;
                    case 'bool':
                        $post[$field] = $post[$field] === 'true' ? true : false;
                        break;
                }
            }
        }

        return $post;
    }

    /**
     * Sanitizes $_POST data based on provided arguments.
     *
     * @param array $post The $_POST data to sanitize.
     * @param array $args An array of fields to sanitize and their respective types.
     * @return array The sanitized $_POST data. Returns false if the current user does not have the 'manage_woocommerce' capability.
     */
    public function sanitize_ajax_admin_POST($post, $args = [])
    {
        // do everything you normally do with dashboard
        $post = $this->sanitize_ajax_dashboard_POST($post, $args);

        // but also check if the current user has the 'manage_woocommerce' capability
        if (!current_user_can('manage_woocommerce')) {
            return false;
        }

        return $post;
    }
}
