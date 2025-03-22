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
            do_settings_sections( $this->page_id );
            submit_button( __( 'Run Report', FKWD_PLUGIN_WCRFC_NAMESPACE ) ); ?>
        <hr>
        <?php do_action( FKWD_PLUGIN_WCRFC_NAMESPACE . '_add_' . $this->page_id . '_form_html' ); ?>
    </form>

    <?php if( !empty( $data_class ) && is_object( $data_class ) ) { 
        $field_name = FKWD_PLUGIN_WCRFC_NAMESPACE . '_report_month'; ?>

    <?php
    $selected_month = !empty( $_POST[ $field_name ] ) ? sanitize_text_field( $_POST[ $field_name ] ) : date( 'm Y' );

    $total_round_up = $data_class->get_monthly_total_roundup( $selected_month );
    ?>

    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th>Month</th>
                <th>Total Rounded Up</th>
            </tr>
            <tr>
                <td><?php echo esc_html( date( 'F Y', strtotime( $selected_month ) ) ); ?></td>
                <td>$<?php echo number_format( $total_round_up, 2 ); ?></td>
            </tr>
    </table>
    <?php } ?>
</div>