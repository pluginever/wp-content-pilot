<?php
defined( 'ABSPATH' ) || die();

/**
 * @param $field
 *
 * @return string
 */
function wpcp_html_input_desc( $field ) {
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
 * @param $field
 *
 * @return string
 */
function wpcp_html_input_label( $field ) {
	$field = wp_parse_args( $field, array(
		'id'    => '',
		'label' => '',
	) );

	if ( empty( $field['label'] ) ) {
		return '';
	}

	$tooltip = '';
	if ( isset( $field['tooltip'] ) ) {
		$tooltip = sprintf( '<span class="wpcp-help-tip" data-tip="%1$s">[?]</span>', wp_kses_post( $field['tooltip'] ) );
	}

	return sprintf( '<label for="%1$s" class="wpcp-label">%2$s %3$s</label>', $field['id'], $field['label'], $tooltip );
}

/**
 * @param $value
 * @param $options
 *
 * @return string
 */
function wpcp_selected( $value, $options ) {
	if ( is_array( $options ) ) {
		$options = array_map( 'strval', $options );

		return selected( in_array( (string) $value, $options, true ), true, false );
	}

	return selected( $value, $options, false );
}

/**
 * @param $raw_attributes
 * @param array $extra
 *
 * @return string
 */
function wpcp_implode_html_attributes( $raw_attributes, $extra = array() ) {
	$attributes = array();
	foreach ( array_merge( $raw_attributes, $extra ) as $name => $value ) {
		$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}

	return implode( ' ', $attributes );
}

/**
 * Handle double column
 *
 * @param bool $end
 *
 * @return string
 */
function wpcp_double_column( $end = false ) {
	if ( $end ) {
		return '</div><!--.wpcp-double-column-->';
	}

	return sprintf( '<div class="wpcp-double-column">' );
}


/**
 * @param $field array
 *
 * @return string
 */
function wpcp_text_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'label'         => false,
		'name'          => '',
		'id'            => $field['name'],
		'value'         => (string) get_post_meta( $thepostid, $field['name'], true ),
		'placeholder'   => '',
		'class'         => 'short',
		'style'         => '',
		'wrapper_class' => '',
		'type'          => 'text',
		'size'          => 'regular',
		'desc'          => '',
		'attributes'    => [],
	) );

	$attributes = wpcp_implode_html_attributes( $field['attributes'] );
	$html       = wpcp_html_input_label( $field );
	$html       .= sprintf( '<input type="%1$s" class="wpcp-input %2$s-text %7$s" id="%3$s" name="%4$s" value="%5$s" %6$s placeholder="%8$s" autocomplete="off" style="%9$s"/>',
		$field['type'], $field['size'], $field['id'], $field['name'], $field['value'], $attributes, sanitize_html_class( $field['class'] ), $field['placeholder'], $field['style'] );
	$html       .= wpcp_html_input_desc( $field );

	return sprintf( '<p class="form-field wpcp-form-field %1$s-field %2$s">%3$s</p>', sanitize_html_class( $field['id'] ), sanitize_html_class( $field['wrapper_class'] ), $html );
}

/**
 * @param $field array
 *
 * @return string
 */
function wpcp_textarea_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'label'         => false,
		'name'          => '',
		'id'            => $field['name'],
		'value'         => (string) get_post_meta( $thepostid, $field['name'], true ),
		'placeholder'   => '',
		'class'         => 'short',
		'style'         => '',
		'wrapper_class' => '',
		'size'          => 'regular',
		'desc'          => '',
		'rows'          => '2',
		'cols'          => '20',
		'attributes'    => [],
	) );

	$attributes = wpcp_implode_html_attributes( $field['attributes'], [
		'rows' => $field['rows'],
		'cols' => $field['cols']
	] );

	$html = wpcp_html_input_label( $field );
	$html .= sprintf( '<textarea class="wpcp-input %1$s-text %2$s" id="%3$s" name="%4$s" placeholder="%5$s" style="%6$s" %7$s>%8$s</textarea>',
		$field['size'], sanitize_html_class( $field['class'] ), $field['id'], $field['name'], $field['placeholder'], $field['style'], $attributes, $field['value'] );
	$html .= wpcp_html_input_desc( $field );


	return sprintf( '<p class="form-field wpcp-form-field %1$s-field %2$s">%3$s</p>', sanitize_html_class( $field['id'] ), sanitize_html_class( $field['wrapper_class'] ), $html );
}

