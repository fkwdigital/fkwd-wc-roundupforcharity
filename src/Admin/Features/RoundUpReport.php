<?php

namespace Fkwd\Plugin\Wcrfc\Admin\Features;

use Fkwd\Plugin\Wcrfc\Admin\Settings;
use Fkwd\Plugin\Wcrfc\Interface\Feature as FeatureInterface;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Feature;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Strings;

/**
 * Class RoundUpReport
 *
 * @package fkwdwcrfc/src
 */
class RoundUpReport implements FeatureInterface
{
    use Feature;
    use Singleton;
    use Strings;

    /** @var Settings */
    private $settings;

    /** @var bool */
    protected $enabled;

    /** @var string */
    protected $slug;

    /** @var string */
    protected $page_id;

    /** @var string */
    protected $db_id;

    /** @var string */
    protected $parent_page_id;

    /** @var string */
    protected $class_name;

    /**
     * Construct the RoundUpReport feature
     *
     * @since 0.1.0
     */
    public function __construct()
    {
        $name = 'RoundUpReport';

        $this->set_name($name);

        $this->register_new_admin_settings();
    }

    /**
     * Register new admin settings page.
     *
     * This method is responsible for setting up the options page for the
     * RoundUpReport feature. It will create the menu item in the WordPress admin
     * dashboard and add all the necessary sections and fields to the page.
     *
     * @since 0.1.0
     *
     * @return object The Settings object
     */
    public function register_new_admin_settings(): object
    {
        try {
            $safe_slug      = $this->create_safe_attribute_field_value($this->name);
            // create the ids and class related to this feature
            $slug           = $this->set_slug($safe_slug);
            $page_id        = $this->set_page_id(FKWD_PLUGIN_WCRFC_NAMESPACE . '_' . $slug . '_settings');
            $db_id          = $this->set_db_id(FKWD_PLUGIN_WCRFC_NAMESPACE . '_' . $slug . '_database_settings');
            $parent_page_id = 'woocommerce';
            $class_name     = $this->set_class_name(\Fkwd\Plugin\Wcrfc\Admin\RoundUpReport::class);

            // set the menu specific settings and options for this feature
            $menu_type      = 'submenu';
            $page_title     = __(FKWD_PLUGIN_WCRFC_NAME . ' Settings', 'fkwd-wc-roundupforcharity');
            $menu_title     = __('Round Up Report', 'fkwd-wc-roundupforcharity');
            $submenu_title  = '';
            $menu_icon      = '';
            $capability     = 'manage_woocommerce';
            $position       = 999;

            $options = [
                'menu_type'  => $menu_type,
                'page_title' => $page_title,
                'menu_title' => $menu_title,
                'submenu_title' => $submenu_title,
                'menu_icon'  => $menu_icon,
                'capability' => $capability,
                'class_name' => $class_name,
                'position'   => $position,
            ];

            $fields = [
                [
                    'label_for' => 'roundup_description',
                    'title' => 'Checkout Field Label',
                    'description' => '',
                    'type' => 'text',
                ],
            ];

            $sections[] = [
                'section_id' => FKWD_PLUGIN_WCRFC_NAMESPACE . '_settings_form_section_general',
                'section_details' => [
                    'section_title'       => 'General Settings',
                    'section_description' => 'Configure general options for Roundup for Charity.',
                    'fields'              => $fields,
                ],
            ];


            // build IDs array for orchestrator
            $ids = [
                'page_settings_id'           => $page_id,
                'page_settings_database_id'  => $db_id,
                'parent_page_id'             => ''
            ];

            if ($parent_page_id) {
                $ids['parent_page_id'] = $parent_page_id;
            }

            // configure this feature's settings page
            $this->settings = new Settings();
            $this->settings->configure($ids, $options, $sections);
        } catch (\Exception $e) {
            add_action('admin_notices', function () use ($e) {
                echo '<div class="notice notice-error"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }

        return $this;
    }
}
