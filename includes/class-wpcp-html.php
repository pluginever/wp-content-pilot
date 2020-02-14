<?php
defined( 'ABSPATH' ) || die();

class WPCP_HTML {

	public static function text_input( $field ) {
		global $thepostid, $post;
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
		$args      = apply_filters( 'wpcp_text_input', wp_parse_args( $field, array(
			'type'          => 'text',
			'label'         => '',
			'name'          => '',
			'id'            => '',
			'value'         => '',
			'default'       => '',
			'size'          => 'regular',
			'class'         => 'short',
			'wrapper_class' => '',
			'placeholder'   => '',
			'tooltip'       => '',
			'desc'          => '',
			'css'           => '',
			'after'         => '',
			'attrs'         => array(),
		) ) );

		$args['id'] = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $args['name'] );

		if ( empty( $args['value'] ) && $meta_value = get_post_meta( $thepostid, $field['name'], true ) ) {
			$args['value'] = (string) $meta_value;
		} else {
			$args['value'] = $args['default'];
		}

		$attributes = self::implode_html_attributes( $args['attrs'], array(
			'placeholder' => $args['placeholder'],
			'style'       => $args['css']
		) );

		if ( ! empty( $args['tooltip'] ) ) {
			$args['tooltip'] = sprintf( '<span class="wpcp-tooltip" data-tip="%1$s">[?]</span>', wp_kses_post( $field['tooltip'] ) );
		}

		$html = sprintf( '<p class="form-field wpcp-field %1$s-field %2$s-input %3$s">', sanitize_html_class( $args['id'] ), $args['type'], sanitize_html_class( $args['wrapper_class'] ) );
		$html .= ! empty( $args['label'] ) ? sprintf( '<label for="%1$s" class="wpcp-label">%2$s %3$s</label>', $args['id'], $args['label'], $args['tooltip'] ) : '';
		$html .= sprintf( '<input type="%1$s" class="wpcp-input %2$s-text %3$s" id="%4$s" name="%5$s" value="%6$s" autocomplete="off" %7$s/>', $args['type'], $args['size'], $args['class'], $args['id'], $args['name'], $args['value'], $attributes );
		$html .= ! empty( $args['after'] ) ? $args['after'] : '';
		$html .= ! empty( $args['desc'] ) ? sprintf( '<span class="wpcp-desc description">%s</span>', wp_kses_post( $args['desc'] ) ) : '';
		$html .= '</p>';

