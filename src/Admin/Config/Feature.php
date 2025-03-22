<?php
namespace Fkwd\Plug\Wcrfc\Admin\Config;

/**
 * The Feature interface represents a feature in the admin area.
 */
interface Feature {

	/**
	 * Constructs a new instance of the feature.
	 */
	public function __construct();

	/**
	 * Initializes the feature.
	 */
	public function init();

	/**
	 * Gets whether the feature is enabled.
	 *
	 * @return bool True if the feature is enabled, false otherwise.
	 */
	public function get_enabled(): bool;
}
