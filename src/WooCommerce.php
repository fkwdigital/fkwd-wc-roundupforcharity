<?php
namespace Fkwd\Plugin\Wcrfc;

use Fkwd\Plugin\Wcrfc\WooCommerce\CheckoutBlock;
use Fkwd\Plugin\Wcrfc\WooCommerce\CheckoutClassic;
use Fkwd\Plugin\Wcrfc\Utils\Traits\Singleton;

/**
 * Class WooCommerce
 *
 * Main orchestrator for WooCommerce integration.
 *
 * @since 0.1.0
 * @package fkwdwcrfc/src
 */
class WooCommerce
{
    use Singleton;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function __construct()
    {
        CheckoutClassic::get_instance();
        //CheckoutBlock::get_instance();
    }
}
