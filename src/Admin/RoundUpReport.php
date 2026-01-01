<?php
namespace Fkwd\Plugin\Wcrfc\Admin;

use Fkwd\Plugin\Wcrfc\Service\Ajax;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Security;

/**
 * Class Report
 * 
 * @package Fkwd\Plugin\Wcrfc
 */
class RoundUpReport
{
    private $service_ajax;
    
    use Security;
    use Singleton;
    
    /**
     * Empty constructor.
     *
     * @return void
     */
    public function __construct() {
        $this->service_ajax = Ajax::get_instance();
    }

    /**
     * Initializes the class.
     * 
     * @return void
     */
    public function init() 
    {
        // adds the correct roundup fee to queries regarding orders
        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_query_roundupfee' ], 10, 2 );
    }

    /**
     * Handles the AJAX request for generating the report for total fees collected in a month's time.
     * 
     * @return void
     */
    public function handle_roundup_report() {
        $error_text = 'We were unable to retrieve the report information for the month selected. Please try again later.';

        $json_success = false;
        $json_message = $error_text . ' Error 1E';
        $json_data = [];
        $json_return = ['success' => $json_success, 'message' => $json_message, 'data' => $json_data];
        
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is handled in the trait Security.
        $this->validate_nonce($_POST);

        $data = $this->service_ajax->sanitize_ajax_dashboard_POST(
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is handled in the trait Security.
            $_POST,
            [
                'month' => 'string',
            ]
        );

        // cant run the report without a valid date passed
        if (empty($data['month'])) {
            $json_return['message'] = $error_text . ' Error 1DN';

            wp_send_json_error($json_return, 200);
        }

        $result = $this->get_monthly_total_roundup( $data );

        if ($result['success']) {
            wp_send_json_success($result, 200);
        } else {
            wp_send_json_error($result, 200);
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
     * @throws \InvalidArgumentException If the provided year_month is not in the correct format.
     */
    public function get_monthly_total_roundup($data) 
    {        
        $error_text = 'We were unable to retrieve the report information for the month selected. Please try again later.';

        $json_return = [
            'success' => false,
            'message' => $error_text . ' Error 1E',
            'data' => null,
        ];
        
        if (empty($data['month'])) {
            $json_return['message'] = $error_text . ' Error 1DN';

            return $json_return;
        }

        // this has already been cleaned in the previous function, so set it for ease of use
        $reporting_month = $data['month'];

        // validate the year-month only format
        $date = \DateTime::createFromFormat('Y-m-d', $reporting_month);

        if (!$date || $date->format('Y-m-d') !== $reporting_month) {
            $json_return['message'] = $error_text . ' Error 1DF';

            return $json_return;
        }

        $start_date = $date->format('Y-m-01');
        $end_date = $date->format('Y-m-t');

        $order_query = new \WC_Order_Query([
            'status' => ['wc-completed', 'wc-processing'],
            'date_created' => $start_date . '...' . $end_date,
            'limit' => -1,
        ]);

        $orders = $order_query->get_orders();

        if (empty($orders)) {
            $json_return['success'] = true;
            $json_return['message'] = 'Report generated successfully, but no orders were found.';
            $json_return['data'] = [
                'total_orders' => 0,
                'total_roundup' => 0.00,
            ];
            return $json_return;
        }

        $order_count = 0;
        $total_fees = 0.00;

        foreach ($orders as $order) {
            $fees = $order->get_fees();

            foreach ($fees as $fee) {
                if ($fee->get_name() === 'Round Up Donation') {
                    $order_count++;
                    $total_fees += (float) $fee->get_total();
                    break;
                }
            }
        }

        $json_return['success'] = true;
        $json_return['message'] = 'Report generated successfully.';
        $json_return['data'] = [
            'total_orders' => $order_count,
            'total_roundup' => $total_fees,
        ];

        return $json_return; 
    }

    /**
     * Retrieve an array of available report months, which is a list of
     * months that have round up fee data associated with them.
     *
     * The results are grouped and summed by month, and ordered in descending
     * order by month.
     *
     * @return array An array of available report months in the format 'YYYY-MM'.
     */
    public function get_available_report_months(): array
    {
        global $wpdb;

        if(empty($wpdb)) {
            return [
                '' => 'No donations found'
            ];
        }

        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $orders_table = $wpdb->prefix . 'wc_orders';

        // query months with round up fee data, grouped and summed
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT DATE_FORMAT(o.date_created_gmt, '%%Y-%%m')
                FROM %s oi
                INNER JOIN %s o ON oi.order_id = o.id
                WHERE oi.order_item_type = 'fee'
                    AND oi.order_item_name = %s
                    AND o.status IN ('wc-completed', 'wc-processing')
                ORDER BY 1 DESC",
                $order_items_table,
                $orders_table,
                'Round Up Donation'
            )
        );

        if (empty($results)) {
            return [
                '' => 'No donations found'
            ];
        }

        $options = [];

        foreach ($results as $year_month) {
            $options[gmdate('Y-m-d', strtotime($year_month . '-01'))] = gmdate('F Y', strtotime($year_month));
        }

        return $options;
    }

    public function handle_query_roundupfee( $query, $query_vars ) {
        if ( ! empty( $query_vars['roundupfee'] ) ) {
            $query['meta_query'][] = array(
                'key' => 'roundupfee',
                'value' => esc_attr( $query_vars['roundupfee'] ),
            );
        }
    
        return $query;
    }
}
