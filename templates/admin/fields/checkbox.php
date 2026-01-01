<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
            <div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-checkbox-field"
                id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>_<?php echo $id; ?>">
                <?php if( !empty( $options ) && is_array( $options ) ) : ?>
                <?php foreach( $options as $option_key => $label ) : ?>
                <p><input type="checkbox" id="<?php echo $id; ?>_<?php echo $option_key; ?>"
                    name="<?php echo $page_database_id; ?>[<?php echo $id; ?>][<?php echo $option_key; ?>]" value="1"
                    <?php if ( isset( $value[ $option_key ] ) && $value[ $option_key ] == 1 ) { ?>checked<?php } ?> />
                <label for="<?php echo $id; ?>_<?php echo $option_key; ?>_id"><?php echo esc_html( $label ); ?></label></p>
                <?php endforeach; ?>
                <?php else: ?>
                <?php if( $value === 0 ) : ?>
                <input type="hidden" name="<?php echo $page_database_id; ?>[<?php echo $id; ?>]" value="0" />
                <?php endif; ?>
                <input type="checkbox" id="<?php echo $id; ?>"
                    name="<?php echo $page_database_id; ?>[<?php echo $id; ?>]" value="1"
                    <?php if( $value == 1 ) { ?>checked<?php } ?> />
                <?php endif; ?>
            </div>
