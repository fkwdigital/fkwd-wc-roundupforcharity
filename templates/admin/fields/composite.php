<?php
/**
 * Composite field template
 *
 * @since 0.1.0
 * @package fkwdwcrfc
 */

// prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$field_id = esc_attr($args['label_for'] ?? $id);
$field_label = $args['label'] ?? '';
$field_name = $page_database_id . '[' . $field_id . ']';
$field_title = $args['title'] ?? '';
$field_description = $args['description'] ?? '';
$field_type = $args['type'] ?? 'text';
$field_value = $args['value'] ?? $value ?? '';
$fields = $args['fields'] ?? [];

// ensure value is an array
if (!is_array($value)) {
    $value = [];
}
?>
<div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-composite-field"
    id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>_<?php echo $field_id; ?>">
    <div class="field field-<?php echo $field_id; ?>">
        <?php foreach ($fields as $field): ?>
        <div class="composite-field-row">
        </div>
        <?php endforeach; ?>
    </div>
</div>
