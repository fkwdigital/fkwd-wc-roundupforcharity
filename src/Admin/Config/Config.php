<?php
namespace Fkwd\Plug\Wcrfc\Admin\Config;

use Fkwd\Plug\Wcrfc\Base;

/**
 * Config class
 *
 * This class extends the Base class and provides feature configuration functionality.
 */
class Config extends Base
{
	/**
	 * @var string $slug The slug of the feature.
	 */
	public $slug;

	/**
	 * @var array $ids The array of IDs.
	 */
	public $ids = [];

	/**
	 * @var array $sections The array of sections.
	 */
	public $sections = [];

	/**
	 * @var array $options The array of options.
	 */
	public $options = [];

	 /**
     * Constructor
     */
    public function __construct()
    {
		$this->set_option_menu_type();
		$this->set_option_capability();
		$this->set_option_position();
    }

	/**
	 * Sets the slug for the object.
	 *
	 * @param string $slug The slug to set.
	 * @throws \Exception If an invalid slug is provided.
	 * @return $this The current object.
	 */
	public function set_slug( $slug )
	{
		$options = [
			'type' => 'attribute',
			'lowercase' => true,
			'trim' => true,
			'strip_tags' => true,
			'convert_spaces' => '_',
		];

		if( $this->fkwd_clean_string( $slug, $options ) !== $slug )
		{
			throw new \Exception( 'Invalid slug provided. Provided ' . $slug . ' is not a valid slug.' );
		}

		$this->slug = $slug;

		return $this;
	}

	/**
	 * Set the page settings ID.
	 *
	 * @param int $id The ID of the page settings.
	 * @return $this The current instance of the class.
	 */
	public function set_page_settings_id( $id )
	{
		$options = [
			'type' => 'attribute',
			'lowercase' => true,
			'trim' => true,
			'strip_tags' => true,
			'convert_spaces' => '_',
		];

		if( $this->fkwd_clean_string( $id, $options ) !== $id )
		{
			throw new \Exception( 'Invalid page settings ID provided. Provided ' . $id . ' is not a valid ID.' );
		}

		$this->ids['page_settings_id'] = $id;

		return $this;
	}

	/**
	 * Sets the page settings database ID.
	 *
	 * @param int $id The ID of the page settings in the database.
	 * @return $this The current instance of the class.
	 */
	public function set_page_settings_database_id( $id )
	{
		$options = [
			'type' => 'attribute',
			'lowercase' => true,
			'trim' => true,
			'strip_tags' => true,
			'convert_spaces' => '_',
		];

		if( $this->fkwd_clean_string( $id, $options ) !== $id )
		{
			throw new \Exception( 'Invalid page settings database ID provided. Provided ' . $id . ' is not a valid ID.' );
		}

		$this->ids['page_settings_database_id'] = $id;

		return $this;
	}

	/**
	 * Sets the parent page ID.
	 *
	 * @param int $id The ID of the parent page.
	 * @return $this The current instance of the class.
	 */
	public function set_parent_page_id( $id )
	{
		$options = [
			'type' => 'attribute',
			'lowercase' => true,
			'trim' => true,
			'strip_tags' => true,
			'convert_spaces' => '_',
		];

		if( $this->fkwd_clean_string( $id, $options ) !== $id )
		{
			throw new \Exception( 'Invalid parent page ID provided. Provided ' . $id . ' is not a valid ID.' );
		}

		$this->ids['parent_page_id'] = $id;

		return $this;
	}

	/**
	 * Set the option menu type.
	 *
	 * @param string $type The menu type to set.
	 * @return $this The current instance of the class.
	 */
	public function set_option_menu_type( $type = NULL )
	{
		$this->options['menu_type'] = $type ?? 'menu';

		return $this;
	}

	/**
	 * Sets the option page title.
	 *
	 * @param string $title The title of the option page.
	 * @return $this The current instance of the class.
	 */
	public function set_option_page_title( $title )
	{
		$this->options['page_title'] = $title;

		return $this;
	}

	/**
	 * Sets the title for the option menu.
	 *
	 * @param string $title The title for the option menu.
	 * @return $this The current instance of the class.
	 */
	public function set_option_menu_title( $title )
	{
		$this->options['menu_title'] = $title;

		return $this;
	}

	/**
	 * Sets the title for the option menu's submenu equivalent.
	 *
	 * @param string $title The title for the option menu.
	 * @return $this The current instance of the class.
	 */
	public function set_option_submenu_title( $title )
	{
		$this->options['submenu_title'] = $title;

		return $this;
	}

	/**
	 * Sets the capability required to access the options.
	 *
	 * @param string $capability The capability required to access the options.
	 * @return $this The current instance of the class.
	 */
	public function set_option_capability( $capability = NULL )
	{
		$this->options['capability'] = $capability ?? 'edit_theme_options';

		return $this;
	}

	/**
	 * Sets the menu icon for admin settings page.
	 *
	 * @param string $icon The icon to be set for the menu.
	 * @return $this The current instance of the class.
	 */
	public function set_option_menu_icon( $icon )
	{
		$this->options['menu_icon'] = $icon;

		return $this;
	}

	/**
	 * Sets the position of the sidebar menu link.
	 *
	 * @param int $position The position to set. If null, the default value is 99.
	 * @return $this The current instance of the class.
	 */
	public function set_option_position( $position = NULL )
	{
		$this->options['position'] = $position ?? 99;

		return $this;
	}

	/**
	 * Sets a section in the admin settings page.
	 *
	 * @param string $section_id The ID of the section.
	 * @param string $section_field_id The field ID of the section.
	 * @param mixed $details The details regarding the fields in the section.
	 * @return $this The current instance of the class.
	 */
	public function set_section( $section_id, $section_field_id, $details )
	{
		$section = [
            'section_id' => $section_id,
            'section_field_id' => $section_field_id,
            'section_details' => $details,
        ];

		$this->sections[] = $section;

		return $this;
	}
}
