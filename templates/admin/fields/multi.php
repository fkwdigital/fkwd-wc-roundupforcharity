<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

$multi_value = is_array($value) ? $value : (!empty($value) ? [$value] : []);
$field_type = $options['field_type'] ?? 'text';
$max_items = $options['max_items'] ?? 10;
$min_items = $options['min_items'] ?? 1;
$item_label = $options['item_label'] ?? 'Item';
$is_composite = ($field_type === 'composite');
$composite_fields = $is_composite ? ($options['fields'] ?? []) : [];

// ensure minimum items for composite fields
if ($is_composite && count($multi_value) < $min_items) {
    while (count($multi_value) < $min_items) {
        $multi_value[] = [];
    }
}
?>
<div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-multi-input-field <?php echo $is_composite ? FKWD_PLUGIN_WCRFC_NAMESPACE . '-composite-field' : ''; ?>"
    id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>_<?php echo $id; ?>"
    data-field-type="<?php echo esc_attr($field_type); ?>"
    data-max-items="<?php echo esc_attr($max_items); ?>"
    data-min-items="<?php echo esc_attr($min_items); ?>"
    data-field-name="<?php echo esc_attr($page_database_id . '[' . $id . ']'); ?>"
    data-item-label="<?php echo esc_attr($item_label); ?>">
    <div class="multi-input-container">
        <?php if ($is_composite): ?>
        <?php foreach ($multi_value as $idx => $item_data): ?>
        <div class="multi-input-item composite-item"
            data-index="<?php echo $idx; ?>">
            <div class="composite-item-header">
                <label
                    class="multi-input-label"><?php echo esc_html($item_label); ?>
                    <?php echo $idx + 1; ?></label>
                <button type="button" class="remove-item button"
                    <?php echo (count($multi_value) <= $min_items) ? 'style="display:none;"' : ''; ?>>×</button>
            </div>
            <div class="composite-item-fields">
                <?php foreach ($composite_fields as $field):
                    $field_key = $field['name'];
                    $field_value = $item_data[$field_key] ?? '';
                    $field_input_type = $field['type'] ?? 'text';
                    ?>
                <div class="composite-field-row">
                    <label
                        for="<?php echo $id; ?>_<?php echo $idx; ?>_<?php echo $field_key; ?>">
                        <?php echo esc_html($field['label']); ?>
                        <?php if (!empty($field['required'])): ?>
                        <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <?php if ($field_input_type == 'textarea' || $field_input_type == 'html'): ?>
                    <textarea
                        id="<?php echo $id; ?>_<?php echo $idx; ?>_<?php echo $field_key; ?>"
                        name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $idx; ?>][<?php echo $field_key; ?>]"
                        placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                        rows="<?php echo esc_attr($field['rows'] ?? 3); ?>"
                        class="multi-input-field composite-field-input field-<?php echo $field_input_type; ?>"
                        <?php echo !empty($field['required']) ? 'required' : ''; ?>><?php echo wp_kses_post($field_value); ?></textarea>
                    <?php elseif ($field_input_type == 'select'): ?>
                    <select
                        id="<?php echo $id; ?>_<?php echo $idx; ?>_<?php echo $field_key; ?>"
                        name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $idx; ?>][<?php echo $field_key; ?>]"
                        class="multi-input-field composite-field-input field-<?php echo $field_input_type; ?>"
                        <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                        <option value="">
                            <?php echo esc_html($field['placeholder'] ?? ''); ?>
                        </option>
                        <?php foreach ($field['options'] as $option): ?>
                        <option
                            value="<?php echo esc_attr($option['value']); ?>"
                            <?php echo ($field_value == $option['value']) ? 'selected' : ''; ?>><?php echo esc_html($option['label']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php elseif ($field_input_type == 'checkbox' || $field_input_type == 'radio'): ?>
                    <input
                        type="<?php echo esc_attr($field_input_type); ?>"
                        id="<?php echo $id; ?>_<?php echo $idx; ?>_<?php echo $field_key; ?>"
                        name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $idx; ?>][<?php echo $field_key; ?>]"
                        value="1"
                        class="multi-input-field composite-field-input field-<?php echo $field_input_type; ?>"
                        <?php echo ($field_value == '1') ? 'checked' : ''; ?>
                    /> <?php else: ?>
                    <input
                        type="<?php echo esc_attr($field_input_type); ?>"
                        id="<?php echo $id; ?>_<?php echo $idx; ?>_<?php echo $field_key; ?>"
                        name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $idx; ?>][<?php echo $field_key; ?>]"
                        value="<?php echo wp_kses_post($field_value); ?>"
                        placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                        class="multi-input-field composite-field-input field-<?php echo $field_input_type; ?>"
                        <?php echo !empty($field['required']) ? 'required' : ''; ?>
                    /> <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <?php if (empty($multi_value)): ?>
        <?php for ($i = 0; $i < $min_items; $i++): ?>
        <div class="multi-input-item" data-index="<?php echo $i; ?>">
            <label
                class="multi-input-label"><?php echo esc_html($item_label); ?>
                <?php echo $i + 1; ?></label>
            <?php if ($field_type === 'textarea'): ?>
            <textarea
                name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $i; ?>]"
                class="multi-input-field" rows="3"></textarea>
            <?php else: ?>
            <input type="<?php echo esc_attr($field_type); ?>"
                name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $i; ?>]"
                class="multi-input-field" value="" />
            <?php endif; ?>
            <button type="button" class="remove-item button"
                <?php echo ($i < $min_items) ? 'style="display:none;"' : ''; ?>>×</button>
        </div>
        <?php endfor; ?>
        <?php else: ?>
        <?php foreach ($multi_value as $idx => $item_value): ?>
        <div class="multi-input-item"
            data-index="<?php echo $idx; ?>">
            <label
                class="multi-input-label"><?php echo esc_html($item_label); ?>
                <?php echo $idx + 1; ?></label>
            <?php if ($field_type === 'textarea'): ?>
            <textarea
                name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $idx; ?>]"
                class="multi-input-field"
                rows="3"><?php echo wp_kses_post($item_value); ?></textarea>
            <?php else: ?>
            <input type="<?php echo esc_attr($field_type); ?>"
                name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $idx; ?>]"
                class="multi-input-field"
                value="<?php echo esc_attr($item_value); ?>" />
            <?php endif; ?>
            <button type="button" class="remove-item button"
                <?php echo ($idx < $min_items) ? 'style="display:none;"' : ''; ?>>×</button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="add-item button button-secondary">+ Add
        <?php echo esc_html($item_label); ?></button>
    <?php if ($is_composite): ?>
    <script type="text/template" id="multi-input-template-<?php echo $id; ?>"> <div class="multi-input-item composite-item" data-index="{INDEX}">
        <div class="composite-item-header">
            <label class="multi-input-label"><?php echo esc_html($item_label); ?> {LABEL_INDEX}</label>
            <button type="button" class="remove-item button">×</button>
        </div>
        <div class="composite-item-fields">
            <?php foreach ($composite_fields as $field): ?>
            <div class="composite-field-row">
                <label for="<?php echo $id; ?>_{INDEX}_<?php echo $field['name']; ?>">
                    <?php echo esc_html($field['label']); ?>
                    <?php if (!empty($field['required'])): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
                <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                <textarea 
                    id="<?php echo $id; ?>_{INDEX}_<?php echo $field['name']; ?>"
                    name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][{INDEX}][<?php echo $field['name']; ?>]"
                    placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                    rows="<?php echo esc_attr($field['rows'] ?? 3); ?>"
                    class="multi-input-field composite-field-input"
                    <?php echo !empty($field['required']) ? 'required' : ''; ?>></textarea>
                <?php elseif (($field['type'] ?? 'text') === 'select'): ?>
                <select
                    id="<?php echo $id; ?>_{INDEX}_<?php echo $field['name']; ?>"
                    name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][{INDEX}][<?php echo $field['name']; ?>]"
                    class="multi-input-field composite-field-input"
                    <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                    <option value="">
                        <?php echo esc_html($field['placeholder'] ?? ''); ?>
                    </option>
                    <?php foreach ($field['options'] as $option): ?>
                    <option
                        value="<?php echo esc_attr($option['value']); ?>"><?php echo esc_html($option['label']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <input 
                    type="<?php echo esc_attr($field['type'] ?? 'text'); ?>"
                    id="<?php echo $id; ?>_{INDEX}_<?php echo $field['name']; ?>"
                    name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][{INDEX}][<?php echo $field['name']; ?>]"
                    placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                    class="multi-input-field composite-field-input"
                    <?php echo !empty($field['required']) ? 'required' : ''; ?> />
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</script>
    <?php else: ?>
    <script type="text/template" id="multi-input-template-<?php echo $id; ?>"> <div class="multi-input-item" data-index="{INDEX}">
            <label class="multi-input-label"><?php echo esc_html($item_label); ?> {LABEL_INDEX}</label>
            <?php if ($field_type === 'textarea'): ?>
                <textarea name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][{INDEX}]" 
                          class="multi-input-field" 
                          rows="3"></textarea>
            <?php else: ?>
                <input type="<?php echo esc_attr($field_type); ?>" 
                       name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][{INDEX}]" 
                       class="multi-input-field" 
                       value="" />
            <?php endif; ?>
            <button type="button" class="remove-item button">×</button>
        </div>
    </script>
    <?php endif; ?>
</div>