/**
 * @param $field array
 *
 * @return string
 */
function wpcp_checkbox_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'label'         => false,
		'name'          => '',
		'id'            => $field['name'],
		'value'         => (string) get_post_meta( $thepostid, $field['name'], true ),
		'placeholder'   => '',
		'class'         => 'short',
		'style'         => '',
		'wrapper_class' => '',
		'size'          => 'regular',
		'desc'          => '',
		'cbvalue'       => 'yes',
		'attributes'    => [],
	) );

	$attributes = wpcp_implode_html_attributes( $field['attributes'] );
	$html       = wpcp_html_input_label( $field );
	$html       .= sprintf( '<input type="checkbox" class="wpcp-input-check %1$s" id="%2$s" name="%3$s" value="%4$s"  %5$s %6$s/>',
		sanitize_html_class( $field['class'] ), $field['id'], $field['name'], $field['cbvalue'], $attributes, checked( $field['value'], $field['cbvalue'], false ) );
	$html       .= wpcp_html_input_desc( $field );


	return sprintf( '<p class="form-field wpcp-form-field %1$s-field %2$s">%3$s</p>', sanitize_html_class( $field['id'] ), sanitize_html_class( $field['wrapper_class'] ), $html );
}

/**
 * @param $field array
 *
 * @return string
 */
function wpcp_radio_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'label'         => false,
		'name'          => '',
		'id'            => $field['name'],
		'value'         => (string) get_post_meta( $thepostid, $field['name'], true ),
		'placeholder'   => '',
		'class'         => 'short',
		'style'         => '',
		'wrapper_class' => '',
		'size'          => 'regular',
		'desc'          => '',
		'inline'        => true,
	) );

	$html = wpcp_html_input_label( $field );
	$html .= sprintf( '<ul class="wpcp-radios %s">', $field['inline'] == true ? 'wpcp-inline' : '' );
	foreach ( $field['options'] as $key => $value ) {
		$html .= sprintf( '<li><label><input type="radio" name="%1$s" value="%2$s" class="%3$s" style="%4$s" %5$s>%6$s</label></li>', $field['name'], $key, $field['class'], $field['style'], checked( esc_attr( $field['value'] ), esc_attr( $key ), false ), esc_html( $value ) );
	}
	$html .= '</ul>';
	$html .= wpcp_html_input_desc( $field );


	return sprintf( '<fieldset class="form-field wpcp-form-field %1$s-field %2$s">%3$s</fieldset>', sanitize_html_class( $field['id'] ), sanitize_html_class( $field['wrapper_class'] ), $html );
}

/**
 * @param $field array
 *
 * @return string
 */
function wpcp_checkboxes_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'label'         => false,
		'name'          => '',
		'id'            => $field['name'],
		'value'         => (string) get_post_meta( $thepostid, $field['name'], true ),
		'placeholder'   => '',
		'class'         => 'short',
		'style'         => '',
		'wrapper_class' => '',
		'size'          => 'regular',
		'desc'          => '',
		'inline'        => true,
		'attributes'    => [],
	) );


	$html = wpcp_html_input_label( $field );
	$html .= sprintf( '<ul class="wpcp-radios %s">', $field['inline'] == true ? 'wpcp-inline' : '' );
	foreach ( $field['options'] as $key => $value ) {
		$html .= sprintf( '<li><label><input type="checkbox" name="%1$s[]" value="%2$s" class="%3$s" style="%4$s" %5$s>%6$s</label></li>', $field['name'], $key, $field['class'], $field['style'], checked( is_array( $field['value'] ) && in_array( $key, $field['value'] ), true, false ), esc_html( $value ) );
	}

	$html .= '</ul>';
	$html .= wpcp_html_input_desc( $field );

	return sprintf( '<fieldset class="form-field wpcp-form-field %1$s-field %2$s">%3$s</fieldset>', sanitize_html_class( $field['id'] ), sanitize_html_class( $field['wrapper_class'] ), $html );
}

