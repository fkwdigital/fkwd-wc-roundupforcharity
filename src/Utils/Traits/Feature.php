<?php

namespace Fkwd\Plugin\Wcrfc\Utils\Traits;

/**
 * Trait Feature
 *
 * @package fkwdwcrfc/src
 */
trait Feature
{
    public $name;

    /**
     * Returns the name of the feature.
     *
     * @return string The name of the feature.
     */
    public function get_name(): string
    {
        return $this->name;
    }


    /**
     * Sets the name of the feature.
     *
     * @param string $name The name to set.
     */
    public function set_name($name): void
    {
        $this->name = $name;
    }

    /**
     * Gets whether the feature is enabled.
     *
     * @return bool True if the feature is enabled, false otherwise.
     */
    public function get_enabled(): bool
    {
        return true;
    }

    /**
     * Sets the enabled status of the feature.
     *
     * @param bool $enabled The enabled status to set.
     */
    public function set_enabled($enabled)
    {
        $this->enabled = $enabled;
        return $this->enabled;
    }

    /**
     * Gets the slug of the feature.
     *
     * @return string The slug of the feature.
     */
    public function get_slug(): string
    {
        return $this->slug;
    }

    /**
     * Sets the slug of the feature.
     *
     * @param string $slug The slug to set.
     */
    public function set_slug($slug)
    {
        $this->slug = $slug;
        return $this->slug;
    }

    /**
     * Gets the page ID for the feature's settings page.
     *
     * @return string The page ID for the feature's settings page.
     */
    public function get_page_id(): string
    {
        return $this->page_id;
    }

    /**
     * Sets the page ID of the feature's settings page.
     *
     * @param string $page_id The page ID to set.
     */
    public function set_page_id($page_id)
    {
        $this->page_id = $page_id;
        return $this->page_id;
    }

    /**
     * Gets the database ID of the feature's settings page.
     *
     * @return string The database ID of the feature's settings page.
     */
    public function get_db_id(): string
    {
        return $this->db_id;
    }

    /**
     * Sets the database ID of the feature's settings page.
     *
     * @param string $db_id The database ID to set.
     */
    public function set_db_id($db_id)
    {
        $this->db_id = $db_id;
        return $this->db_id;
    }

    /**
     * Gets the current database option value saved for this feature.
     *
     * @return array The current database option value for this feature.
     */
    public function get_db_option(): array
    {
        if (!get_option($this->get_db_id())) {
            return [];
        }

        return get_option($this->get_db_id());
    }

    /**
     * Gets the parent page ID for the feature's settings page.
     *
     * @return string The parent page ID for the feature's settings page.
     */
    public function get_parent_page_id(): string
    {
        return $this->parent_page_id;
    }

    /**
     * Sets the parent page ID for the feature's settings page.
     *
     * @param string $parent_page_id The parent page ID to set.
     */
    public function set_parent_page_id($parent_page_id)
    {
        $this->parent_page_id = $parent_page_id;
        return $this->parent_page_id;
    }

    /**
     * Gets the class name for the feature.
     *
     * @return string The class name for the feature.
     */
    public function get_class_name()
    {
        return $this->class_name;
    }

    /**
     * Sets the class name for the feature.
     *
     * @param string $class_name The class name to set.
     *
     * @return string The class name for the feature.
     */
    public function set_class_name($class_name)
    {
        $this->class_name = $class_name;
        return $this->class_name;
    }
}
