            <?php if( !empty( $data_class ) && is_object( $data_class ) ) {
                $field_name = FKWD_PLUGIN_WCRFC_NAMESPACE . '_report_month'; ?>
            <div class="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>-select-field"
                id="<?php echo FKWD_PLUGIN_WCRFC_NAMESPACE; ?>_<?php echo $id; ?>">
                <select name="<?php echo $this->page_database_id; ?>[<?php echo $id; ?>]" id="<?php echo $id; ?>_id">
                    <?php if( !empty( $available_options ) && is_array( $available_options ) ) { 
                        echo $available_options['options'] ?? ''; 
                    } ?>
                </select>
            </div>
            <?php } ?>
