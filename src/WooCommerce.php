<?php
namespace Fkwd\Plug\Wcrfc;

//use Fkwd\Plug\Admin;

/**
 * Class Main
 *
 * @package fkwdwcrfc/src
 */
class WooCommerce extends Base
{
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        
    }

    public function init() {
        // add checkbox to WooCommerce Classic checkout
        add_action( 'woocommerce_review_order_before_submit', [ $this, 'charity_round_up_checkbox' ] );
        add_action( 'woocommerce_cart_calculate_fees', [ $this, 'add_round_up_fee' ] );
        add_action( 'woocommerce_checkout_update_order_review', [ $this, 'fkwd_save_roundup_in_session' ] );
    }
    
    /**
     * Adds a checkbox to the WooCommerce checkout, allowing customers to round up to the nearest dollar for Loose Ends
     * 
     * @return void
     */
    public function charity_round_up_checkbox() {
        echo '<p class="form-row form-row-wide ' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee">
            <label><input type="checkbox" name="' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee" id="' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee_input" value="1" /> <span>Round up to the nearest dollar for <a href="https://looseends.org/how-this-works" target="_blank">Loose Ends</a>, a charity project that matches volunteer handwork finishers with textile projects people have left undone due to death or disability.</span></label>
                <input type="hidden" id="' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_roundup_fee_amount" name="' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_roundup_fee_amount" value="0" />
            </p>';
    }

    /**
     * Save the round-up preference in the user's session. This is triggered when the user
     * submits the checkout form.
     *
     * @param array $posted_data The posted data from the checkout form.
     * @return void
     */
    public function fkwd_save_roundup_in_session( $posted_data ) {
        parse_str( $posted_data, $parsed_data );
        if ( isset( $parsed_data[ FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee' ] ) && $parsed_data[ FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee' ] == '1' ) {
            WC()->session->set( FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee', true );
        } else {
            WC()->session->set( FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee', false );
        }
    }

    /**
     * Adds a fee to the WooCommerce cart to round up the order total to the nearest dollar.
     *
     * This function checks if the round-up option is selected either via classic
     * checkout post data or stored in the session. If selected, it calculates the
     * round-up amount by comparing the current cart total to the nearest whole dollar
     * and adds this amount as a fee to the cart.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     * @return void
     */
    public function add_round_up_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }
    
        $round_up_classic = false;
        // check if post_data is available and parse it.
        if ( isset( $_POST['post_data'] ) ) {
            parse_str( $_POST['post_data'], $parsed_data );
            $round_up_classic = isset( $parsed_data[ FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee' ] ) && 
                                $parsed_data[ FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee' ] == '1';
        }
        
        // if not found, check the session.
        if ( ! $round_up_classic ) {
            $round_up_classic = WC()->session->get( FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee' );
        }
        
        if ( $round_up_classic ) {
            $cart_total    = $cart->cart_contents_total + $cart->shipping_total;
            $rounded_total = ceil( $cart_total );
            $round_up_amount = $rounded_total - $cart_total;
    
            if ( $round_up_amount > 0 ) {
                $cart->add_fee( 'Round Up Donation', $round_up_amount, false );
            }
        }
    }

    /**
     * Calculate the amount needed to round up the cart total to the nearest dollar.
     *
     * This function checks if the WooCommerce cart is available and calculates the 
     * difference between the rounded total (to the nearest dollar) and the current 
     * cart total (including shipping). If the cart is not available, it returns 0.0.
     *
     * @return float The round up amount needed to reach the nearest dollar.
     */
    protected function calculate_round_up_amount() {
        if ( function_exists( 'WC' ) && isset( WC()->cart ) ) {
            $cart_total    = WC()->cart->cart_contents_total + WC()->cart->shipping_total;
            $rounded_total = ceil( $cart_total );
            return $rounded_total - $cart_total;
        }
        
        return 0.0;
    }
}
