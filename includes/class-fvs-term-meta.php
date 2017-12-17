<?php
	
	defined( 'ABSPATH' ) or die( 'Keep Silent' );
	
	if ( ! class_exists( 'FVS_Term_Meta' ) ):
		
		class FVS_Term_Meta {
			
			private $taxonomy;
			private $post_type;
			private $fields = array();
			
			public function __construct( $taxonomy, $post_type, $fields = array() ) {
				
				$this->taxonomy  = $taxonomy;
				$this->post_type = $post_type;
				$this->fields    = $fields;
				
				// Category/term ordering
				//add_action( 'create_term', array( $this, 'create_term' ), 5, 3 );
				
				add_action( 'delete_term', array( $this, 'delete_term' ), 5, 4 );
				
				// Add form
				add_action( "{$this->taxonomy}_add_form_fields", array( $this, 'add' ) );
				add_action( "{$this->taxonomy}_edit_form_fields", array( $this, 'edit' ), 10 );
				add_action( "created_term", array( $this, 'save' ), 10, 3 );
				add_action( "edit_term", array( $this, 'save' ), 10, 3 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				
				// Add columns
				//add_filter( 'manage_edit-product_cat_columns', array( $this, 'product_cat_columns' ) );
				//add_filter( 'manage_product_cat_custom_column', array( $this, 'product_cat_column' ), 10, 3 );
				
			}
			
			public function delete_term( $term_id, $tt_id, $taxonomy, $deleted_term ) {
				global $wpdb;
				
				$term_id = absint( $term_id );
				if ( $term_id and $taxonomy == $this->taxonomy ) {
					$wpdb->delete( $wpdb->termmeta, array( 'term_id' => $term_id ), array( '%d' ) );
				}
			}
			
			public function enqueue_scripts() {
				//add_thickbox();
				wp_enqueue_media();
				
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				
				//wp_enqueue_script('jquery-ui-core');
				//wp_enqueue_script('jquery-ui-widget');
				//wp_enqueue_script('jquery-ui-mouse');
				// wp_enqueue_script( 'jquery-ui-sortable' );
				
			}
			
			public function save( $term_id, $tt_id = '', $taxonomy = '' ) {
				
				if ( $taxonomy == $this->taxonomy ) {
					foreach ( $this->fields as $field ) {
						foreach ( $_POST as $post_key => $post_value ) {
							if ( $field[ 'id' ] == $post_key ) {
								switch ( $field[ 'type' ] ) {
									case 'text':
									case 'color':
										$post_value = esc_html( $post_value );
										break;
									case 'url':
										$post_value = esc_url( $post_value );
										break;
									case 'image':
										$post_value = absint( $post_value );
										break;
									case 'textarea':
										$post_value = esc_textarea( $post_value );
										break;
									case 'editor':
										$post_value = wp_kses_post( $post_value );
										break;
									case 'select':
									case 'select2':
										$post_value = sanitize_key( $post_value );
										break;
									default:
										do_action( 'fvs_save_term_meta', $term_id, $field, $post_value, $taxonomy );
										break;
								}
								update_term_meta( $term_id, $field[ 'id' ], $post_value );
							}
						}
					}
					do_action( 'fvs_after_term_meta_saved', $term_id, $taxonomy );
				}
			}
			
			public function add() {
				$this->generate_fields();
			}
			
			private function generate_fields( $term = FALSE ) {
				
				$screen = get_current_screen();
				
				if ( ( $screen->post_type == $this->post_type ) and ( $screen->taxonomy == $this->taxonomy ) ) {
					$this->generate_form_fields( $term );
				}
			}
			
			private function generate_form_fields( $term ) {
				
				$fields = apply_filters( 'fvs_term_meta_fields', $this->fields, $term );
				
				foreach ( $fields as $field ) {
					
					$field = apply_filters( 'fvs_term_meta_field', $field, $term );
					
					$field[ 'id' ] = esc_html( $field[ 'id' ] );
					
					if ( ! $term ) {
						$field[ 'value' ] = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
					} else {
						$field[ 'value' ] = get_term_meta( $term->term_id, $field[ 'id' ], TRUE );
					}
					
					$field[ 'size' ]        = isset( $field[ 'size' ] ) ? $field[ 'size' ] : '40';
					$field[ 'required' ]    = ( isset( $field[ 'required' ] ) and $field[ 'required' ] == TRUE ) ? ' aria-required="true"' : '';
					$field[ 'placeholder' ] = ( isset( $field[ 'placeholder' ] ) ) ? ' placeholder="' . $field[ 'placeholder' ] . '" data-placeholder="' . $field[ 'placeholder' ] . '"' : '';
					$field[ 'desc' ]        = ( isset( $field[ 'desc' ] ) ) ? $field[ 'desc' ] : '';
					
					$field[ 'dependency' ]       = ( isset( $field[ 'dependency' ] ) ) ? $field[ 'dependency' ] : array();
					
					$this->field_start( $field, $term );
					switch ( $field[ 'type' ] ) {
						case 'text':
						case 'url':
							ob_start();
							?>
                            <input name="<?php echo $field[ 'id' ] ?>" id="<?php echo $field[ 'id' ] ?>"
                                   type="<?php echo $field[ 'type' ] ?>"
                                   value="<?php echo $field[ 'value' ] ?>"
                                   size="<?php echo $field[ 'size' ] ?>" <?php echo $field[ 'required' ] . $field[ 'placeholder' ] ?>>
							<?php
							echo ob_get_clean();
							break;
						case 'color':
							ob_start();
							?>
                            <input name="<?php echo $field[ 'id' ] ?>" id="<?php echo $field[ 'id' ] ?>" type="text" class="fvs-color-picker" value="<?php echo $field[ 'value' ] ?>" size="<?php echo $field[ 'size' ] ?>" <?php echo $field[ 'required' ] . $field[ 'placeholder' ] ?>>
							<?php
							echo ob_get_clean();
							break;
						case 'textarea':
							ob_start();
							?>
                            <textarea name="<?php echo $field[ 'id' ] ?>" id="<?php echo $field[ 'id' ] ?>" rows="5" cols="<?php echo $field[ 'size' ] ?>" <?php echo $field[ 'required' ] . $field[ 'placeholder' ] ?>><?php echo $field[ 'value' ] ?></textarea>
							<?php
							echo ob_get_clean();
							break;
						case 'editor':
							$field[ 'settings' ] = isset( $field[ 'settings' ] )
								? $field[ 'settings' ]
								: array(
									'textarea_rows' => 8,
									'quicktags'     => FALSE,
									'media_buttons' => FALSE
								);
							ob_start();
							wp_editor( $field[ 'value' ], $field[ 'id' ], $field[ 'settings' ] );
							echo ob_get_clean();
							break;
						case 'select':
						case 'select2':
							
							$field[ 'options' ] = isset( $field[ 'options' ] ) ? $field[ 'options' ] : array();
							$field[ 'multiple' ] = isset( $field[ 'multiple' ] ) ? ' multiple="multiple"' : '';
							$css_class           = ( $field[ 'type' ] == 'select2' ) ? 'fvs-selectwoo' : '';
							
							ob_start();
							?>
                            <select name="<?php echo $field[ 'id' ] ?>" id="<?php echo $field[ 'id' ] ?>" class="<?php echo $css_class ?>" <?php echo $field[ 'multiple' ] ?>>
								<?php
									foreach ( $field[ 'options' ] as $key => $option ) {
										echo '<option' . selected( $field[ 'value' ], $key, FALSE ) . ' value="' . $key . '">' . $option . '</option>';
									}
								?>
                            </select>
							<?php
							echo ob_get_clean();
							break;
						
						case 'ajax-select2':
							
							$field[ 'options' ] = isset( $field[ 'options' ] ) ? $field[ 'options' ] : array();
							$field[ 'multiple' ] = isset( $field[ 'multiple' ] ) ? ' multiple="multiple"' : '';
							$field[ 'action' ]   = isset( $field[ 'action' ] ) ? ' data-ajax-action="' . esc_attr( $field[ 'action' ] ) . '"' : 'data-ajax-action=""';
							$options             = apply_filters( 'hippo_theme_plugin_ajax_select2_term_meta_options', array(), $field[ 'value' ], $field );
							
							ob_start();
							?>
                            <select <?php echo $field[ 'action' ] ?> <?php echo $field[ 'placeholder' ] ?> name="<?php echo $field[ 'id' ] ?>" id="<?php echo $field[ 'id' ] ?>" class="ajax-fvs-selectwoo" <?php echo $field[ 'multiple' ] ?>>
								<?php
									foreach ( $options as $value => $text ) {
										printf( '<option selected="selected" value="%s">%s</option>', esc_attr( $value ), esc_html( $text ) );
									}
								?>
                            </select>
							<?php
							echo ob_get_clean();
							break;
						
						case 'icon':
							$field[ 'options' ] = isset( $field[ 'options' ] ) ? $field[ 'options' ] : array();
							$field[ 'multiple' ] = isset( $field[ 'multiple' ] ) ? ' multiple="multiple"' : '';
							$css_class           = 'icon-fvs-selectwoo';
							ob_start();
							?>
                            <select name="<?php echo $field[ 'id' ] ?>" id="<?php echo $field[ 'id' ] ?>"
                                    class="<?php echo $css_class ?>" <?php echo $field[ 'multiple' ] ?>>
                                <option value=""><?php __( '-- Choose an Icon --', 'hippo-theme-plugin' ) ?></option>
								<?php
									foreach ( $field[ 'options' ] as $key => $option ) {
										echo '<option' . selected( $field[ 'value' ], $key, FALSE ) . ' value="' . $key . '">' . $option . '</option>';
									}
								?>
                            </select>
							<?php
							echo ob_get_clean();
							break;
						
						case 'image':
							ob_start();
							?>
                            <div class="meta-image-field-wrapper">
                                <div class="image-preview">
                                    <img data-placeholder="<?php echo esc_url( $this->placeholder_img_src() ); ?>" src="<?php echo esc_url( $this->get_img_src( $field[ 'value' ] ) ); ?>" width="60px" height="60px"/>
                                </div>
                                <div class="button-wrapper">
                                    <input type="hidden" id="<?php echo $field[ 'id' ] ?>" name="<?php echo $field[ 'id' ] ?>" value="<?php echo esc_attr( $field[ 'value' ] ) ?>"/>
                                    <button type="button" class="fvs_upload_image_button button button-primary button-small"><?php esc_html_e( 'Upload / Add image', 'hippo-theme-plugin' ); ?></button>
                                    <button type="button" style="<?php echo( empty( $field[ 'value' ] ) ? 'display:none' : '' ) ?>" class="fvs_remove_image_button button button-danger button-small"><?php esc_html_e( 'Remove image', 'hippo-theme-plugin' ); ?></button>
                                </div>
                            </div>
							<?php
							echo ob_get_clean();
							break;
						
						default:
							do_action( 'fvs_term_meta_field', $field, $term );
							break;
						
					}
					$this->field_end( $field, $term );
					
				}
			}
			
			private function field_start( $field, $term ) {
				
				$depends = empty( $field[ 'dependency' ] ) ? '' : "data-depends='" . wp_json_encode( $field[ 'dependency' ] ) . "'";
				
				ob_start();
				if ( ! $term ) {
					?>
                    <div <?php echo $depends ?> class="form-field <?php echo esc_attr( $field[ 'id' ] ) ?> <?php echo empty( $field[ 'required' ] ) ? '' : 'form-required' ?>">
                    <label for="<?php echo esc_attr( $field[ 'id' ] ) ?>"><?php echo $field[ 'label' ] ?></label>
					<?php
				} else {
					?>
                    <tr <?php echo $depends ?> class="form-field  <?php echo esc_attr( $field[ 'id' ] ) ?> <?php echo empty( $field[ 'required' ] ) ? '' : 'form-required' ?>"">
                    <th scope="row"><label for="<?php echo esc_attr( $field[ 'id' ] ) ?>"><?php echo $field[ 'label' ] ?></label></th>
                    <td>
					<?php
				}
				echo ob_get_clean();
			}
			
			public function get_img_src( $thumbnail_id = FALSE ) {
				if ( ! empty( $thumbnail_id ) ) {
					$image = wp_get_attachment_thumb_url( $thumbnail_id );
				} else {
					$image = $this->placeholder_img_src();
				}
				
				return $image;
			}
			
			public function placeholder_img_src() {
				return flatsome_variation_swatches()->images_uri( 'placeholder.png' );
			}
			
			private function field_end( $field, $term ) {
				
				ob_start();
				if ( ! $term ) {
					?>
                    <p><?php echo $field[ 'desc' ] ?></p>
                    </div>
					<?php
				} else {
					?>
                    <p class="description"><?php echo $field[ 'desc' ] ?></p></td>
                    </tr>
					<?php
				}
				echo ob_get_clean();
			}
			
			public function edit( $term ) {
				$this->generate_fields( $term );
			}
		}
	
	endif;