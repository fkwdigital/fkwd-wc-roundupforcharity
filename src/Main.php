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
 * @package fkwdwcrfc/src
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
     * This function is called when the plugin is activated. It performs the following tasks:
     * 2. Checks that all plugin dependencies are met.
     * 3. Checks and installs any needed database tables.
     *
     * @return void
     */
    public function activate()
    {
        if ( get_option( 'fkwd_plugin_wcrfc_db_version' ) !== FKWD_PLUGIN_WCRFC_DB_VERSION ) {
            // Run your installation / upgrade routines here.
            $this->maybe_upgrade_database();
            update_option( 'fkwd_plugin_wcrfc_db_version', FKWD_PLUGIN_WCRFC_DB_VERSION );
        }

        // check that dependencies are met
        if( $this->check_plugin_dependencies() === false ) {
            return;
        }
    }

    /**
     * Deactivates the plugin by removing any lingering hooks or actions.
     *
     * @return void
     */
    public function deactivate()
    {
        // Remove any lingering hooks or actions
        remove_action( 'init', [ $this, 'init' ] );
    }

    /**
     * Check plugin dependencies and deactivate if necessary.
     *
     * This function checks if the 'memberful-wp' plugin is activated. If it is not,
     * the function adds an action to display an admin notice and returns, effectively
     * deactivating the current plugin.
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
            // clear the flag if WooCommerce is active
            delete_option(FKWD_PLUGIN_WCRFC_NAMESPACE . '_activation_failed');
        }

        return true;
    }

    /**
     * Display a notice when the required plugin Memberful WP is missing.
     *
     * @return void
     */
    public function show_missing_plugin_notice()
    {
        if ( get_option( FKWD_PLUGIN_WCRFC_NAMESPACE . '_activation_failed' ) ) {
            echo '<div class="error"><p>' . esc_html__( 'WooCommerce plugin is not active. ' . FKWD_PLUGIN_WCRFC_NAME . ' requires the WooCommerce plugin to function properly. Please install and activate the WooCommerce plugin to activate functionality.', FKWD_PLUGIN_WCRFC_NAMESPACE ) . '</p></div>';
        }
    }

    /**
     * Installs the required database tables.
     *
     * @return void
     */
    private function install_database_tables()
    {
        global $wpdb;

        if(empty($wpdb)) {
            return;
        }

        // for tables specific to this plugin
        $db_slug = 'wcrfc';

        // always add the logs table for fkwd plugins in general if its missing
        $logs_table_name = $wpdb->prefix . 'fkwd_logs';

        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '{$logs_table_name}'") != $logs_table_name) {
            $logs_sql = "CREATE TABLE {$logs_table_name} (
                `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) DEFAULT NULL,
                `action` VARCHAR(255) NOT NULL,
                `log` TEXT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) {$charset_collate};";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($logs_sql);
        }
    }

    /**
     * Checks if the database version is outdated and if so, installs the required database tables.
     *
     * @return void
     */
    public function maybe_upgrade_database() 
    {
        $installed_version = get_option( 'fkwd_plugin_wcrfc_db_version' );
    
        if ( version_compare( $installed_version, FKWD_PLUGIN_WCRFC_DB_VERSION, '<' ) ) {
            $this->install_database_tables(); 
            update_option( 'fkwd_plugin_wcrfc_db_version', FKWD_PLUGIN_WCRFC_DB_VERSION );
        }
    }
}
