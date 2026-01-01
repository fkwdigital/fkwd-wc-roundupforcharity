<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>            
            <div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-select-field"
                id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>_<?php echo $id; ?>">
                <select
                    name="<?php echo $page_database_id; ?>[<?php echo $id; ?>]"
                    id="<?php echo $id; ?>_id"
                    <?php disabled($disabled); ?>
                    <?php if (!empty($args['multiple'])) { ?>
                    multiple="multiple" <?php } ?>>
                    <?php include(FKWD_PLUGIN_WCRFC_DIR_PATH . 'templates/admin/fields/options.php'); ?>
                </select>
            </div>
