<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

if (empty($options)) {
    return;
}

foreach ($options as $option_key => $option_label) : ?>
<option value="<?php echo esc_attr($option_key); ?>" <?php if ($selected == $option_key): ?>selected<?php endif; ?>>
    <?php echo esc_html($option_label); ?>
</option>
<?php endforeach; ?>
