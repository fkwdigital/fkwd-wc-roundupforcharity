<?php
/**
 * Plugin Name:       FKWD Checkout Roundup for Charity
 * Plugin URI:        https://fkwdigital.com/wordpress
 * Description:       Adds a checkbox to the WooCommerce checkout to allow a user to "round up" their order total to the nearest dollar. Report is generated that you can use to manually send to a charity at your choice of interval.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Tested up to:      6.9
 * Requires PHP:      8.0
 * Stable tag:        1.0.0
 * Author:            FKW Digital
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       fkwd-wc-roundupforcharity
 *
 * @package Fkwdwcrfc
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Plugin constants.
 *
 * @since 0.1.0
 */
if ( ! defined( 'FKWD_PLUGIN_WCRFC_VERSION' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_VERSION', '1.0.0' );
}

if ( ! defined( 'FKWD_PLUGIN_WCRFC_DB_VERSION' ) ) {
    define( 'FKWD_PLUGIN_WCRFC_DB_VERSION', '0.1.0' );
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
 * Check PHP version requirement
 *
 * @since 0.1.0
 */
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>';
        echo sprintf(
            esc_html__('FKWD WC Roundup for Charity requires PHP 8.0 or higher. You are running PHP %s. Please upgrade PHP to use this plugin.', 'fkwdwcrfc'),
            PHP_VERSION
        );
        echo '</p></div>';
    });

    return;
}

/**
 * Check WordPress version requirement
 *
 * @since 0.1.0
 */
if (version_compare(get_bloginfo('version'), '6.8', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>';
        echo esc_html__('FKWD WC Roundup for Charity requires WordPress 6.8 or higher. Please upgrade WordPress to use this plugin.', 'fkwdwcrfc');
        echo '</p></div>';
    });

    return;
}

/**
 * The class autoloader.
 *
 * @since 1.0.0
 */
$fkwdwcrfc_plugin_loader =  plugin_dir_path(__FILE__) . '/vendor/autoload.php';

if (file_exists($fkwdwcrfc_plugin_loader)) {
    require_once($fkwdwcrfc_plugin_loader);
} else {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>';
        echo esc_html__('FKWD WC Roundup for Charity has had a critical issue. Please contact <a href="mailto:support@fkwdigital.com">the plugin author</a> to resolve this issue.', 'fkwdwcrfc');
        echo '</p></div>';
    });
}

$fkwdwcrfc_main_class = Fkwd\Plugin\Wcrfc\Main::get_instance();

/**
 * Hooks the code that runs when the plugin is activated
 *
 */
register_activation_hook(__FILE__, array( $fkwdwcrfc_main_class, 'activate' ));

/**
 * Hooks the code that runs when the plugin is deactivated
 *
 */
register_deactivation_hook(__FILE__, array( $fkwdwcrfc_main_class, 'deactivate' ));
