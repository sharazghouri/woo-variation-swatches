<?php
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	if ( ! function_exists( 'gwp_plugin_deactivate_feedback_dialog' ) ):
		function gwp_plugin_deactivate_feedback_dialog( $deactivate_reasons ) {
			?>
            <div id="gwp-plugin-deactivate-feedback-dialog-wrapper" style="display: none">
                <form class="feedback-dialog-form" method="post" onsubmit="return false">
                    <input type="hidden" name="action" value="gwp_deactivate_feedback"/>
                    <input type="hidden" name="plugin" id="gwp-plugin-slug" value=""/>
                    <div class="feedback-dialog-form-caption"><?php esc_html_e( 'May we have a little info about why you are deactivating?', 'woo-variation-swatches' ); ?></div>
                    <div class="feedback-dialog-form-body">
						<?php foreach ( $deactivate_reasons as $reason_key => $reason ) : ?>
                            <div class="feedback-dialog-input-wrapper">
                                <input id="feedback-<?php echo esc_attr( $reason_key ); ?>" class="feedback-dialog-input" type="radio" name="reason_type" value="<?php echo esc_attr( $reason_key ); ?>"/>
                                <label for="feedback-<?php echo esc_attr( $reason_key ); ?>" class="feedback-dialog-label"><?php echo $reason[ 'title' ]; ?></label>
								<?php if ( ! empty( $reason[ 'input_placeholder' ] ) ) : ?>
                                    <input class="feedback-text" style="display: none" disabled type="text" name="reason_text" placeholder="<?php echo esc_attr( $reason[ 'input_placeholder' ] ); ?>"/>
								<?php endif; ?>
								<?php if ( ! empty( $reason[ 'alert' ] ) ) : ?>
                                    <div class="feedback-text feedback-alert"><?php echo $reason[ 'alert' ]; ?></div>
								<?php endif; ?>
                            </div>
						<?php endforeach; ?>
                    </div>
                    <div class="feedback-dialog-form-buttons">
                        <button class="button button-primary" data-defaultvalue="<?php esc_html_e( 'Send &amp; Deactivate', 'woo-variation-swatches' ) ?>" data-deactivating="<?php esc_html_e( 'Deactivating...', 'woo-variation-swatches' ) ?>" id="feedback-dialog-form-button-send"><?php esc_html_e( 'Send &amp; Deactivate', 'woo-variation-swatches' ) ?></button>
                        <span class="spinner"></span>
                        <a href="#" id="feedback-dialog-form-button-skip"><?php esc_html_e( 'Skip &amp; Deactivate', 'woo-variation-swatches' ) ?></a>
                        <div class="clear"></div>
                    </div>
                </form>
            </div>
		<?php }
	endif; ?>