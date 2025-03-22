<?php if ( ! defined( 'ABSPATH' ) ) exit; // exit if accessed directly ?>
<div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-settings-<?php echo $this->page_id; ?>-form wrap">
    <h1><?php echo esc_html( $this->page_options['page_title'] ); ?></h1>

    <?php settings_errors( FKWD_PLUGIN_WCRFC_NAMESPACE . '_settings_messages' ); settings_errors(); ?>

    <form method="post" action="options.php" id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>_settings_form"
        onkeydown="return event.key != 'Enter';">
        <?php
            // access all potential existing form values
            settings_fields( $this->page_id );
            // loop through and display all potential field options
            do_settings_sections( $this->page_id ); ?>
            <button class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE ?>-roundup-report-button <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE ?>-buttons button button-secondary">Run Report <span class="spinner"></span></button>
        <hr>
        <?php do_action( FKWD_PLUGIN_WCRFC_NAMESPACE . '_add_' . $this->page_id . '_form_html' ); ?>
    </form>
    <table class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE ?>-roundup-report-table" cellspacing="0">
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
