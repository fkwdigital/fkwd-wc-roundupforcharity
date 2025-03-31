<?php
namespace Fkwd\Plug\Wcrfc\Admin;

use Fkwd\Plug\Wcrfc\Base;

/**
 * Class Settings
 *
 * @package fkwdwcrfc/src
 */
class Settings extends Base
{
    // page slug for this functionality
	private $page_id;

	// input field for the wp_option meta key name
	private $page_database_id;

    // array key for the db names for the fields themselves
    private $page_input_id;

    // array key for the db names for the fields themselves
    private $parent_page_id;

    // page options set by the user initializing the settings
    private $page_options;

    // class path to use for data calls
    private $class_name;

    /**
     * Constructor
     *
     * @return none
     */
    public function __construct()
    {

    }

    /**
     * Configures the settings for the admin page.
     *
     * This method initializes settings by assigning IDs and options, and optionally
     * sections if provided. It sets the page ID, database ID, and input ID from the 
     * provided `$ids` array. Additionally, it configures the page options from the 
     * `$options` array and registers WordPress admin actions for the menu and settings.
     *
     * If a parent page ID is specified, the page is set as a submenu under the specific parent.
     * If sections are provided, they are added to the settings page.
     *
     * @param array $ids An associative array containing the IDs for the page, database, and optionally, the parent page.
     * @param array $options The options to configure the settings page.
     * @param array|null $sections Optional sections to be added to the settings page.
     */
	public function configure( $ids, $options, $sections = NULL )
    {
        if( !empty( $ids) && !empty( $options ) )
        {
            $this->page_id = $ids['page_settings_id'];
            $this->page_database_id = $ids['page_settings_database_id'];
            $this->page_input_id = $this->page_database_id . '_input';
            $this->page_options = $options;

            // if this page wants to be a submenu under a specific parent, set that here
            if( !empty( $ids['parent_page_id'] ) )
            {
                $this->parent_page_id = $ids['parent_page_id'];
            }

            add_action( 'admin_menu', [ $this, 'add_' . $this->page_options['menu_type'] . '_page' ], 99 );

            // registers the settings related to the pages added
            add_action( 'admin_init', [ $this, 'register_settings' ] );

            if( !empty( $sections ) ) {
                // add the sections to the settings page created
                $this->add_sections( $sections );
            }
        }
	}

	/**
	 * Adds top level menu page and form in that page.
	 *
	 * @return void
	 */
	public function add_menu_page()
    {
        if( empty( $this->page_id ) || empty( $this->page_options['page_title'] ) || empty( $this->page_options['menu_title'] ) )
        {
            return;
        }

		add_menu_page(
			$this->page_options['page_title'],
            $this->page_options['menu_title'],
			$this->page_options['capability'] ?? 'edit_theme_options',
			$this->page_id,
			[ $this, 'add_settings_form_html' ],
			$this->page_options['menu_icon'] ?? 'dashicons-generic',
			$this->page_options['position'] ?? 99
		);

        $this->parent_page_id = $this->page_id;

        if( !empty( $this->page_options['submenu_title'] ) ) {
            $this->page_options['menu_title'] = $this->page_options['submenu_title'];
        }

        $this->add_submenu_page();

	}

    /**
     * Adds sub menu page and form in that page.
     *
     * @return void
     */
    public function add_submenu_page()
    {
        if( empty( $this->page_id ) || empty( $this->page_options['page_title'] ) || empty( $this->page_options['menu_title'] ) )
        {
            return;
        }

        if ( empty( $this->parent_page_id ) ) {
            $this->parent_page_id = $this->page_id;
        }
        
		add_submenu_page(
			$this->parent_page_id,
            $this->page_options['page_title'],
            $this->page_options['menu_title'],
            $this->page_options['capability'] ?? 'manage_options',
			$this->page_id,
            [ $this, 'add_settings_form_html' ],
		);

	}

    public function register_settings()
    {
        // registers the setting option for the db
        register_setting( $this->page_id, $this->page_database_id, [ $this, 'validate_settings_form_inputs' ] );
    }

	/**
	 * Adds sections to the settings form.
	 *
	 * This function adds sections to the settings form based on the provided array of sections.
	 *
	 * @param array $sections An array containing the sections to be added to the form. Each section is represented by an associative array with the following keys:
	 *                        - section_key (string): The identifier of the section.
	 *                        - section_title (string): The title of the section.
	 *                        - section_description (string): The description of the section.
	 *                        - page_id (mixed): The ID of the page the section belongs to.
	 * @throws Exception if there is an error rendering the section.
	 * @return void
	 */
	public function add_sections( $sections = [] )
    {
        if( empty( $sections ) || !is_array( $sections ) )
        {
            return;
        }

        $page_id = $this->page_id;

        add_action( 'admin_init', function() use ( $sections, $page_id ) {
            if( empty( $sections ) ) {
                return;
            }

            foreach( $sections as $section )
            {
                if( empty( $section['section_id'] ) || empty( $section['section_details'] ) )
                {
                    return;
                }

                // adds the section details to the form the wordpress way
                add_settings_section(
                    $section['section_id'],
                    $section['section_details']['section_title'] ?? '',
                    function() use ( $section, $page_id ) {
                        echo $section['section_details']['section_description'] ?? '';
                    },
                    $page_id
                );

                $fields = $section['section_details']['fields'];

                if( empty( $fields ) )
                {
                    return;
                }

                if( is_array( $fields ) )
                {
                    foreach( $fields as $field )
                    {
                        $field['id'] = $field['label_for'];

                        $field = [
                            $field['id'],
                            $field['title'],
                            [ &$this, 'prepare_section_field' ],
                            $this->page_id,
                            $section['section_id'],
                            $field
                        ];

                        add_settings_field( ...$field );
                    }
                }

                if( !empty( $section['section_details']['after_section'] ) )
                {
                    $field = [
                        $section['section_id'] . '_after',
                        '',
                        [ &$this, 'add_section_after' ],
                        $this->page_id,
                        $section['section_id'],
                        $section['section_details']['after_section']
                    ];

                    add_settings_field( ...$field );
                }

                $class = $section['section_details']['class'];

                if ( !empty( $class ) )
                {
                    $class_name = $class;

                    // Check if the class exists to avoid errors
                    if ( class_exists( $class_name ) ) {
                        // Instantiate the class
                        $class_instance = new $class_name();
                        $this->class_name = $class_name;

                        // Call the init() method if it exists
                        if ( method_exists( $class_instance, 'init' ) ) {
                            $class_instance->init();
                        }
                    }
                }
            }
        });

	}

