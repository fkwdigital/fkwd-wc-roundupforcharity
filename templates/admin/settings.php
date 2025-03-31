<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<div class="wrap">
	<h1><?php echo FKWD_PLUGIN_WCRFC_NAME; ?></h1>

	<?php settings_errors( FKWD_PLUGIN_WCRFC_NAMESPACE . '-messages' ); ?>

	<form action="<?php echo esc_url(admin_url('options.php')); ?>" method="post">
		<?php
			wp_nonce_field( $this->settings_page_id . '-nonce', $this->settings_page_id . '-nonce', false, 60 * 60 * 24 );
			settings_fields( $this->settings_page_id );
			do_settings_sections( $this->settings_page_id );
			submit_button(__( 'Save Settings', FKWD_PLUGIN_WCRFC_NAMESPACE), 'primary', 'save-settings' );
		?>
	</form>
</div>
