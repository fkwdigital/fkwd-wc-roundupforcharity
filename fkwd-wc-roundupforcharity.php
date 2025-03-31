<?php
/**
 * Plugin Name:       FKWD WC Roundup for Charity
 * Plugin URI:        https://fkwdigital.com/wordpress
 * Description:       Adds a checkbox to the WooCommerce checkout to allow a user to "round up" their order total to the nearest dollar. Report is generated that you can use to manually send to a charity at your choice of interval.
 * Version:           0.1.3
 * Requires at least: 6.7
 * Requires PHP:      8.0
 * Author:            FKW Digital
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       fkwdwcrfc
 * Update URI:        https://github.com/fkwdigital/fkwd-wc-roundupforcharity
 *
 * @package Fkwdwcrfc
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
 

/**
 * Plugin constants.
 *
 * @since 1.0.0
 */
if ( ! defined( 'FKWD_PLUGIN_WCRFC_VERSION' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_VERSION', '0.1.2' );
}

if ( ! defined( 'FKWD_PLUGIN_WCRFC_DB_VERSION' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_DB_VERSION', '1.0.0' );
}

if ( ! defined( 'FKWD_PLUGIN_WCRFC_NAME' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_NAME', 'FKWD WC Roundup for Charity' );
}

if ( ! defined( 'FKWD_PLUGIN_WCRFC_NAMESPACE' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_NAMESPACE', 'fkwdwcrfc' );
}

if ( ! defined( 'FKWD_PLUGIN_WCRFC_DIR_PATH' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'FKWD_PLUGIN_WCRFC_DIR_URL' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_DIR_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * The child theme class autoloader.
 *
 * @since 1.0.0
 */
$plugin_loader = FKWD_PLUGIN_WCRFC_DIR_PATH . '/vendor/autoload.php';

if ( file_exists( $plugin_loader ) ) {
    require_once( $plugin_loader );
} else {
    wp_die( 'Cannot locate the ' . FKWD_PLUGIN_WCRFC_NAME . ' plugin autoloader.' );
}

/**
 * Activation and deactivation hooks.
 *
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'activate_fkwdwcrfc' );
register_deactivation_hook( __FILE__, 'deactivate_fkwdwcrfc' );

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'fkwdwcrfc' );

/**
 * Get the instance of Main class and set up the plugin.
 *
 * @since 1.0.0
 * @return Main|null
 */
function fkwdwcrfc() {
    $admin = new Fkwd\Plug\Wcrfc\Admin();
    $admin->init();

    $wc = new Fkwd\Plug\Wcrfc\WooCommerce();
    $wc->init();

    $frontend = new Fkwd\Plug\Wcrfc\Frontend();
    $frontend->init();

    return Fkwd\Plug\Wcrfc\Main::get_instance();
}

/**
 * Activate the plugin.
 *
 * @since 1.0.0
 */
function activate_fkwdwcrfc() {
    fkwdwcrfc()->activate();
}

/**
 * Deactivate the plugin.
 *
 * @since 1.0.0
 */
function deactivate_fkwdwcrfc() {
    fkwdwcrfc()->deactivate();
}