    /**
     * Prepare the section fields.
     *
     * @param array $fields The section fields.
     * @return void
     */
    public function prepare_section_field( $field )
    {
        $args = $field['args'] ?? [];
        $disabled = false;

        if( !empty( $args['depends'] ) && is_array( $args['depends'] ) ) {
            $dependents = $args['depends'];

            foreach( $dependents as $dependent ) {
                if( empty( get_option( $this->page_database_id )[ $dependent ] ) ) {
                    $disabled = true;
                }
            }
        }

		$this->render_field( $field, $disabled );

    }

    public static function add_section_after( $after_section ) {
        if( empty( $after_section['content'] ) ) {
            return;
        }

        echo wp_kses_post( $after_section['content'] );
    }

    /**
     * Creates a set of multiple types of fields and their functionality
     * - sorts through which type of field it needs and creates them
     *
     * @param object $fields houses all the data from the generate function
     * @param boolean $disabled if this is true, the field will be set as disabled until other values are met
     * @return none
     */
    public function render_field( $field, $disabled = false )
    {
        if( empty( $field ) )
        {
            return;
        }

        $id = $field['id'] ?? NULL;
        $label = $field['title'] ?? NULL;
        $description = $field['description'] ?? NULL;
        $type = $field['type'] ?? 'text';
        $default = $field['default'] ?? NULL;

        if( $id === NULL )
        {
            return;
        }

        if( !empty( $description ) )
        {
			?><p class="description"><?php echo esc_html_e( $description, FKWD_PLUGIN_WCRFC_NAMESPACE ); ?></p><?php
		}

		$values = get_option( $this->page_database_id ) ?? NULL;

        ?><div
    class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-fields <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-mixed-fields fields-group <?php echo str_replace( '_', '-', $id ); ?>"><?php

        $value = isset( $values[ $id ] ) ? $values[ $id ] : '';

        // if the type is checkbox or radio, value is 1 or 0
        if( $type == 'checkbox' || $type == 'radio' ) {
            $value = (int) $value;
        }

        if( $type == 'select' ) {
            if( !empty( $this->class_name ) ) {
                $data_class = $this->class_name::get_instance();

                $available_options = $data_class->get_available_options( $field );
            }
        }

        if( $type != 'select' && $type != 'checkbox' && $type != 'radio' ) {
            include( FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/fields/input.php' );
        } else {
            if( file_exists( FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/fields/' . $type . '.php' ) ) {
                include( FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/fields/' . $type . '.php' );
            }
        }
        ?>
</div>
<?php
    }

    /**
     * Validates form fields from injection and security issues
     * - this runs before the fields are added to the database
     *
     * @param object $fields has all the input fields that got entered by the user
     * @return none
     */
	public function validate_settings_form_inputs( $fields ) {
		do_action( FKWD_PLUGIN_WCRFC_NAMESPACE . '_pre_validate_settings_form_inputs', $fields );

        // Check if the input is an array
        if ( !empty( $fields) && is_array( $fields ) ) {
            // Iterate over each top-level array
            foreach ( $fields as $section_key => &$section_fields ) {
                // Check if the section is an array itself
                if ( !empty( $section_fields ) && is_array( $section_fields) ) {
                    // Iterate over each field in the section
                    foreach ( $section_fields as $field_key => $field_value ) {
                        if( !empty( $field_value ) ) {
                            // Sanitize each field value
                            $section_fields[$field_key] = sanitize_text_field( (string) $field_value );   
                        }
                    }
                } else if( !empty( $section_fields ) ) {
                    // Sanitize the field if it's not an array
                    $fields[$section_key] = sanitize_text_field( (string) $section_fields );
                }
            }

            unset( $section_fields ); // Break the reference with the last element
        } else {
            return false;
        }

        do_action( FKWD_PLUGIN_WCRFC_NAMESPACE . '_post_validate_settings_form_inputs', $fields );

        // Return the sanitized fields
        return $fields;
	}

	/**
     * Registers the form's HTML
     * - creates the boundaries around the form fields
     *
     * @param none
     * @return none
     */
	public function add_settings_form_html() {
		// check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $template_id = str_replace( [ FKWD_PLUGIN_WCRFC_NAMESPACE . '_', '_' ], [ '', '-' ], $this->page_id );

        if( !empty( $this->class_name ) ) {
            $data_class = $this->class_name::get_instance();
        }

        include_once( FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/' . $template_id . '.php' );
	}
}
