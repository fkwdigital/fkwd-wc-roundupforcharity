<?php

namespace Fkwd\Plugin\Wcrfc\Admin;

/**
 * Class Settings
 *
 * @package Fkwd\Plugin\Wcrfc
 */
class Settings
{
    private $menu;
    private $registrar;
    private $renderer;
    private $sanitizer;
    private $ids;
    private $page_id;
    private $db_id;
    private $options;
    private $sections;
    private $parent_page_id;
    private $template_id;
    private $class_name;

    /**
     * Configure the settings page by creating the following objects:
     *
     * @var Menu      $menu      The object that creates the menu item.
     * @var Registrar $registrar The object that registers the settings fields.
     * @var Renderer  $renderer  The object that renders the page.
     * @var Sanitizer $sanitizer The object that sanitizes the fields.
     */
    public function __construct()
    {
        $this->menu      = new Settings\Menu();
        $this->registrar = new Settings\Registrar();
        $this->renderer  = new Settings\Renderer();
        $this->sanitizer = new Settings\Sanitizer();
    }

    /**
     * Configure the settings page.
     *
     * This method sets the page id, database id, parent page id, options, and sections.
     * It also sets the template id and class name if provided.
     * Additionally, it registers the admin menu and admin init actions.
     *
     * @param array $ids An associative array of page settings ids.
     * @param array $options An associative array of page settings options.
     * @param array $sections An associative array of page settings sections.
     */
    public function configure($ids, $options, $sections = null)
    {   
        $this->page_id        = $ids['page_settings_id'];
        $this->db_id          = $ids['page_settings_database_id'];
        $this->parent_page_id = $ids['parent_page_id'] ?? $this->page_id;
        $this->options        = $options;
        $this->sections       = $sections;
        $this->template_id    = str_replace([ FKWD_PLUGIN_WCRFC_NAMESPACE . '_', '_' ], [ '', '-' ], $this->page_id);
        $this->class_name     = $options['class_name'] ?? null;

        $priority = $this->calculate_menu_priority();
        add_action('admin_menu', [ $this, 'register_menu' ], $priority);
        add_action('admin_init', [ $this, 'register_settings_and_sections' ]);
    }


    /**
     * Register the menu item.
     *
     * This method registers the menu item under the appropriate parent.
     * If the menu type is 'menu', it adds a top-level menu item.
     * If the menu type is 'submenu', it adds a submenu item under the parent page id.
     *
     * @return void
     */
    public function register_menu()
    {
        static $registered_pages = [];

        if (in_array($this->page_id, $registered_pages)) {
            return;
        }

        $registered_pages[] = $this->page_id;


        $callback = function () {
            $this->renderer->render_form($this->template_id, $this->class_name, $this->page_id, $this->options);
        };

        if ($this->options['menu_type'] === 'menu') {
            $this->menu->add_menu_page($this->page_id, $this->options, $callback);
        } else {
            $this->menu->add_submenu_page($this->parent_page_id, $this->page_id, $this->options, $callback);
        }
    }

    /**
     * Registers settings and sections for the admin page.
     *
     * This method utilizes the registrar to register the settings associated with the page ID and database ID,
     * ensuring they are sanitized using the specified callback. If sections are provided, it iterates over each
     * section and its fields to append database ID and class name for renderer usage. Finally, it adds the sections
     * to the page using the registrar.
     *
     * @return void
     */
    public function register_settings_and_sections()
    {
        // collect all field configs for sanitizer context
        $all_field_configs = [];

        if (!empty($this->sections)) {
            foreach ($this->sections as $section) {
                if (!empty($section['section_details']['fields'])) {
                    $all_field_configs = array_merge($all_field_configs, $section['section_details']['fields']);
                }
            }
        }

        // pass field configs to sanitizer for context-aware sanitization
        $this->sanitizer->set_field_configs($all_field_configs);

        $sanitize_callback = [$this->sanitizer, 'sanitize'];
        $this->registrar->register_settings($this->page_id, $this->db_id, $sanitize_callback);

        if (!empty($this->sections)) {
            // add db_id and class_name to each field for renderer use
            foreach ($this->sections as &$section) {
                if (!empty($section['section_details']['fields'])) {
                    foreach ($section['section_details']['fields'] as &$field) {
                        $field['page_id']        = $this->page_id;
                        $field['parent_page_id'] = $this->parent_page_id;
                        $field['page_title']     = $this->options['page_title'];
                        $field['db_id']          = $this->db_id;
                        $field['class_name']     = $this->class_name;
                    }
                }
            }

            unset($section, $field);

            $this->registrar->add_sections($this->page_id, $this->db_id, $this->sections, $this->renderer);
        }
    }

    /**
     * Calculate the menu priority based on the provided options.
     *
     * If the menu type is 'menu', the priority is set to 10.
     * If the menu type is 'submenu', the priority is set to 10 plus the position value or 99 if it is not provided.
     *
     * @return int The calculated menu priority.
     */
    private function calculate_menu_priority(): int
    {
        if ($this->options['menu_type'] === 'menu') {
            return 10;
        }

        return 10 + ($this->options['position'] ?? 99);
    }
}
