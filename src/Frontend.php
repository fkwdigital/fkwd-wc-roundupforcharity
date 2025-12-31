<?php
namespace Fkwd\Plugin\Wcrfc;

use Fkwd\Plugin\Wcrfc\Admin\Settings;

/**
 * Class Frontend
 *
 * @package fkwdwcrfc/src
 */
class Frontend extends Base {
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
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_files' ] );
    }

    /**
     * Enqueues admin-specific files.
     *
     * @return void
     */
    public function enqueue_files() 
    {
        wp_enqueue_style( FKWD_PLUGIN_WCRFC_NAMESPACE . '-style', FKWD_PLUGIN_WCRFC_DIR_URL . 'assets/dist/css/frontend.css', array(), FKWD_PLUGIN_WCRFC_VERSION, 'all' );
        wp_enqueue_script( FKWD_PLUGIN_WCRFC_NAMESPACE . '-script', FKWD_PLUGIN_WCRFC_DIR_URL .  'assets/dist/scripts/frontend.min.js', [ 'jquery' ], FKWD_PLUGIN_WCRFC_VERSION, true );
    }
}
