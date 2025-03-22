<?php
namespace Fkwd\Plug\Wcrfc\Admin\Config;

/**
 * Class Report
 * 
 * @package fkwdwcrfc/src
 */
class Report extends Config implements Feature {

    public function __construct() {
		parent::__construct();
    }

	public function init()
	{
		try {
			$this->set_slug( 'roundupreport' )
				->set_page_settings_id( FKWD_PLUGIN_WCRFC_NAMESPACE . '_' . $this->slug . '_settings' )
				->set_page_settings_database_id( FKWD_PLUGIN_WCRFC_NAMESPACE . '_' . $this->slug . '_database_settings' )
                ->set_parent_page_id( 'woocommerce' );

			if( !empty( $this->ids['page_settings_id'] && !empty( $this->ids['page_settings_database_id'] ) ) )
			{
				$this->set_option_menu_type( 'submenu' )
					->set_option_page_title( __( 'Round Up Report', FKWD_PLUGIN_WCRFC_NAMESPACE ) )
					->set_option_menu_title( __( 'Round Up Report', FKWD_PLUGIN_WCRFC_NAMESPACE ) )
					->set_option_capability( 'manage_woocommerce' );

                $report_settings_section = [
                    'section_title' => 'Select Month to Generate Report',
                    'section_description' => 'Generates a round up report of the month selected. Defaults to the current month. Only months that have associated round up orders will appear in the dropdown.',
                    'fields' => [
                        [
                            'label_for' => 'report_month',
                            'title' => 'Select Month:',
                            'description' => 'Select the month for the report.',
                            'type' => 'select',
                        ],
                    ],
                    'class' => 'Fkwd\Plug\Wcrfc\Admin\Report'
                ];

                $this->set_section(
                    FKWD_PLUGIN_WCRFC_NAMESPACE . '_settings_form_section_roundupreport',
                    FKWD_PLUGIN_WCRFC_NAMESPACE . '_settings_form_section_roundupreport_fields',
                    $report_settings_section
                );
			}
		} catch ( \Exception $e ) {
			// log the error
            $this->send_log( 'add_report_admin_page', $e->getMessage(), get_current_user_id() );
			error_log( $e->getMessage() );
			// display an admin error notice in WordPress
			add_action( 'admin_notices', function() use ( $e ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
			} );
		}

		return $this;

	}

	public function get_enabled(): bool
	{
		// try {
		// 	if( !empty( get_option( FKWD_PLUGIN_WCRFC_NAMESPACE . '_memberful_token' ) ) )
		// 	{
		// 		return true;
		// 	}
		// } catch ( \Exception $e ) {
		// 	// log the error
		// 	error_log($e->getMessage());
		// 	// display an admin error notice in WordPress
		// 	add_action('admin_notices', function() use ($e) {
		// 		
		// 		<div class="notice notice-error">
		// 			<p><?php echo esc_html($e->getMessage());</p>
		// 		</div>
		// 		
		// 	});
		// }

		return true;
	}
}
