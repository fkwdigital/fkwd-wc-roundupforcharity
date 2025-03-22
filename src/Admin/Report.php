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

    public function init() 
    {
        // adds method to support ajax action to resend missing backleads
        add_action( 'wp_ajax_roundup_report', [ $this, 'handle_roundup_report' ] );
        add_action( 'wp_ajax_nopriv_roundup_report', [ $this, 'handle_roundup_report' ] );
    }

    public function handle_roundup_report() {
        check_ajax_referer( FKWD_PLUGIN_WCRFC_NAMESPACE . '_nonce', 'nonce' );

        $data = $_POST;

        if( empty( $data['month'] ) ) {
            wp_send_json_error( [ 'message' => 'Report running failed; missing required entries.' ] );
        } else {
            $month = $data['month'];
        }

        $result = $this->get_monthly_total_roundup( $month );

        if( empty( $result ) ) {
            $result->total_orders = 0; 
            $result->total_roundup = 0;
        }

        if( !empty( $result ) ) {
            wp_send_json_success( [ 'success' => true, 'total_orders' => $result->total_orders, 'total_roundup' => $result->total_roundup ] );
        } else {
            wp_send_json_error( [ 'message' => 'Report running failed.' ] );
        }
    }


    public function get_monthly_total_roundup( $year_month = NULL ) {
        global $wpdb;

        $return = NULL;

        if( empty( $year_month ) ) {
            $year_month = date( 'Y-m' );
        }

        $date_obj = \DateTime::createFromFormat( 'Y-m', $year_month );
        if (!$date_obj) {
            $this->send_log( 'generate_monthly_report', FKWD_PLUGIN_WCRFC_NAMESPACE . ': Datetime provided in a non-valid format.', get_current_user_id() );
            error_log( FKWD_PLUGIN_WCRFC_NAMESPACE . ': Datetime provided in a non-valid format.' );
            return $return;
        }

        $start_date = $date_obj->format( 'Y-m-01' );
        $end_date = $date_obj->format( 'Y-m-t' );

        $sql =  "SELECT COUNT(DISTINCT `o`.`id`) as `total_orders`, SUM(`om`.`meta_value`) as `total_roundup`
            FROM `wp_wc_orders` as `o`
            JOIN `wp_wc_orders_meta` as `om`
            WHERE `o`.`type` = %s
            AND `o`.`status` IN (%s, %s)
            AND `o`.`date_created_gmt` BETWEEN %s AND %s
            AND `om`.`meta_key` = %s";

        $query = $wpdb->prepare( $sql,
            'shop_order',
            'wc-completed',
            'wc-processing',
            $start_date,
            $end_date,
            '_' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee_amount'
        );

        $total_round_up = $wpdb->get_row( $query );

        if( !empty( $total_round_up ) ) {
            $return = $total_round_up;
        }

        return $return;
    }

    /**
     * Retrieves an array of available months for generating a report.
     *
     * This function looks at orders with a `wc-completed` or `wc-processing` status
     * and orders that have a `_round_up_fee_amount` meta value set. It then
     * returns an array of the distinct months where such orders exist.
     *
     * @return array An array of strings in the format 'YYYY-MM'.
     */
    public function get_available_months(): array {
        global $wpdb;

        $return = [ date( 'Y-m' ) ];
    
        // Query distinct months from shop orders with relevant statuses.
        $query = "
            SELECT DISTINCT DATE_FORMAT(p.post_date, '%Y-%m') as month
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = %s
            AND p.post_type = %s
            AND p.post_status IN (%s, %s)
            ORDER BY month DESC
        ";
    
        $prepared_query = $wpdb->prepare(
            $query,
            '_' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee_amount',
            'shop_order',
            'wc-completed',
            'wc-processing'
        );
    
        $results = $wpdb->get_col( $prepared_query );
        
        if ( ! empty( $results ) ) {
            $return = $results;
        }
    
        return $return;
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
        $field_name = $field ?? 'report_month';
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
