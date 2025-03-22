            <div class="<?php echo FKWD_PLUGIN_YSS_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_YSS_NAMESPACE; ?>-checkbox-field" id="<?php echo FKWD_PLUGIN_YSS_NAMESPACE; ?>_<?php echo $id; ?>">
                <?php if( $value === 0 ) : ?>
                <input type="hidden" name="<?php echo $this->page_database_id; ?>[<?php echo $id; ?>]" value="0" />
                <?php endif; ?>
                <input type="checkbox"
                    id="<?php echo $id; ?>_id"
                    name="<?php echo $this->page_database_id; ?>[<?php echo $id; ?>]"
                    value="1"
                    <?php if( $value == 1 ) { ?>checked<?php } ?>
                /> <label for="<?php echo $this->page_database_id; ?>[<?php echo $id; ?>]"><?php echo esc_html_e( $label ); ?></label>
			</div>
