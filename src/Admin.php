<?php
namespace Fkwd\Plug\Wcrfc;

use Fkwd\Plug\Wcrfc\Admin\Settings;

/**
 * Class Admin
 *
 * @package fkwdwcrfc/src
 */
class Admin extends Base {
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
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_files' ] );

        // adds plugin action links
        add_action( 'plugin_action_links', [ $this, 'add_plugin_page_action_links' ], 10, 2 );

        // create admin settings pages
        $this->create_admin_pages();
    }

    /**
     * Create admin pages and initialize them.
     */
    public function create_admin_pages() {
        // create admin settings pages
        $pages = [
            'report' => [
                'config' => new Admin\Config\Report,
                'dependencies' => []
            ],
        ];

        foreach ( $pages as $page_id => $options ) {
            $activate = false;

            // make sure all other plugin dependencies are met
            if( !empty( $options['dependencies'] ) ) {
                foreach( $options['dependencies'] as $dependency ) {
                    if( is_plugin_active( $dependency ) ) {
                        $activate = true;
                    }
                }
            } else {
                $activate = true;
            }

            if( !$activate ) {
                continue;
            }

            // create a new config class for this feature
            $config = $options['config'];
            $config->init();

            // if optional field sections/fields are set, provide them otherwise set to null
            $sections = $config->sections ?? NULL;

            // create a new settings page using wordpress admin options class
            $settings = new Settings;
            $settings->configure( $config->ids, $config->options, $sections );
        }
    }

    /**
     * Enqueues admin-specific files.
     *
     * @return void
     */
    public function enqueue_files() 
    {
        wp_enqueue_style( FKWD_PLUGIN_WCRFC_NAMESPACE  .  '-admin-style', FKWD_PLUGIN_WCRFC_DIR_URL .  'assets/dist/css/admin.css', [], FKWD_PLUGIN_WCRFC_VERSION, 'all' );
        wp_enqueue_script( FKWD_PLUGIN_WCRFC_NAMESPACE . '-admin-script', FKWD_PLUGIN_WCRFC_DIR_URL .  'assets/dist/scripts/admin.min.js', [ 'jquery' ], FKWD_PLUGIN_WCRFC_VERSION, true );
        wp_localize_script( FKWD_PLUGIN_WCRFC_NAMESPACE . '-admin-script', FKWD_PLUGIN_WCRFC_NAMESPACE . '_data', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( FKWD_PLUGIN_WCRFC_NAMESPACE . '_nonce' ),
        ] );
    }

    public function add_plugin_page_action_links( $links, $file ) 
    {            
        if ( !is_admin() || ( !empty( $file ) && $file != FKWD_PLUGIN_WCRFC_NAMESPACE ) ) {
			return $links;
        }

        $settings_url = esc_url( add_query_arg( 'page', FKWD_PLUGIN_WCRFC_NAMESPACE . '_roundup_report', admin_url( 'admin.php' ) ) );

		array_unshift( $links, '<a href="' . $settings_url . '">' . esc_html__( 'Reports', FKWD_PLUGIN_WCRFC_NAMESPACE ) . '</a>' );

		return $links;
    }
}