		return $html;
	}


	public static function textarea_input( $field ) {
		global $thepostid, $post;
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
		$args      = apply_filters( 'wpcp_textarea_input', wp_parse_args( $field, array(
			'type'          => 'text',
			'label'         => '',
			'name'          => '',
			'id'            => '',
			'value'         => '',
			'default'       => '',
			'size'          => 'regular',
			'class'         => 'short',
			'wrapper_class' => '',
			'placeholder'   => '',
			'tooltip'       => '',
			'desc'          => '',
			'css'           => '',
			'rows'          => '2',
			'cols'          => '20',
			'attrs'         => array(),
		) ) );

		$args['id'] = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $args['name'] );
		if ( empty( $args['value'] ) && ! empty( $thepostid ) && $meta_value = get_post_meta( $thepostid, $args['name'], true ) ) {
			$args['value'] = (string) $meta_value;
		} else {
			$args['value'] = $args['default'];
		}
		$attributes = self::implode_html_attributes( $args['attrs'], array(
			'placeholder' => $args['placeholder'],
			'style'       => $args['css'],
			'rows'        => $args['rows'],
			'cols'        => $args['cols'],
		) );

		if ( ! empty( $args['tooltip'] ) ) {
			$args['tooltip'] = sprintf( '<span class="wpcp-tooltip" data-tip="%1$s">[?]</span>', wp_kses_post( $args['tooltip'] ) );
		}


		$html = sprintf( '<p class="form-field wpcp-field %1$s-field %2$s-input %3$s">', sanitize_html_class( $args['id'] ), $args['type'], sanitize_html_class( $args['wrapper_class'] ) );
		$html .= ! empty( $args['label'] ) ? sprintf( '<label for="%1$s" class="wpcp-label">%2$s %3$s</label>', $args['id'], $args['label'], $args['tooltip'] ) : '';
		$html .= sprintf( '<textarea class="wpcp-input %1$s-text %2$s" id="%3$s" name="%4$s" autocomplete="off" %5$s>%6$s</textarea>', $args['size'], $args['class'], $args['id'], $args['name'], $attributes, $args['value'] );
		$html .= ! empty( $args['desc'] ) ? sprintf( '<span class="wpcp-desc description">%s</span>', wp_kses_post( $args['desc'] ) ) : '';
		$html .= '</p>';

		return $html;
	}


	public static function checkbox_input( $field ) {
		global $thepostid, $post;
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
		$args      = apply_filters( 'wpcp_checkbox_input', wp_parse_args( $field, array(
			'type'          => 'text',
			'label'         => '',
			'name'          => '',
			'id'            => '',
			'value'         => '',
			'default'       => '',
			'size'          => 'regular',
			'class'         => 'short',
			'wrapper_class' => '',
			'placeholder'   => '',
			'tooltip'       => '',
			'desc'          => '',
			'css'           => '',
			'cbvalue'       => 'on',
			'attrs'         => array(),
		) ) );

		$args['id'] = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $args['name'] );
		if ( empty( $args['value'] ) && ! empty( $thepostid ) && $meta_value = get_post_meta( $thepostid, $args['name'], true ) ) {
			$args['value'] = (string) $meta_value;
		} else {
			$args['value'] = $args['default'];
		}
		$attributes = self::implode_html_attributes( $args['attrs'], array(
			'style' => $args['css'],
		) );

		if ( ! empty( $args['tooltip'] ) ) {
			$args['tooltip'] = sprintf( '<span class="wpcp-tooltip" data-tip="%1$s">[?]</span>', wp_kses_post( $args['tooltip'] ) );
		}

		$checked = checked( $args['value'], $args['cbvalue'], false );
		$input   = sprintf( '<input type="checkbox" class="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s %6$s/><span class="checkmark"></span>', $args['class'], $args['id'], $args['name'], $args['cbvalue'], $checked, $attributes );

		$html = sprintf( '<p class="form-field wpcp-field wpcp-checkbox %1$s-field %2$s-input %3$s">', sanitize_html_class( $args['id'] ), $args['type'], sanitize_html_class( $args['wrapper_class'] ) );
		$html .= ! empty( $args['label'] ) ? sprintf( '<label for="%1$s" class="wpcp-label">%2$s %3$s %4$s</label>', $args['id'], $input, $args['label'], $args['tooltip'] ) : '';
		$html .= ! empty( $args['desc'] ) ? sprintf( '<span class="wpcp-desc description">%s</span>', wp_kses_post( $args['desc'] ) ) : '';
		$html .= '</p>';

		return $html;
	}


	public static function multi_checkbox_input( $field ) {

	}

	public static function radio_input( $field ) {

	}


	public static function select_input( $field ) {
		global $thepostid, $post;
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

		$args = apply_filters( 'wpcp_select_input', wp_parse_args( $field, array(
			'label'         => '',
			'name'          => null,
			'options'       => array(),
			'value'         => array(),
			'default'       => array(),
			'class'         => '',
			'wrapper_class' => '',
			'id'            => '',
			'tooltip'       => '',
			'css'           => '',
			'size'          => 'large',
			'placeholder'   => __( '-- Please Select --', 'wp-content-pilot' ),
			'multiple'      => false,
			'attrs'         => array()
		) ) );

		$args['id'] = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $args['name'] );
		if ( empty( $args['value'] ) && ! empty( $thepostid ) && $meta_value = get_post_meta( $thepostid, sanitize_key( $field['name'] ), true ) ) {
			$args['value'] = $meta_value;
		} else {
			$args['value'] = $args['default'];
		}

		$attributes = self::implode_html_attributes( $args['attrs'], array(
			'style' => $args['css'],
		) );

		if ( ! empty( $args['tooltip'] ) ) {
			$args['tooltip'] = sprintf( '<span class="wpcp-tooltip" data-tip="%1$s">[?]</span>', wp_kses_post( $field['tooltip'] ) );
		}

		$options = '';
		foreach ( $args['options'] as $key => $value ) {
			$options .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), self::is_selected( $key, $args['value'] ), esc_html( $value ) );
		}

		$html = sprintf( '<p class="form-field wpcp-field wpcp-check %1$s-field %2$s-input %3$s">', sanitize_html_class( $args['id'] ), 'select', sanitize_html_class( $args['wrapper_class'] ) );
		$html .= ! empty( $args['label'] ) ? sprintf( '<label for="%1$s" class="wpcp-label">%2$s %3$s</label>', $args['id'], $args['label'], $args['tooltip'] ) : '';
		$html .= sprintf( '<select class="wpcp-input %1$s-text %2$s" id="%3$s" name="%4$s" %5$s/>%6$s</select>', $args['size'], $args['class'], $args['id'], $args['name'], $attributes, $options );
		$html .= ! empty( $args['desc'] ) ? sprintf( '<span class="wpcp-desc description">%s</span>', wp_kses_post( $args['desc'] ) ) : '';
		$html .= '</p>';

		return $html;


	}


	public static function range_input( $field ) {
		$field = wp_parse_args( $field, array(
			'class' => '',
			'attrs' => [],
			'min'   => 0,
			'max'   => 500,
			'skin'  => 'round',
		) );

		$field['class'] .= ' wpcp-range-slider ';
		$field['attrs'] = wp_parse_args( $field['attrs'], array(
			'data-min'  => $field['min'],
			'data-max'  => $field['max'],
			'data-skin' => $field['skin'],
		) );

		return self::text_input( $field );
	}


	public static function switch_input( $field ) {
		global $thepostid, $post;
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

		$args = apply_filters( 'wpcp_switch_input', wp_parse_args( $field, array(
			'label'         => false,
			'name'          => '',
			'id'            => '',
			'value'         => '',
			'default'       => '',
			'check'         => 'on',
			'css'           => '',
			'class'         => '',
			'wrapper_class' => '',
			'tooltip'       => '',
			'desc'          => '',
			'attrs'         => [],
		) ) );

		$args['id'] = esc_attr( ! empty( $args['id'] ) ? $args['id'] : $args['name'] );
		if ( empty( $args['value'] ) && ! empty( $thepostid ) && $meta_value = get_post_meta( $thepostid, $field['name'], true ) ) {
			$args['value'] = (string) $meta_value;
		} else {
			$args['value'] = $args['default'];
		}
		$attributes = self::implode_html_attributes( $args['attrs'], array(
			'style' => $args['css'],
		) );

		if ( ! empty( $args['tooltip'] ) ) {
			$args['tooltip'] = sprintf( '<span class="wpcp-tooltip" data-tip="%1$s">[?]</span>', wp_kses_post( $args['tooltip'] ) );
		}
		$checked = checked( $args['value'], $args['check'], false );
		$input   = sprintf( '<input type="checkbox" class="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s %6$s/><span class="wpcp-switch"></span>', $args['class'], $args['id'], $args['name'], $args['check'], $checked, $attributes );
		$desc    = ! empty( $args['desc'] ) ? sprintf( '<span class="wpcp-desc description">%s</span>', wp_kses_post( $args['desc'] ) ) : '';
		$html    = sprintf( '<p class="form-field wpcp-field wpcp-switch %1$s-field %2$s-input %3$s">', sanitize_html_class( $args['id'] ), 'switch', sanitize_html_class( $args['wrapper_class'] ) );
		$html    .= sprintf( '<label for="%1$s" class="wpcp-label">%2$s %3$s %4$s %5$s</label>', $args['id'], $args['label'], $args['tooltip'], $input, $desc );
		$html    .= '</p>';

		return $html;

	}


	public static function hidden_input( $field ) {

	}

	/**
	 * @param $field
	 *
	 * @return string
	 */
	public static function input_desc( $field ) {
		$field = wp_parse_args( $field, array(
			'id'   => '',
			'desc' => '',
		) );

		return sprintf( '<span class="wpcp-input-desc">%s</span>', wp_kses( $field['desc'], array(
			'a'      => array( 'class' => true, 'href' => true ),
			'button' => array( 'class' => true ),
			'i'      => array( 'class' => true )
		) ) );
	}


	/**
	 * @param $value
	 * @param $options
	 *
	 * @return string
	 */
	public static function is_selected( $value, $options ) {

		if ( is_array( $options ) ) {
			$options = array_map( 'intval', $options );

			return selected( in_array( $value, $options, true ), true, false );
		}

		return selected( $value, $options, false );
	}


	/**
	 * @param $raw_attributes
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function implode_html_attributes( $raw_attributes, $extra = array() ) {
		$attributes = array();
		foreach ( array_merge( $raw_attributes, $extra ) as $name => $value ) {
			$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $attributes );
	}

	/**
	 * @param string $class
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public static function start_double_columns( $class = '' ) {
		return sprintf( '<div class="wpcp-dc %s">', sanitize_html_class( $class ) );
	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	public static function end_double_columns() {
		return '</div><!--wpcp-dc-->';
	}

}
