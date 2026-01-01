<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

$report_months_options = $data_class->get_available_report_months();
?>
<div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-settings-<?php echo $page_id; ?>-form wrap">
	<h1><?php echo esc_html( $page_options['page_title'] ); ?></h1>

	<?php settings_errors( FKWD_PLUGIN_WCRFC_NAMESPACE . '-messages' ); ?>

	<form action="<?php echo esc_url(admin_url('options.php')); ?>" method="post" onkeydown="return event.key != 'Enter';">
		<?php
			wp_nonce_field( $page_id . '-nonce', $page_id . '-nonce', false, 60 * 60 * 24 );
			settings_fields( $page_id );
			do_settings_sections( $page_id );
			submit_button(__( 'Save Settings', FKWD_PLUGIN_WCRFC_NAMESPACE), 'primary', 'save-settings' );
		?>
        <?php do_action( FKWD_PLUGIN_WCRFC_NAMESPACE . '_add_' . $page_id . '_form_html' ); ?>
	</form>
    <div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-generate-report-form">
        <h2>Generate Report by Month</h2>
        <select id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-report-month-select-field">
            <option value="">Select Month</option>
            <?php foreach ( $report_months_options as $report_key => $report_month ) : ?>
                <option value="<?php echo $report_key; ?>"><?php echo $report_month; ?></option>
            <?php endforeach; ?>
        </select>
        <button id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-generate-report" class="button button-secondary">Generate Report</button>
        <table class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE ?>-report-results-table" cellspacing="0">
            <thead>
                <tr>
                    <th>Total Orders</th>
                    <th>Total Revenue to Distribute</th>
                </tr>
                <tr>
                    <td class="total-orders"></td>
                    <td class="total-revenue"></td>
                </tr>
        </table>
    </div>
</div>
