<?php

namespace Fkwd\Plugin\Wcrfc\Utils\Traits;

use Fkwd\Plugin\Wcrfc\Utils\Traits\Database;

/**
 * Trait Logging
 *
 * @package fkwdwcrfc/src
 */
trait Logging
{
    use Database;

    /**
     * Writes a log entry.
     *
     * @param string $action The type of event this log entry is for. e.g. 'order_created', 'order_updated', etc.
     * @param string $log The log entry to be recorded.
     * @param int|null $user_id The user ID to associate with the log entry, if applicable.
     * @return bool True if log entry was successfully inserted, false otherwise.
     */
    public function send_log(string $action, string $log, array $args = []): bool
    {
        $table = $this->wp_fkwd_tables['logs'];

        $data = array(
            'action'     => sanitize_text_field($action),
            'log'        => $log,
            'created_at' => current_time('mysql'),
        );

        $format = array('%s', '%s', '%s');

        if (!empty($args['user_id'])) {
            $data['user_id'] = absint($args['user_id']) ?? null;
            $format[] = '%d';
        }

        if (!empty($args['item_id'])) {
            $data['item_id'] = absint($args['item_id']) ?? null;
            $format[] = '%d';
        }

        $result = $this->wpdb->insert($table, $data, $format);

        if (false === $result) {
            error_log(FKWD_PLUGIN_WCRFC_NAMESPACE . ': Failed to insert log entry. Error: ' . $this->wpdb->last_error);
            return false;
        }

        return true;
    }
}
