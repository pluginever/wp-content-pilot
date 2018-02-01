<?php
/**
 * @since 1.0.0
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return string
 *
 */
function wpcp_set_content_html_or_text( $content, $article, $campaign_id ) {
    wpcp_log( 'Dev', 'wpcp_set_content_html_or_text' );
    if ( 'html' != wpcp_get_post_meta( $campaign_id, '_content_type', 'html' ) ) {
        $content = strip_tags( $content );
    }

    return $content;
}

/**
 * @since 1.0.
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return string
 *
 */
function wpcp_remove_unauthorized_html( $content, $article, $campaign_id ) {
    wpcp_log( 'Dev', 'wpcp_remove_unauthorized_html' );
    $default_allowed_tags = [
        'a'          => array(
            'href'   => true,
            'target' => true,
        ),
        'audio'      => array(
            'autoplay' => true,
            'controls' => true,
            'loop'     => true,
            'muted'    => true,
            'preload'  => true,
            'src'      => true,
        ),
        'b'          => array(),
        'blockquote' => array(
            'cite'     => true,
            'lang'     => true,
            'xml:lang' => true,
        ),
        'br'         => array(),
        'button'     => array(
            'disabled' => true,
            'name'     => true,
            'type'     => true,
            'value'    => true,
        ),
        'em'         => array(),
        'h2'         => array(
            'align' => true,
        ),
        'h3'         => array(
            'align' => true,
        ),
        'h4'         => array(
            'align' => true,
        ),
        'h5'         => array(
            'align' => true,
        ),
        'h6'         => array(
            'align' => true,
        ),
        'i'          => array(),
        'img'        => array(
            'alt'    => true,
            'align'  => true,
            'height' => true,
            'src'    => true,
            'width'  => true,
        ),
        'p'          => array(
            'align'    => true,
            'dir'      => true,
            'lang'     => true,
            'xml:lang' => true,
        ),
        'table'      => array(
            'align'       => true,
            'bgcolor'     => true,
            'border'      => true,
            'cellpadding' => true,
            'cellspacing' => true,
            'dir'         => true,
            'rules'       => true,
            'summary'     => true,
            'width'       => true,
        ),
        'tbody'      => array(
            'align'   => true,
            'char'    => true,
            'charoff' => true,
            'valign'  => true,
        ),
        'td'         => array(
            'abbr'    => true,
            'align'   => true,
            'axis'    => true,
            'bgcolor' => true,
            'char'    => true,
            'charoff' => true,
            'colspan' => true,
            'dir'     => true,
            'headers' => true,
            'height'  => true,
            'nowrap'  => true,
            'rowspan' => true,
            'scope'   => true,
            'valign'  => true,
            'width'   => true,
        ),
        'tfoot'      => array(
            'align'   => true,
            'char'    => true,
            'charoff' => true,
            'valign'  => true,
        ),
        'th'         => array(
            'abbr'    => true,
            'align'   => true,
            'axis'    => true,
            'bgcolor' => true,
            'char'    => true,
            'charoff' => true,
            'colspan' => true,
            'headers' => true,
            'height'  => true,
            'nowrap'  => true,
            'rowspan' => true,
            'scope'   => true,
            'valign'  => true,
            'width'   => true,
        ),
        'thead'      => array(
            'align'   => true,
            'char'    => true,
            'charoff' => true,
            'valign'  => true,
        ),
        'tr'         => array(
            'align'   => true,
            'bgcolor' => true,
            'char'    => true,
            'charoff' => true,
            'valign'  => true,
        ),
        'u'          => array(),
        'ul'         => array(
            'type' => true,
        ),
        'ol'         => array(
            'start'    => true,
            'type'     => true,
            'reversed' => true,
        ),
        'li'         => array(),
        'iframe'     => array(
            'frameborder' => true,
            'height'      => true,
            'width'       => true,
            'src'         => true,
        )
    ];

    $allowed_tags = apply_filters( 'wpcp_allowed_html_tags', $default_allowed_tags, $article, $campaign_id );

    return wp_kses( $content, $allowed_tags );
}

/**
 * @since 1.0.0
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return string
 *
 */
function wpcp_maybe_remove_hyperlinks( $content, $article, $campaign_id ) {
    if ( wpcp_get_post_meta( $campaign_id, '_strip_links', '0' ) ) {
        wpcp_log( 'Dev', 'wpcp_maybe_remove_hyperlinks' );

        return wpcp_html_remove_hyperlinks( $content );
    }

    return $content;
}

/**
 * @since 1.0.0
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return string
 *
 */
function wpcp_maybe_remove_images( $content, $article, $campaign_id ) {
    if ( ! empty( wpcp_get_post_meta( $campaign_id, '_remove_images', '0' ) ) ) {
        wpcp_log( 'Dev', 'wpcp_maybe_remove_images' );

        return wpcp_html_remove_images( $content );
    }

    return $content;
}

/**
 * @since 1.0.0
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return \PHPHtmlParser\Dom
 *
 */
function wpcp_maybe_fix_links( $content, $article, $campaign_id ) {
    wpcp_log( 'Dev', 'wpcp_fix_links' );

    return wpcp_html_fix_links( $content, $article['host'], true, $article, $campaign_id );
}

/**
 * Replace template tags from content
 *
 * @since 1.0.0
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return string
 *
 */
function wpcp_content_replace_template_tags( $content, $article, $campaign_id ) {
    $content_template = wpcp_get_post_meta( $campaign_id, '_post_template', true );
    $replacer         = Pluginever\WPCP\Core\Template_Tags_Replacer::init();
    $content          = $replacer->replace_tags( $content_template, $article );

    return apply_filters( 'wpcp_content_replace_template_tags', $content, $replacer, $article, $campaign_id );
}

/**
 * @since 1.0.0
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return string
 *
 */
function wpcp_title_replace_template_tags( $content, $article, $campaign_id ) {
    $title_template = wpcp_get_post_meta( $campaign_id, '_post_title', true );
    $replacer       = Pluginever\WPCP\Core\Template_Tags_Replacer::init();
    $title          = $replacer->replace_tags( $title_template, $article );

    return apply_filters( 'wpcp_content_replace_template_tags', $title, $replacer, $article, $campaign_id );
}


function wpcp_set_post_type( $post_type, $article, $campaign_id ) {
    $new_type = wpcp_get_post_meta( $campaign_id, '_post_type', '0' );
    if ( ! empty( $new_type ) ) {
        return $new_type;
    }

    return $post_type;
}

function wpcp_set_post_status( $status, $article, $campaign_id ) {
    $new_status = wpcp_get_post_meta( $campaign_id, '_post_status', '0' );
    if ( ! empty( $new_status ) ) {
        return $new_status;
    }

    return $status;
}

function wpcp_set_post_author( $author, $article, $campaign_id ) {
    $new_author = wpcp_get_post_meta( $campaign_id, '_post_author', '0' );
    if ( ! empty( $new_author ) ) {
        return $new_author;
    }

    return $author;
}

function wpcp_set_post_excerpt( $excerpt, $article, $campaign_id ) {
    if ( '1' !== wpcp_get_post_meta( $campaign_id, '_excerpt', '0' ) ) {
        $excerpt = '';
    }

    return $excerpt;
}
