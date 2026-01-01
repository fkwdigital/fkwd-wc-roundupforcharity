<?php
namespace Fkwd\Plugin\Wcrfc;

use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;

/**
 * Class Frontend
 *
 * @package fkwdwcrfc/src
 */
class Frontend {
    use Singleton;

    /**
     * Empty constructor.
     *
     * @return void
     */
    public function __construct() 
    {
        
    }
    
    /**
     * Initializes the function by enqueueing frontend assets.
     *
     */
    public function init() 
    {
        // enqueue frontend assets
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_files' ] );
    }

    /**
     * Enqueues frontend-specific files.
     *
     * @return void
     */
    public function enqueue_files() 
    {
        wp_enqueue_style( FKWD_PLUGIN_WCRFC_NAMESPACE . '-style', FKWD_PLUGIN_WCRFC_DIR_URL . 'assets/dist/css/frontend.css', [], FKWD_PLUGIN_WCRFC_VERSION, 'all' );
        wp_enqueue_script( FKWD_PLUGIN_WCRFC_NAMESPACE . '-script', FKWD_PLUGIN_WCRFC_DIR_URL .  'assets/dist/scripts/frontend.min.js', [ 'jquery' ], FKWD_PLUGIN_WCRFC_VERSION, true );
    }
}
