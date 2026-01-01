<?php
namespace Fkwd\Plugin\Wcrfc;

use Fkwd\Plugin\Wcrfc\Admin;
use Fkwd\Plugin\Wcrfc\Frontend;
use Fkwd\Plugin\Wcrfc\WooCommerce;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Strings;

/**
 * Class Main
 *
 * @package Fkwd\Plugin\Wcrfc
 */
class Main
{
    use Singleton;
    use Strings;

    /**
     * Constructor
     *
     * @return none
     */
    public function __construct()
    {
        Admin::get_instance();

        Frontend::get_instance();

        WooCommerce::get_instance();
    }

    public function init() 
    {
        if( $this->check_plugin_dependencies() === false ) {
            return;
        }
    }

    /**
     * Activate the plugin.
     * 
     * @since 0.1.0
     *
     * @return void
     */
    public function activate()
    {
        // check that dependencies are met
        if( $this->check_plugin_dependencies() === false ) {
            return;
        }
    }

    /**
     * Deactivates the plugin by removing any lingering hooks or actions.
     * 
     * @since 0.1.0
     *
     * @return void
     */
    public function deactivate()
    {
        // remove any lingering hooks or actions
        remove_action( 'init', [ $this, 'init' ] );
    }

    /**
     * Check plugin dependencies like WooCommerce and deactivates if necessary.
     * 
     * @since 0.1.0
     *
     * @return void
     */
    public function check_plugin_dependencies()
    {
        // include the plugin.php file to use is_plugin_active
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        // check if the woocommerce plugin is activated
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            // set a transient to show the admin notice on the next admin page load
            update_option(FKWD_PLUGIN_WCRFC_NAMESPACE . '_activation_failed', true);

            return false;
        } else {
            // clear the flag if woocommerce is active
            delete_option(FKWD_PLUGIN_WCRFC_NAMESPACE . '_activation_failed');
        }

        return true;
    }

    /**
     * Display a notice when the required plugin WooCommerce is missing.
     * 
     * @since 0.1.0
     *
     * @return void
     */
    public function show_missing_plugin_notice()
    {
        if ( get_option( FKWD_PLUGIN_WCRFC_NAMESPACE . '_activation_failed' ) ) {
            echo '<div class="error"><p>' . esc_html__( 'WooCommerce plugin is not active. FKWD WC Round Up For Charity requires the WooCommerce plugin to function properly. Please install and activate the WooCommerce plugin to activate functionality.', 'fkwd-checkout-roundupforcharity' ) . '</p></div>';
        }
    }
}
