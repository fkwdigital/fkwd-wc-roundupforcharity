<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
            <div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-input-field"
                id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>_<?php echo $id; ?>">
                <input type="<?php echo $type; ?>" id="<?php echo $id; ?>_id"
                    name="<?php echo $page_database_id; ?>[<?php echo $id; ?>]" value="<?php echo $value; ?>" />
            </div>
