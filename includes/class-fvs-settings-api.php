<?php
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
 
	if ( ! class_exists( 'FVS_Settings_API' ) ):
		
		class FVS_Settings_API {
			
			private $setting_name = 'flatsome_variation_swatches';
			private $slug;
			private $plugin_class;
			
			private $fields = array();
			
			public function __construct() {
				
				$this->plugin_class = flatsome_variation_swatches();
				
				$this->settings_name = apply_filters( 'fvs_settings_name', $this->setting_name );
				
				// $this->slug = sprintf( '%s-settings', sanitize_key( $this->plugin_class->get_theme_name() ) );
				$this->slug = sprintf( '%s-settings', sanitize_key( $this->plugin_class->basename() ) );
				
				$this->fields = apply_filters( 'fvs_settings', $this->fields );
				
				add_action( 'admin_menu', array( $this, 'add_menu' ) );
				
				add_action( 'admin_init', array( $this, 'settings_init' ), 90 );
				
				add_filter( 'plugin_action_links_' . $this->plugin_class->basename(), array( $this, 'plugin_action_links' ) );
				
				add_action( 'wp_before_admin_bar_render', array( $this, 'add_admin_bar' ), 999 );
				
				add_action( 'admin_footer', array( $this, 'admin_inline_js' ) );
				
				do_action( 'fvs_setting_api_init', $this );
			}
			
			public function admin_inline_js() {
				?>
                <script type="text/javascript">
                    jQuery(function ($) {
                        $('#<?php echo $this->slug ?>-wrap').on('click', '.nav-tab', function (event) {
                            event.preventDefault();
                            var target = $(this).data('target');
                            $(this).addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
                            $('#' + target).show().siblings().hide();
                        });
                    });
                </script>
				<?php
				
			}
			
			public function add_menu() {
				
				if ( empty( $this->fields ) ) {
					return '';
				}
				
				$page_title = esc_html__( 'Flatsome Variation Swatches Settings', 'flatsome-variation-swatches' );
				$menu_title = esc_html__( 'Swatches Settings', 'flatsome-variation-swatches' );
				add_menu_page( $page_title, $menu_title, 'edit_theme_options', $this->slug, array( $this, 'settings_form' ), 'dashicons-admin-generic', 31 );
			}
			
			public function add_admin_bar() {
				
				if ( empty( $this->fields ) ) {
					return '';
				}
				
				global $wp_admin_bar;
				
				// $slug  = sprintf( '%s-settings', sanitize_key( Hippo_Theme_Plugin()->get_theme_name() ) );
				$url        = admin_url( sprintf( 'admin.php?page=%s', $this->slug ) );
				$menu_title = esc_html__( 'Swatches Settings', 'flatsome-variation-swatches' );
				
				$args = array(
					'id'    => $this->settings_name,
					'title' => $menu_title,
					'href'  => $url,
					'meta'  => array(
						'class' => sprintf( '%s-admin-toolbar', $this->slug )
					)
				);
				$wp_admin_bar->add_menu( $args );
			}
			
			public function plugin_action_links( $links ) {
				
				if ( empty( $this->fields ) ) {
					return $links;
				}
				
				$url          = admin_url( sprintf( 'admin.php?page=%s', $this->slug ) );
				$plugin_links = array( sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Settings', 'hippo-theme-plugin' ) ) );
				
				return array_merge( $plugin_links, $links );
			}
			
			public function get_option( $option, $default = FALSE ) {
				$options = get_option( $this->settings_name, $default );
				if ( isset( $options[ $option ] ) ) {
					return apply_filters( "fvs_settings_get_option", $options[ $option ], $option, $options, $default );
				} else {
					return apply_filters( "fvs_settings_get_option", $default, $option, $options, $default );
				}
			}
			
			public function update_option( $key, $value ) {
				$options         = get_option( $this->settings_name );
				$options[ $key ] = $value;
				
				update_option( $this->settings_name, $options );
			}
			
			public function settings_init() {
				
				register_setting( $this->settings_name, $this->settings_name );
				
				
				foreach ( $this->fields as $tabs ) {
					
					$tabs = apply_filters( 'fvs_settings_sections', $tabs );
					
					foreach ( $tabs[ 'sections' ] as $section ) {
						
						add_settings_section( $tabs[ 'id' ] . $section[ 'id' ], $section[ 'title' ], function () use ( $section ) {
							if ( isset( $section[ 'desc' ] ) && ! empty( $section[ 'desc' ] ) ) {
								echo '<div class="inside">' . $section[ 'desc' ] . '</div>';
							}
						}, $tabs[ 'id' ] . $section[ 'id' ] );
						
						$section = apply_filters( 'fvs_settings_fields', $section );
						
						foreach ( $section[ 'fields' ] as $field ) {
							
							//$field[ 'label_for' ] = $this->settings_name . '[' . $field[ 'id' ] . ']';
							$field[ 'label_for' ] = $field[ 'id' ] . '-field';
							$field[ 'default' ]   = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
							
							if ( $field[ 'type' ] == 'checkbox' ) {
								unset( $field[ 'label_for' ] );
							}
							
							add_settings_field( $this->settings_name . '[' . $field[ 'id' ] . ']', $field[ 'title' ], array( $this, 'field_callback' ), $tabs[ 'id' ] . $section[ 'id' ], $tabs[ 'id' ] . $section[ 'id' ], $field );
						}
					}
				}
			}
			
			public function field_callback( $field ) {
				
				switch ( $field[ 'type' ] ) {
					case 'checkbox':
						$this->checkbox_field_callback( $field );
						break;
					
					case 'select':
						$this->select_field_callback( $field );
						break;
					
					case 'number':
						$this->number_field_callback( $field );
						break;
					
					case 'post_select':
						$this->post_select_field_callback( $field );
						break;
					
					default:
						$this->text_field_callback( $field );
						break;
				}
				
				do_action( 'hippo_theme_plugin_settings_field_callback', $field );
			}
			
			public function checkbox_field_callback( $args ) {
				$current = esc_attr( $this->get_option( $args[ 'id' ] ) );
				$value   = esc_attr( $args[ 'value' ] );
				$size    = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';
				$html    = sprintf( '<label><input type="checkbox" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s/> %6$s</label>', $size, $args[ 'id' ], $value, $this->settings_name, checked( $current, $value, FALSE ), esc_attr( $args[ 'desc' ] ) );
				
				echo $html;
			}
			
			public function select_field_callback( $args ) {
				
				$options = apply_filters( "hippo_theme_plugin_settings_{$args[ 'id' ]}_select_options", $args[ 'options' ] );
				
				$value = esc_attr( $this->get_option( $args[ 'id' ], $args[ 'default' ] ) );
				
				$options = array_map( function ( $key, $option ) use ( $value ) {
					return "<option value='{$key}'" . selected( $key, $value, FALSE ) . ">{$option}</option>";
				}, array_keys( $options ), $options );
				
				$size = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';
				$html = sprintf( '<select class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]">%3$s</select>', $size, $args[ 'id' ], implode( '', $options ), $this->settings_name );
				$html .= $this->get_field_description( $args );
				echo $html;
			}
			
			public function get_field_description( $args ) {
				if ( ! empty( $args[ 'desc' ] ) ) {
					$desc = sprintf( '<p class="description">%s</p>', $args[ 'desc' ] );
				} else {
					$desc = '';
				}
				
				return $desc;
			}
			
			public function post_select_field_callback( $args ) {
				
				$options = apply_filters( "hippo_ticket_settings_{$args[ 'id' ]}_post_select_options", $args[ 'options' ] );
				
				$value = esc_attr( $this->get_option( $args[ 'id' ], $args[ 'default' ] ) );
				
				$options = array_map( function ( $option ) use ( $value ) {
					return "<option value='{$option->ID}'" . selected( $option->ID, $value, FALSE ) . ">$option->post_title</option>";
				}, $options );
				
				$size = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';
				$html = sprintf( '<select class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]">%3$s</select>', $size, $args[ 'id' ], implode( '', $options ), $this->settings_name );
				$html .= $this->get_field_description( $args );
				echo $html;
			}
			
			public function text_field_callback( $args ) {
				$value = esc_attr( $this->get_option( $args[ 'id' ], $args[ 'default' ] ) );
				$size  = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'regular';
				$html  = sprintf( '<input type="text" class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]" value="%3$s"/>', $size, $args[ 'id' ], $value, $this->settings_name );
				$html  .= $this->get_field_description( $args );
				echo $html;
			}
			
			public function number_field_callback( $args ) {
				$value = esc_attr( $this->get_option( $args[ 'id' ], $args[ 'default' ] ) );
				$size  = isset( $args[ 'size' ] ) && ! is_null( $args[ 'size' ] ) ? $args[ 'size' ] : 'small';
				
				$min    = isset( $args[ 'min' ] ) && ! is_null( $args[ 'min' ] ) ? 'min="' . $args[ 'min' ] . '"' : '';
				$max    = isset( $args[ 'max' ] ) && ! is_null( $args[ 'max' ] ) ? 'max="' . $args[ 'max' ] . '"' : '';
				$step   = isset( $args[ 'step' ] ) && ! is_null( $args[ 'step' ] ) ? 'step="' . $args[ 'step' ] . '"' : '';
				$suffix = isset( $args[ 'suffix' ] ) && ! is_null( $args[ 'suffix' ] ) ? ' <span>' . $args[ 'suffix' ] . '</span>' : '';
				
				$html = sprintf( '<input type="number" class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s %6$s %7$s /> %8$s', $size, $args[ 'id' ], $value, $this->settings_name, $min, $max, $step, $suffix );
				$html .= $this->get_field_description( $args );
				echo $html;
			}
			
			public function settings_form() {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
				}
				?>
                <div id="<?php echo $this->slug ?>-wrap" class="wrap settings-wrap">

                    <h1><?php echo get_admin_page_title() ?></h1>

                    <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ) ?>" enctype="multipart/form-data">
						<?php
							settings_errors();
							settings_fields( $this->settings_name );
						?>
						
						<?php $this->options_tabs(); ?>

                        <div id="settings-tabs">
							<?php foreach ( $this->fields as $tab ): ?>

                                <div id="<?php echo $tab[ 'id' ] ?>"
                                     class="settings-tab fvs-setting-tab"
                                     style="<?php echo( ! isset( $tab[ 'active' ] ) ? 'display: none' : '' ) ?>">
									<?php foreach ( $tab[ 'sections' ] as $section ):
										$this->do_settings_sections( $tab[ 'id' ] . $section[ 'id' ] );
									endforeach; ?>
                                </div>
							
							<?php endforeach; ?>
                        </div>
						<?php
							submit_button();
						?>
                    </form>
                </div>
				<?php
			}
			
			public function options_tabs() {
				?>
                <h2 class="nav-tab-wrapper wp-clearfix">
					<?php foreach ( $this->fields as $tabs ): ?>
                        <a data-target="<?php echo $tabs[ 'id' ] ?>"
                           class="fvs-setting-nav-tab nav-tab <?php echo ( isset( $tabs[ 'active' ] ) and $tabs[ 'active' ] ) ? 'nav-tab-active' : '' ?> "
                           href="#<?php echo $tabs[ 'id' ] ?>"><?php echo $tabs[ 'title' ] ?></a>
					<?php endforeach; ?>
                </h2>
				<?php
			}
			
			private function do_settings_sections( $page ) {
				global $wp_settings_sections, $wp_settings_fields;
				
				if ( ! isset( $wp_settings_sections[ $page ] ) ) {
					return;
				}
				
				foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
					if ( $section[ 'title' ] ) {
						echo "<h2>{$section['title']}</h2>\n";
					}
					
					if ( $section[ 'callback' ] ) {
						call_user_func( $section[ 'callback' ], $section );
					}
					
					if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section[ 'id' ] ] ) ) {
						continue;
					}
					
					echo '<table class="form-table">';
					$this->do_settings_fields( $page, $section[ 'id' ] );
					echo '</table>';
				}
			}
			
			public function array2html_attr( $attributes, $do_not_add = array() ) {
				
				$attributes = wp_parse_args( $attributes, array() );
				
				if ( ! empty( $do_not_add ) and is_array( $do_not_add ) ) {
					foreach ( $do_not_add as $att_name ) {
						unset( $attributes[ $att_name ] );
					}
				}
				
				
				$attributes_array = array();
				
				foreach ( $attributes as $key => $value ) {
					
					if ( is_bool( $attributes[ $key ] ) and $attributes[ $key ] === TRUE ) {
						return $attributes[ $key ] ? $key : '';
					} elseif ( is_bool( $attributes[ $key ] ) and $attributes[ $key ] === FALSE ) {
						$attributes_array[] = '';
					} else {
						$attributes_array[] = $key . '="' . $value . '"';
					}
				}
				
				return implode( ' ', $attributes_array );
			}
			
			private function build_dependency( $require_array ) {
				$b_array = array();
				foreach ( $require_array as $k => $v ) {
					$b_array[ '#' . $k . '-field' ] = $v;
				}
				
				return 'data-depends="[' . esc_attr( wp_json_encode( $b_array ) ) . ']"';
			}
			
			private function do_settings_fields( $page, $section ) {
				global $wp_settings_fields;
				
				if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
					return;
				}
				
				foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
					/*$class = '';

					if ( ! empty( $field[ 'args' ][ 'class' ] ) ) {
						$class = ' class="' . esc_attr( $field[ 'args' ][ 'class' ] ) . '"';
					}*/
					
					$custom_attributes = $this->array2html_attr( isset( $field[ 'args' ][ 'attributes' ] ) ? $field[ 'args' ][ 'attributes' ] : array() );
					
					$wrapper_id = ! empty( $field[ 'args' ][ 'id' ] ) ? esc_attr( $field[ 'args' ][ 'id' ] ) . '-wrapper' : '';
					$dependency = ! empty( $field[ 'args' ][ 'require' ] ) ? $this->build_dependency( $field[ 'args' ][ 'require' ] ) : '';
					
					printf( '<tr id="%s" %s %s>', $wrapper_id, $custom_attributes, $dependency );
					
					if ( ! empty( $field[ 'args' ][ 'label_for' ] ) ) {
						echo '<th scope="row"><label for="' . esc_attr( $field[ 'args' ][ 'label_for' ] ) . '">' . $field[ 'title' ] . '</label></th>';
					} else {
						echo '<th scope="row">' . $field[ 'title' ] . '</th>';
					}
					
					echo '<td>';
					call_user_func( $field[ 'callback' ], $field[ 'args' ] );
					echo '</td>';
					echo '</tr>';
				}
			}
		}
		
		function fvs_settings_api() {
			return new FVS_Settings_API();
		}
		
		add_action( 'plugins_loaded', 'fvs_settings_api' );
	
	endif;
