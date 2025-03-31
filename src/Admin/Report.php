<?php
namespace Fkwd\Plug\Wcrfc\Admin;

use Fkwd\Plug\Wcrfc\Base;

/**
 * Class Report
 * 
 * @package fkwdwcrfc/src
 */
class Report extends Base 
{
    /**
     * Constructor for the Report class.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Initializes the Report class.
     * 
     * Adds the necessary actions to support the AJAX request for the report.
     * 
     * @return void
     */
    public function init() 
    {
        // adds method to support ajax action to resend missing backleads
        add_action( 'wp_ajax_roundup_report', [ $this, 'handle_roundup_report' ] );
        add_action( 'wp_ajax_nopriv_roundup_report', [ $this, 'handle_roundup_report' ] );
    }

    /**
     * Handles the AJAX request for the report.
     * 
     * This function runs a SQL query to get the total number of orders and the total
     * rounded up amount for the given month. It also handles the case where the
     * month is not provided.
     * 
     * @return void
     */
    public function handle_roundup_report() {
        check_ajax_referer( FKWD_PLUGIN_WCRFC_NAMESPACE . '_nonce', 'nonce' );

        $data = $_POST;

        if( empty( $data['month'] ) ) {
            wp_send_json_error( [ 'message' => 'Report running failed; missing required entries.' ] );
        } else {
            $month = $data['month'];
        }

        $result = $this->get_monthly_total_roundup( $month );

        if( !empty( $result ) ) {
            wp_send_json_success( [ 'success' => true, 'total_orders' => $result['total_orders'], 'total_roundup' => $result['total_roundup'] ] );
        } else {
            wp_send_json_error( [ 'message' => 'Report running failed.' ] );
        }
    }

    /**
     * Retrieves the total orders and total roundup amount for the given month.
     *
     * This function looks at orders with a `wc-completed` or `wc-processing` status
     * and orders that have a `_round_up_fee_amount` meta value set. It then returns
     * an object with two properties: `total_orders` and `total_roundup`.
     *
     * @param string $year_month The month for which to retrieve the total orders
     *                            and total roundup amount.
     * @return object|null An object with two properties: `total_orders` and `total_roundup`.
     */
    public function get_monthly_total_roundup( $year_month = NULL ) {
        if( empty( $year_month ) ) {
            $year_month = date( 'Y-m' );
        }

        $date_obj = \DateTime::createFromFormat( 'Y-m', $year_month );
        if (!$date_obj) {
            $this->send_log( 'generate_monthly_report', FKWD_PLUGIN_WCRFC_NAMESPACE . ': Datetime provided in a non-valid format.', get_current_user_id() );
            error_log( FKWD_PLUGIN_WCRFC_NAMESPACE . ': Datetime provided in a non-valid format.' );
            return;
        }

        $start_date = $date_obj->format( 'Y-m-01' );
        $end_date = $date_obj->format( 'Y-m-t' );

        $order_query = new \WC_Order_Query;

        $order_query->set( 'status', array( 'wc-completed', 'wc-processing' ) );
        $order_query->set( 'date_created', $start_date . '...' . $end_date );

        $orders = $order_query->get_orders();

        $order_count = 0;
        $total_fees = 0.00;

        foreach ( $orders as $order ) {
            $fees = $order->get_total_fees();

            if( $fees > 0 ) {
                $order_count++;
                $total_fees += $fees;
            }
        }

        return array( 'total_orders' => $order_count, 'total_roundup' => floatval( $total_fees ) ); 
    }

    /**
     * Retrieves an array of available months for generating a report.
     *
     * This function looks at orders with a `wc-completed` or `wc-processing` status. It then
     * returns an array of the distinct months where such orders exist.
     *
     * @return array An array of strings in the format 'YYYY-MM'.
     */
    public function get_available_months(): array {
        $order_query = new \WC_Order_Query;

        $order_query->set( 'status', array( 'wc-completed', 'wc-processing' ) );

        $orders = $order_query->get_orders();

        $months_with_fees = [ date( 'Y-m' ) ];

        foreach( $orders as $order ) {
            $fees = $order->get_total_fees();
            $date_created = date( 'Y-m', strtotime( $order->get_date_created() ) );

            if( $fees > 0 && !in_array( $date_created, $months_with_fees ) ) {
                $months_with_fees[] = $date_created;
            }
        }

        return $months_with_fees;
    }

    /**
     * Generates HTML for a form element to select available months and displays
     * the total roundup amount for the selected month.
     *
     * This function retrieves the available months with roundup orders and 
     * creates a dropdown menu for selecting a month. It also calculates the 
     * total roundup amount for the selected month and displays it in a table.
     *
     * The selected month is determined by POST data or defaults to the first 
     * available month. The total roundup is fetched from the database for the 
     * selected month.
     * 
     * @return void
     */
    public function get_available_options( $field ) {
        if( empty( $field ) && empty( $field['label_for'] ) && $field['label_for'] != 'report_month' ) {
            return;
        }

        $options = [
            'default' => [
                'value' => date( 'F Y' ),
                'label' => date( 'F Y' )
            ]
        ];
        
        $available_months = $this->get_available_months();

        if ( !empty( $available_months ) || is_array( $available_months ) ) {
            foreach ( $available_months as $month ) {
                $month = sanitize_text_field( $month );
                $month_label = date( 'F Y', strtotime( $month ) );
                $options['options'] = '<option value="' . esc_attr( $month ) . '">' . esc_html( $month_label ) . '</option>';
            }
        }

        return $options;
    }
}
