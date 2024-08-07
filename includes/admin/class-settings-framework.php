<?php
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'Ever_Settings_Framework' ) ) :

	/**
	 * Class Ever_Settings_Framework.
	 *
	 * @since 1.0.0
	 */
	class Ever_Settings_Framework {

		/**
		 * Settings sections array.
		 *
		 * @var array $settings_sections Settings sections array.
		 *
		 * @since 1.0.0
		 */
		protected $settings_sections = array();

		/**
		 * Settings fields array.
		 *
		 * @var array $settings_fields Settings fields array.
		 *
		 * @since 1.0.0
		 */
		protected $settings_fields = array();

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_media();
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery' );
		}

		/**
		 * Set settings sections.
		 *
		 * @param array $sections Sections.
		 *
		 * @since 1.0.0
		 * @return $this Single instance.
		 */
		public function set_sections( $sections ) {
			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * Add a single section.
		 *
		 * @param string $section Section.
		 *
		 * @since 1.0.0
		 * @return $this Single instance.
		 */
		public function add_section( $section ) {
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * Set settings fields.
		 *
		 * @param array $fields Fields.
		 *
		 * @since 1.0.0
		 * @return $this Single instance.
		 */
		public function set_fields( $fields ) {
			$this->settings_fields = $fields;

			return $this;
		}

		/**
		 * Set fields.
		 *
		 * @param string $section Section.
		 * @param string $field Field.
		 *
		 * @since 1.0.0
		 * @return $this Single instance.
		 */
		public function add_field( $section, $field ) {
			$defaults                            = array(
				'name'  => '',
				'label' => '',
				'desc'  => '',
				'type'  => 'text',
			);
			$arg                                 = wp_parse_args( $field, $defaults );
			$this->settings_fields[ $section ][] = $arg;

			return $this;
		}

		/**
		 * Initialize and registers the settings sections and field's to WordPress.
		 * Usually this should be called at `admin_init` hook.
		 * This function gets the initiated settings sections and fields. Then registers them to WordPress and ready for use.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function admin_init() {
			// Register settings sections.
			foreach ( $this->settings_sections as $section ) {
				if ( false === get_option( $section['id'] ) ) {
					add_option( $section['id'] );
				}
				if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
					$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';

					// phpcs:ignore $callback = create_function( '', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";' );
					$callback = function ( $section ) {
						echo wp_kses_post( str_replace( '"', '\"', $section['desc'] ) );
					};
				} elseif ( isset( $section['callback'] ) ) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}
				add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
			}
			// Register settings fields.
			foreach ( $this->settings_fields as $section => $field ) {
				foreach ( $field as $option ) {
					$name     = $option['name'];
					$type     = isset( $option['type'] ) ? $option['type'] : 'text';
					$label    = isset( $option['label'] ) ? $option['label'] : '';
					$callback = $option['callback'] ?? array(
						$this,
						'callback_' . $type,
					);
					$args     = array(
						'id'                => $name,
						'class'             => isset( $option['class'] ) ? $option['class'] : $name,
						'label_for'         => "{$section}[{$name}]",
						'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
						'name'              => $label,
						'section'           => $section,
						'size'              => isset( $option['size'] ) ? $option['size'] : null,
						'options'           => isset( $option['options'] ) ? $option['options'] : '',
						'std'               => isset( $option['default'] ) ? $option['default'] : '',
						'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
						'type'              => $type,
						'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
						'min'               => isset( $option['min'] ) ? $option['min'] : '',
						'max'               => isset( $option['max'] ) ? $option['max'] : '',
						'step'              => isset( $option['step'] ) ? $option['step'] : '',
					);
					if ( isset( $option['disabled'] ) && true === $option['disabled'] ) {
						$args['disabled'] = 'disabled';
					}
					add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
				}
			}
			// Creates our settings in the options table.
			foreach ( $this->settings_sections as $section ) {
				register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
			}
		}

		/**
		 * Get field description for display.
		 *
		 * @param array $args Array of arguments.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_field_description( $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) );
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * Displays a text field for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_text( $args ) {
			$value       = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'text';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$disabled    = isset( $args['disabled'] ) ? $args['disabled'] : '';
			printf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s/>', esc_attr( $type ), esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $value ), esc_attr( $placeholder ), esc_attr( $disabled ) );
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a url field for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_url( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays a number field for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_number( $args ) {
			$value       = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'number';
			$disabled    = isset( $args['disabled'] ) ? $args['disabled'] : '';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$min         = ( '' === $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
			$max         = ( '' === $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
			$step        = ( '' === $args['step'] ) ? '' : ' step="' . $args['step'] . '"';
			printf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s%10$s/>', esc_attr( $type ), esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $value ), esc_attr( $placeholder ), esc_attr( $min ), esc_attr( $max ), esc_attr( $step ), esc_attr( $disabled ) );
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a number field for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_heading( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			printf( '<h2 class="ever-settings-heading">%1$s</h2>', esc_attr( $value ) );
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a checkbox for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_checkbox( $args ) {
			$value    = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$disabled = isset( $args['disabled'] ) ? $args['disabled'] : '';
			echo '<fieldset>';
			printf( '<label for="wpuf-%1$s[%2$s]">', esc_attr( $args['section'] ), esc_attr( $args['id'] ) );
			printf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', esc_attr( $args['section'] ), esc_attr( $args['id'] ) );
			printf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s %4$s />', esc_attr( $args['section'] ), esc_attr( $args['id'] ), checked( $value, 'on', false ), esc_attr( $disabled ) );
			printf( '%1$s</label>', wp_kses_post( $args['desc'] ) );
			echo '</fieldset>';
		}

		/**
		 * Displays a multicheckbox for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_multicheck( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			echo '<fieldset>';
			printf( '<input type="hidden" name="%1$s[%2$s]" value="" />', esc_attr( $args['section'] ), esc_attr( $args['id'] ) );
			foreach ( $args['options'] as $key => $label ) {
				$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
				printf( '<label for="wpuf-%1$s[%2$s][%3$s]">', esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $key ) );
				printf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $key ), checked( $checked, $key, false ) );
				printf( '%1$s</label><br>', esc_html( $label ) );
			}
			echo '</fieldset>';
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a radio button for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_radio( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			echo '<fieldset>';
			foreach ( $args['options'] as $key => $label ) {
				printf( '<label for="wpuf-%1$s[%2$s][%3$s]">', esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $key ) );
				printf( '<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $key ), checked( $value, $key, false ) );
				printf( '%1$s</label><br>', esc_html( $label ) );
			}
			echo '</fieldset>';
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a selectbox for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_select( $args ) {
			$value    = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size     = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$disabled = isset( $args['disabled'] ) ? $args['disabled'] : '';
			printf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]" %4$s>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $disabled ) );
			foreach ( $args['options'] as $key => $label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), esc_attr( selected( $value, $key, false ) ), esc_html( $label ) );
			}
			echo '</select>';
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a textarea for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_textarea( $args ) {
			$value       = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			printf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_html( $placeholder ), esc_textarea( $value ) );
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays the html for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_html( $args ) {
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a rich text textarea for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_wysiwyg( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';
			echo '<div style="max-width: ' . esc_attr( $size ) . ';">';
			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10,
			);
			if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
				$editor_settings = array_merge( $editor_settings, $args['options'] );
			}
			wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );
			echo '</div>';
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a file upload field for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_file( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			// phpcs:ignore $id    = $args['section'] . '[' . $args['id'] . ']';
			$label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File', 'wp-content-pilot' );
			printf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $value ) );
			printf( '<input type="button" class="button wpsa-browse" value="%s" />', esc_html( $label ) );
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a password field for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_password( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			printf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $value ) );
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a color picker field for a settings field.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_color( $args ) {
			$value    = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size     = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$disabled = isset( $args['disabled'] ) ? $args['disabled'] : '';
			printf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" %6$s/>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), esc_attr( $value ), esc_attr( $args['std'] ), esc_attr( $disabled ) );
			echo wp_kses_post( $this->get_field_description( $args ) );
		}

		/**
		 * Displays a select box for creating the pages select box.
		 *
		 * @param array $args settings field args.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function callback_pages( $args ) {
			$dropdown_args = array(
				'selected' => esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) ),
				'name'     => $args['section'] . '[' . $args['id'] . ']',
				'id'       => $args['section'] . '[' . $args['id'] . ']',
				'echo'     => 0,
			);
			echo wp_kses_post( wp_dropdown_pages( $dropdown_args ) );
		}

		/**
		 * Sanitize callback for Settings API.
		 *
		 * @param mixed $options Options.
		 *
		 * @since 1.0.0
		 * @return mixed
		 */
		public function sanitize_options( $options ) {
			if ( ! $options ) {
				return $options;
			}
			foreach ( $options as $option_slug => $option_value ) {
				$sanitize_callback = $this->get_sanitize_callback( $option_slug );
				// If callback is set, call it.
				if ( $sanitize_callback ) {
					$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
				}
			}

			return $options;
		}

		/**
		 * Get sanitization callback for given option slug.
		 *
		 * @param string $slug option slug.
		 *
		 * @since 1.0.0
		 * @return mixed string or bool false.
		 */
		public function get_sanitize_callback( $slug = '' ) {
			if ( empty( $slug ) ) {
				return false;
			}
			// Iterate over registered fields and see if we can find proper callback.
			foreach ( $this->settings_fields as $section => $options ) {
				foreach ( $options as $option ) {
					if ( $option['name'] !== $slug ) {
						continue;
					}

					// Return the callback name.
					return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Get the value of a settings field.
		 *
		 * @param string $option settings field name.
		 * @param string $section the section name this field belongs to.
		 * @param string $default_value default text if it's not found.
		 *
		 * @return string
		 */
		public function get_option( $option, $section, $default_value = '' ) {
			$options = get_option( $section );
			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default_value;
		}

		/**
		 * Show navigations as tab.
		 * Shows all the settings section labels as tab.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function show_navigation() {
			$html  = '<div class="ever-settings-sidebar"><ul>';
			$count = count( $this->settings_sections );
			// Don't show the navigation if only one section exists.
			if ( 1 === $count ) {
				return;
			}
			foreach ( $this->settings_sections as $tab ) {
				$html .= sprintf( '<li><a href="#%1$s" id="%1$s-tab">%2$s</a></li>', $tab['id'], $tab['title'] );
			}
			$html .= '</ul></div>';
			echo wp_kses_post( $html );
		}

		/**
		 * Show the section settings forms.
		 * This function displays every section in a different form.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function show_forms() {
			$this->style_fix();
			?>
			<div class="ever-settings-content">
				<?php foreach ( $this->settings_sections as $form ) { ?>
					<div id="<?php echo esc_attr( $form['id'] ); ?>" class="group" style="display: none;">
						<form method="post" action="options.php">
							<?php
							do_action( 'wsa_form_top_' . $form['id'], $form );
							settings_fields( $form['id'] );
							do_settings_sections( $form['id'] );
							do_action( 'wsa_form_bottom_' . $form['id'], $form );
							if ( isset( $this->settings_fields[ $form['id'] ] ) ) :
								?>
								<div style="padding-left: 10px">
									<?php submit_button(); ?>
								</div>
							<?php endif; ?>
						</form>
					</div>
				<?php } ?>
			</div>
			<?php
			$this->script();
		}

		/**
		 * Display settings.
		 *
		 * @since 1.0.0
		 */
		public function show_settings() {
			echo '<div class="ever-settings d-flex">';
			$this->show_navigation();
			$this->show_forms();
			echo '</div>';
		}

		/**
		 * Tabbable JavaScript codes & Initiate Color Picker.
		 *
		 * This code uses localstorage for displaying active tabs.
		 *
		 * @since 1.0.0
		 */
		public function script() {
			?>
			<script>
				jQuery(document).ready(function ($) {
					// Initiate Color Picker.
					$('.wp-color-picker-field').wpColorPicker();
					// Switches option sections.
					$('.group').hide();
					var activetab = '';
					if (typeof (localStorage) != 'undefined') {
						activetab = localStorage.getItem("activetab");
					}
					// if url has section id as hash then set it as active or override the current local storage value.
					if (window.location.hash) {
						activetab = window.location.hash;
						if (typeof (localStorage) != 'undefined') {
							localStorage.setItem("activetab", activetab);
						}
					}
					if (activetab !== '' && $(activetab).length) {
						$(activetab).fadeIn();
					} else {
						$('.group:first').fadeIn();
					}
					$('.group .collapsed').each(function () {
						$(this).find('input:checked').parent().parent().parent().nextAll().each(
							function () {
								if ($(this).hasClass('last')) {
									$(this).removeClass('hidden');
									return false;
								}
								$(this).filter('.hidden').removeClass('hidden');
							});
					});

					if (activetab !== '' && $(activetab + '-tab').length) {
						$(activetab + '-tab').closest('li').addClass('active');
					} else {
						$('.ever-settings-sidebar  li:first').addClass('active');
					}

					$('.ever-settings-sidebar li a').click(function (evt) {
						$('.ever-settings-sidebar li').removeClass('active');
						$(this).closest('li').addClass('active').blur();

						var clicked_group = $(this).attr('href');
						if (typeof (localStorage) != 'undefined') {
							localStorage.setItem("activetab", $(this).attr('href'));
						}
						$('.group').hide();
						$(clicked_group).fadeIn();
						evt.preventDefault();
					});

					$('.wpsa-browse').on('click', function (event) {
						event.preventDefault();
						var self = $(this);
						// Create the media frame.
						var file_frame = wp.media.frames.file_frame = wp.media({
							title: self.data('uploader_title'),
							button: {
								text: self.data('uploader_button_text'),
							},
							multiple: false
						});
						file_frame.on('select', function () {
							attachment = file_frame.state().get('selection').first().toJSON();
							self.prev('.wpsa-url').val(attachment.url).change();
						});
						// Finally, open the modal.
						file_frame.open();
					});
				});
			</script>
			<?php
		}

		/**
		 * Style.
		 *
		 * @since 1.0.0
		 */
		public function style_fix() {
			global $wp_version;
			?>
			<style type="text/css">
				<?php if ( version_compare( $wp_version, '3.8', '<=' ) ) : ?>
				/** WordPress 3.8 Fix. **/
				.form-table th {
					padding: 20px 10px;
				}
				<?php endif; ?>
				.ever-settings *, .ever-settings *::before, .ever-settings *::after {
					box-sizing: border-box;
				}
				.ever-settings {
					margin: 16px 0;
				}
				.ever-settings.d-flex {
					display: -ms-flexbox !important;
					display: flex !important;
				}
				.ever-settings-sidebar {
					position: relative;
					z-index: 1;
					min-width: 185px;
					background-color: #eaeaea;
					border-bottom: 1px solid #cccccc;
					border-left: 1px solid #cccccc;
				}
				.ever-settings-sidebar > ul {
					margin: 0;
				}
				.ever-settings-sidebar > ul > li {
					margin: 0;
				}
				.ever-settings-sidebar > ul > li:first-child a {
					border-top-color: #cccccc;
				}
				.ever-settings-sidebar > ul > li a {
					display: block;
					padding: 0 20px;
					margin: 0 -1px 0 0;
					overflow: hidden;
					font-size: 13px;
					font-weight: 700;
					line-height: 3;
					color: #777;
					text-decoration: none;
					text-overflow: ellipsis;
					white-space: nowrap;
					border-top: 1px solid #f7f5f5;
					border-bottom: 1px solid #cccccc;
					width: 100%;
					border-right: 0;
					border-left: 0;
					box-shadow: none !important;
				}
				.ever-settings-sidebar > ul > li.active a {
					color: #23282d;
					background-color: #fff;
					border-right: 1px solid #fff !important;
				}
				.ever-settings-content {
					position: relative;
					width: 100%;
					padding: 10px 20px;
					background-color: #fff;
					border: 1px solid #cccccc;
					min-height: 500px;
				}
				.ever-settings-content h2 {
					padding: 0 0 16px 0 !important;
					margin: 8px 0 16px !important;
					font-size: 18px !important;
					font-weight: 300;
					border-bottom: 1px solid #cccccc;

				}
				.ever-settings-heading {
					position: relative;
					left: -17%;
				}
				.ever-settings-container::after {
					clear: both;
				}
				.ever-settings-container .ever-settings-main {
					width: 80%;
					display: inline-block;
				}
				.ever-settings-container .ever-settings-right-sidebar {
					width: 19%;
					float: right;
					padding-top: 10px;
					display: inline-block;
				}
				.ratings-stars-container {
					text-align: center;
					margin-top: 10px;
				}
				.ratings-stars-container span {
					vertical-align: text-top;
					color: #ffb900;
				}
				.ratings-stars-container a {
					text-decoration: none;
				}
				.pro th label::after {
					content: 'PRO';
					font-size: 11px;
					font-weight: 400;
					background: red;
					color: #fff;
					padding: 0 5px;
					line-height: 1;
					margin-left: 5px;
				}
			</style>
			<?php
		}
	}
endif;
