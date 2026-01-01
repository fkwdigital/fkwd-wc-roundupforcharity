<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
            <div class="<?php echo $this->clean_string(FKWD_PLUGIN_WCRFC_NAMESPACE . '-field', ['type' =>'attribute']) . ' ' . $this->clean_string(FKWD_PLUGIN_WCRFC_NAMESPACE . '-input-field', ['type' =>'attribute']); ?>"
                id="<?php echo $this->clean_string(FKWD_PLUGIN_WCRFC_NAMESPACE . '_' . $id, ['type' =>'attribute']); ?>">
                <input type="<?php echo $this->clean_string($type); ?>" id="<?php echo $id; ?>_id"
                    name="<?php echo $this->clean_string($page_database_id, ['type' => 'attribute'] .'[' . $id . ']"', ['type' => 'attribute']); ?>" value="<?php echo $this->clean_string($value); ?>" />
            </div>
