            <div class="<?php echo FKWD_PLUGIN_YSS_NAMESPACE; ?>-field <?php echo FKWD_PLUGIN_YSS_NAMESPACE; ?>-radio-field">
				<div class="field field-<?php echo $name; ?>">
                    <?php if( $value === 0 ) : ?>
                    <input type="hidden" name="<?php echo $this->page_database_id; ?>[<?php echo $fields[ 'id' ]; ?>][<?php echo $name; ?>]" value="0" />
                    <?php endif; ?>
                    <input type="radio"
                        id="<?php echo $fields[ 'id' ]; ?>_<?php echo $name; ?>_id"
                        name="<?php echo $this->page_database_id; ?>[<?php echo $fields[ 'id' ]; ?>][<?php echo $name; ?>]"
                        value="1"
                        <?php if( $value == 1 ) { ?>checked<?php } ?>
                    /> <label for="<?php echo $this->page_database_id; ?>[<?php echo $fields[ 'id' ]; ?>][<?php echo $name; ?>]"><?php echo esc_html_e( $subfield_label ); ?></label>
				</div>
			</div>
