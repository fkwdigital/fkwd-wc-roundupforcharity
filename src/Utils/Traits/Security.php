<?php

namespace Fkwd\Plugin\Wcrfc\Utils\Traits;

use Fkwd\Plugin\Wcrfc\Utils\Traits\Strings;

/**
 * Handles all security and verification actions
 */
trait Security
{
    use Strings;

    /**
     * Validates the nonce value for a given post request.
     *
     * This function checks the nonce value from the post data against the expected
     * nonce for the FKWD plugin namespace. If the nonce verification fails, a JSON
     * error response is sent indicating a security check failure.
     *
     * @param array $post The post data containing the nonce.
     * @return bool True if the nonce is valid, otherwise a JSON error is sent.
     */
    public function validate_nonce($post): bool
    {
        // validate nonce value
        if (empty($post['nonce']) || ! wp_verify_nonce($post['nonce'], FKWD_PLUGIN_WCRFC_NAMESPACE . '_nonce')) {
            wp_send_json_error(['message' => $this->safe_translation('Security check failed.', FKWD_PLUGIN_WCRFC_NAMESPACE)]);
        }

        return true;
    }
}
