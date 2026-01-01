<?php 
namespace Fkwd\Plugin\Wcrfc\WooCommerce;

use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Strings;

/**
 * Class CheckoutClassic
 * 
 * @package Fkwd\Plugin\Wcrfc
 */
class CheckoutClassic
{
    use Singleton;
    use Strings;

    /** @var string */
    private $session_key = FKWD_PLUGIN_WCRFC_NAMESPACE . '_round_up_fee';

    /** @var string */
    private $fee_name = 'Round Up Donation';

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Initialize hooks.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function init()
    {
        // classic checkout hooks
        add_action('woocommerce_review_order_before_submit', [$this, 'render_round_up_checkbox']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'add_round_up_fee']);
        add_action('woocommerce_checkout_update_order_review', [$this, 'save_roundup_in_session']);
        
        // ajax handler for checkbox state
        add_action('wp_ajax_' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_update_roundup', [$this, 'ajax_update_roundup']);
        add_action('wp_ajax_nopriv_' . FKWD_PLUGIN_WCRFC_NAMESPACE . '_update_roundup', [$this, 'ajax_update_roundup']);
    }

    /**
     * Get the session key.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function get_session_key(): string
    {
        return $this->session_key;
    }

    /**
     * Get the fee name.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function get_fee_name(): string
    {
        return $this->fee_name;
    }

    /**
     * Render the round up checkbox on classic checkout.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function render_round_up_checkbox()
    {
        $data = get_option(FKWD_PLUGIN_WCRFC_NAMESPACE . '_roundupreport_database_settings');
        $description = !empty($data['roundup_description']) 
            ? $data['roundup_description'] 
            : 'Round up to the nearest dollar for charity.';

        $checked = $this->is_round_up_enabled() ? 'checked="checked"' : '';
        $amount = $this->calculate_round_up_amount();

        echo '<p class="form-row form-row-wide ' . $this->clean_string(FKWD_PLUGIN_WCRFC_NAMESPACE . '-round-up-fee', ['type' => 'attribute']) . '">
            <label>
                <input type="checkbox" 
                    name="' . $this->clean_string($this->session_key, ['type' => 'attribute']) . '" 
                    id="' . $this->clean_string($this->session_key, ['type' => 'attribute']) . '_input" 
                    value="1" ' . $this->clean_string($checked, ['type' => 'html']) . ' />
                <span>' . $this->clean_string($description) . '</span>
            </label>
            <input type="hidden" 
                id="' . $this->clean_string(FKWD_PLUGIN_WCRFC_NAMESPACE . '_roundup_amount', ['type' => 'attribute']) . '" 
                value="' . $this->clean_string($amount, ['type' => 'float']) . '" />
        </p>';
    }

    /**
     * Save round up preference to session.
     *
     * @since 0.1.0
     *
     * @param string|array $posted_data Posted checkout data.
     * @return void
     */
    public function save_roundup_in_session($posted_data)
    {
        // Nonce is verified by WooCommerce before this hook fires.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies nonce in checkout flow.
        if (is_string($posted_data)) {
            parse_str($posted_data, $posted_data);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies nonce in checkout flow.
        $enabled = isset($posted_data[$this->session_key]) && $posted_data[$this->session_key] === '1';
        
        $this->set_round_up_session($enabled);
    }

    /**
     * Ajax handler for updating round up state.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function ajax_update_roundup()
    {
        check_ajax_referer(FKWD_PLUGIN_WCRFC_NAMESPACE . '_nonce', 'nonce');

        $enabled = isset($_POST['enabled']) && $_POST['enabled'] === '1';
        
        $this->set_round_up_session($enabled);

        WC()->cart->calculate_totals();

        wp_send_json_success([
            'enabled' => $enabled,
            'amount' => $this->calculate_round_up_amount(),
        ]);
    }

    /**
     * Add round up fee to cart.
     *
     * @since 0.1.0
     *
     * @param \WC_Cart $cart The cart object.
     * @return void
     */
    public function add_round_up_fee($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (!WC()->session) {
            return;
        }

        // check post_data first (during checkout update)
        $enabled = $this->check_post_data_for_roundup();

        // fallback to session
        if ($enabled === null) {
            $enabled = $this->is_round_up_enabled();
        }

        if (!$enabled) {
            return;
        }

        $amount = $this->calculate_round_up_amount_from_cart($cart);

        if ($amount > 0) {
            $cart->add_fee($this->fee_name, $amount, false);
        }
    }

    /**
     * Check if round up is enabled in session.
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public function is_round_up_enabled(): bool
    {
        if (!WC()->session) {
            return false;
        }

        return WC()->session->get($this->session_key, false) === true;
    }

    /**
     * Set round up session value.
     *
     * @since 0.1.0
     *
     * @param bool $enabled Whether round up is enabled.
     * @return void
     */
    public function set_round_up_session(bool $enabled): void
    {
        if (!WC()->session) {
            return;
        }

        WC()->session->set($this->session_key, $enabled);
    }

    /**
     * Check POST data for round up preference.
     *
     * @since 0.1.0
     *
     * @return bool|null True/false if found, null if not present.
     */
    private function check_post_data_for_roundup(): ?bool
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies nonce in checkout flow.
        if (!isset($_POST['post_data'])) {
            return null;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies nonce in checkout flow.
        $post_data = sanitize_text_field(wp_unslash($_POST['post_data']));

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies nonce in checkout flow.
        parse_str($post_data, $parsed_data);

        if (!array_key_exists($this->session_key, $parsed_data)) {
            return null;
        }

        return $parsed_data[$this->session_key] === '1';
    }

    /**
     * Calculate round up amount from cart object.
     *
     * @since 0.1.0
     *
     * @param \WC_Cart $cart The cart object.
     * @return float
     */
    public function calculate_round_up_amount_from_cart($cart): float
    {
        $cart_total = $cart->get_cart_contents_total() + $cart->get_shipping_total() + $cart->get_taxes_total();
        $rounded_total = ceil($cart_total);
        $difference = $rounded_total - $cart_total;

        // if difference is 0, round up to next dollar
        if ($difference < 0.01) {
            $difference = 1.00;
        }

        return round($difference, 2);
    }

    /**
     * Calculate round up amount from global cart.
     *
     * @since 0.1.0
     *
     * @return float
     */
    public function calculate_round_up_amount(): float
    {
        if (!function_exists('WC') || !WC()->cart) {
            return 0.0;
        }

        return $this->calculate_round_up_amount_from_cart(WC()->cart);
    }
}