/**
 * @param $field array
 *
 * @return string
 */
function wpcp_select_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'label'         => false,
		'name'          => '',
		'id'            => $field['name'],
		'value'         => (string) get_post_meta( $thepostid, $field['name'], true ),
		'options'       => [],
		'placeholder'   => '',
		'class'         => 'short',
		'style'         => '',
		'wrapper_class' => '',
		'size'          => 'regular',
		'desc'          => '',
		'attributes'    => [],
	) );

	$html    = wpcp_html_input_label( $field );
	$options = '';
	foreach ( $field['options'] as $key => $value ) {
		$options .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), wpcp_selected( $key, $field['value'] ), esc_html( $value ) );
	}
	$data = wpcp_implode_html_attributes( $field['attributes'] );
	$html .= sprintf( '<select class="wpcp-input select %1$s" id="%2$s" name="%3$s" %4$s style="%5$s">%6$s</select>', sanitize_html_class( $field['wrapper_class'] ), $field['id'], $field['name'], $data, $field['style'], $options );
	$html .= wpcp_html_input_desc( $field );

	return sprintf( '<p class="form-field wpcp-form-field %1$s-field %2$s">%3$s</p>', sanitize_html_class( $field['id'] ), sanitize_html_class( $field['wrapper_class'] ), $html );

}

/**
 * @param $field array
 *
 * @return string
 */
function wpcp_hidden_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'name'  => '',
		'id'    => $field['name'],
		'class' => $field['class'],
		'value' => (string) get_post_meta( $thepostid, $field['name'], true ),
	) );

	return sprintf( '<input type="hidden" class="%1$s" id="%2$s" value="%3$s">', sanitize_html_class( $field['class'] ), $field['id'], $field['value'] );
}

function wpcp_switch_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args( $field, array(
		'label'         => false,
		'name'          => '',
		'id'            => $field['name'],
		'value'         => (string) get_post_meta( $thepostid, $field['name'], true ),
		'check'         => 'on',
		'style'         => '',
		'class'         => '',
		'wrapper_class' => '',
		'desc'          => '',
		'attributes'    => [],
	) );

	$html = wpcp_html_input_label( $field );

	$html .= sprintf( '
					<label for="%1$s">
					<input type="checkbox" class="%2$s" id="%3$s" name="%4$s" value="%5$s" %6$s%/>
					<span class="wpcp-switch-view"></span>
					</label>', $field['id'], $field['class'], $field['id'], $field['name'], $field['check'], checked( $field['value'], $field['check'], false ) );

	return sprintf( '<p class="form-field wpcp-switch-field wpcp-form-field %1$s-field %2$s">%3$s</p>', sanitize_html_class( $field['id'] ), sanitize_html_class( $field['wrapper_class'] ), $html );
}


function wpcp_range_input( $field ) {
	$field               = wp_parse_args( $field, array(
		'class'      => '',
		'attributes' => [],
		'min'        => 0,
		'max'        => 500,
		'skin'       => 'round',
	) );
	$field['class']      .= ' wpcp-range-slider ';
	$field['attributes'] = wp_parse_args( $field['attributes'], array(
		'data-min'  => $field['min'],
		'data-max'  => $field['max'],
		'data-skin' => $field['skin'],
	) );

	return wpcp_text_input( $field );
}

