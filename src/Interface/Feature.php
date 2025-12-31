<?php

namespace Fkwd\Plugin\Wcrfc\Interface;

/**
 * The Feature interface represents a feature in the admin area.
 */
interface Feature
{
    /**
     * Constructs a new instance of the feature.
     */
    public function __construct();

    /**
     * Initializes the feature.
     */
    public function register_new_admin_settings();

    /**
     * Gets whether the feature is enabled.
     *
     * @return bool True if the feature is enabled, false otherwise.
     */
    public function get_enabled(): bool;
}
