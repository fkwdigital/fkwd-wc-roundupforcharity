<?php
namespace Fkwd\Plugin\Wcrfc;

use Fkwd\Plugin\Wcrfc\Service\Ajax;
use Fkwd\Plugin\Wcrfc\Utils\Discovery;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;

/**
 * Class Admin
 *
 * @package Fkwd\Plugin\Wcrfc
 */
class Admin
{
    use Singleton;

    /** @var array $features List of features registered in the admin. */
    private $features = [];
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        
    }
    
    /**
     * Initializes the function by enqueueing admin assets and creating admin settings pages.
     *
     */
    public function init() {
        // enqueue admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // register plugin feature classes
        add_action('init', [$this, 'register_features'], 1);

        // register admin ajax endpoints
        Ajax::get_instance();
    }

    /**
     * Registers and initializes features discovered in the specified configuration directory.
     *
     * This function discovers features within the provided configuration directory and namespace,
     * initializes them if they are enabled, and configures associated settings.
     *
     * @return void
     */
    public function register_features()
    {
        $config_dir       = FKWD_PLUGIN_WCRFC_DIR_PATH . 'src/Admin/Features';
        $config_namespace = 'Fkwd\\Plugin\\Wcrfc\\';
        $config_features  = 'Admin\\Features\\';

        $this->features = Discovery::discover($config_dir, $config_namespace, $config_features);
    }

    /**
     * Enqueues admin-specific files.
     *
     * @return void
     */
    public function enqueue_assets() 
    {
        wp_enqueue_style( FKWD_PLUGIN_WCRFC_NAMESPACE  .  '-admin-style', FKWD_PLUGIN_WCRFC_DIR_URL .  'assets/dist/css/admin.css', [], FKWD_PLUGIN_WCRFC_VERSION, 'all' );
        wp_enqueue_script( FKWD_PLUGIN_WCRFC_NAMESPACE . '-admin-script', FKWD_PLUGIN_WCRFC_DIR_URL .  'assets/dist/scripts/admin.min.js', [ 'jquery' ], FKWD_PLUGIN_WCRFC_VERSION, true );
        wp_localize_script( FKWD_PLUGIN_WCRFC_NAMESPACE . '-admin-script', FKWD_PLUGIN_WCRFC_NAMESPACE . '_data', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( FKWD_PLUGIN_WCRFC_NAMESPACE . '_nonce' ),
        ] );
    }
}
