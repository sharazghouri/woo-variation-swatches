<?php
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	$deactivate_reasons = array(
		'no_longer_needed'               => array(
			'title'             => __( 'I no longer need the plugin', 'woo-variation-swatches' ),
			'input_placeholder' => '',
		),
		'found_a_better_plugin'          => array(
			'title'             => __( 'I found a better plugin', 'woo-variation-swatches' ),
			'input_placeholder' => __( 'Please share which plugin', 'woo-variation-swatches' ),
		),
		'couldnt_get_the_plugin_to_work' => array(
			'title'             => __( 'I couldn\'t get the plugin to work', 'woo-variation-swatches' ),
			'input_placeholder' => '',
		),
		'temporary_deactivation'         => array(
			'title'             => __( 'It\'s a temporary deactivation', 'woo-variation-swatches' ),
			'input_placeholder' => '',
		),
		'other'                          => array(
			'title'             => __( 'Other', 'woo-variation-swatches' ),
			'input_placeholder' => __( 'Please share the reason', 'woo-variation-swatches' ),
		)
	);
	$plugin_name        = 'WooCommerce Variation Swatches';
?>
<div id="gwp-plugin-deactivate-feedback-dialog-wrapper" style="display: none">
    <form class="feedback-dialog-form" method="post" onsubmit="return false">
        <input type="hidden" name="action" value="gwp_deactivate_feedback"/>
        <input type="hidden" name="plugin" id="gwp-plugin-slug" value=""/>
        <div class="feedback-dialog-form-caption"><?php esc_html_e( sprintf( 'If you have a moment, please share why you are deactivating %s:', $plugin_name ), 'woo-variation-swatches' ); ?></div>
        <div class="feedback-dialog-form-body">
			<?php foreach ( $deactivate_reasons as $reason_key => $reason ) : ?>
                <div class="feedback-dialog-input-wrapper">
                    <input id="feedback-<?php echo esc_attr( $reason_key ); ?>" class="feedback-dialog-input" type="radio" name="reason_key" value="<?php echo esc_attr( $reason_key ); ?>"/>
                    <label for="feedback-<?php echo esc_attr( $reason_key ); ?>" class="feedback-dialog-label"><?php echo esc_html( $reason[ 'title' ] ); ?></label>
					<?php if ( ! empty( $reason[ 'input_placeholder' ] ) ) : ?>
                        <input class="feedback-text" style="display: none" disabled type="text" name="reason_<?php echo esc_attr( $reason_key ); ?>" placeholder="<?php echo esc_attr( $reason[ 'input_placeholder' ] ); ?>"/>
					<?php endif; ?>
                </div>
			<?php endforeach; ?>
        </div>
        <div class="feedback-dialog-form-buttons">
            <button class="button button-primary" id="feedback-dialog-form-button-send"><?php esc_html_e( 'Submit &amp; Deactivate', 'woo-variation-swatches' ) ?></button>
            <a href="#" id="feedback-dialog-form-button-skip"><?php esc_html_e( 'Skip &amp; Deactivate', 'woo-variation-swatches' ) ?></a>
        </div>
    </form>
</div>