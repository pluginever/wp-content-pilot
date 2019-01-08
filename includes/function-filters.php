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
        $content = wp_strip_all_tags( $content, true  );
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
            'xml:lang' => true,
        ),
        'table'      => array(

        ),
        'tbody'      => array(

        ),
        'td'         => array(

        ),
        'tfoot'      => array(

        ),
        'th'         => array(
        ),
        'thead'      => array(

        ),
        'tr'         => array(

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

    //$allowed_tags = apply_filters( 'wpcp_allowed_html_tags', $default_allowed_tags, $article, $campaign_id );

    return wp_kses( $content, $default_allowed_tags );
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
    error_log('wpcp_maybe_remove_images');
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
 * @since 1.0.0
 *
 * @param $post_type
 * @param $campaign_id
 * @param $article
 *
 * @return mixed|null
 *
 */
function wpcp_set_post_type( $post_type, $campaign_id, $keyword ) {
    $new_type = wpcp_get_post_meta( $campaign_id, '_post_type', '0' );
    if ( ! empty( $new_type ) ) {
        return $new_type;
    }

    return $post_type;
}

/**
 * @since 1.0.0
 *
 * @param $status
 * @param $campaign_id
 * @param $article
 *
 * @return mixed|null
 *
 */
function wpcp_set_post_status( $status, $campaign_id, $keyword ) {
    $new_status = wpcp_get_post_meta( $campaign_id, '_post_status', '0' );
    if ( ! empty( $new_status ) ) {
        return $new_status;
    }

    return $status;
}

/**
 * @since 1.0.0
 *
 * @param $author
 * @param $campaign_id
 * @param $article
 *
 * @return mixed|null
 *
 */
function wpcp_set_post_author( $author, $campaign_id, $keyword ) {
    $new_author = wpcp_get_post_meta( $campaign_id, '_post_author', '0' );
    if ( ! empty( $new_author ) ) {
        return $new_author;
    }

    return $author;
}

/**
 * @since 1.0.0
 *
 * @param $excerpt
 * @param $campaign_id
 * @param $article
 *
 * @return string
 *
 */
function wpcp_set_post_excerpt( $excerpt, $campaign_id, $keyword ) {
    if ( '1' !== wpcp_get_post_meta( $campaign_id, '_excerpt', '0' ) ) {
        $excerpt = '';
    }

    return $excerpt;
}

/**
 * Set post comment status
 *
 * @since 1.0.3
 *
 * @param string $status
 * @param int    $campaign_id
 * @param string $keyword
 *
 * @return string
 */
function wpcp_set_comment_status( $status, $campaign_id, $keyword ) {
    if ( '1' !== wpcp_get_post_meta( $campaign_id, '_comment_status', '0' ) ) {
        return $status;
    }

    return 'open';
}

/**
 * Set post ping status
 *
 * @since 1.0.3
 *
 * @param string $status
 * @param int    $campaign_id
 * @param string $keyword
 *
 * @return string
 */
function wpcp_set_ping_status( $status, $campaign_id, $keyword ) {
    if ( '1' !== wpcp_get_post_meta( $campaign_id, '_ping_status', '0' ) ) {
        return $status;
    }

    return 'open';
}

/**
 * Replace content template with tags
 * @since 1.0.0
 *
 * @param $content
 * @param $article
 * @param $campaign_id
 *
 * @return mixed
 */
function wpcp_post_content_as_template( $content, $article, $campaign_id ) {
    wpcp_log('dev', 'wpcp_post_content_as_template');
    $template = wpcp_get_post_meta( $campaign_id, '_post_template', '' );
    if ( ! empty( $template ) ) {
        $parsed_content =  wpcp_parse_template_tags( $template, $article, $campaign_id);
        return str_replace('{content}', $content, $parsed_content);
    }

    return $content;
}

/**
 * @param $title
 * @param $article
 * @param $campaign_id
 *
 * @return mixed
 */
function wpcp_post_title_as_template( $title, $article, $campaign_id ) {
    $template = wpcp_get_post_meta( $campaign_id, '_post_title', '' );
    if ( ! empty( $template ) ) {
        $parsed_content =  wpcp_parse_template_tags( $template, $article, $campaign_id );
        return str_replace('{title}', $title, $parsed_content);
    }

    return $title;
}

/**
 * Rejects article from the banned hosts for article campaign
 *
 * @since 1.0.0
 *
 * @param $links
 *
 * @return mixed
 *
 */
function wpcp_reject_banned_hosts( $links ) {
    $banned_hosts = wpcp_get_settings( 'banned_hosts', 'wpcp_settings_article', [] );
    if ( ! empty( $banned_hosts ) ) {
        $banned_hosts = wpcp_string_to_array( $banned_hosts, PHP_EOL );

        foreach ( $links as $link_key => $link ) {
            foreach ( $banned_hosts as $host ) {
                if ( strpos( $link, 'googleapis' ) !== false ) {
                    continue;
                }

                if ( strpos( $link, $host ) !== false ) {
                    wpcp_log( 'dev', "Rejecting link {$link} because contains {$host}" );
                    unset( $links[ $link_key ] );
                }
            }
        }
    }


    return $links;
}

/**
 * Test the acceptance of the article
 *
 * @since 1.0.0
 *
 * @param $article
 * @param $campaign_id
 * @param $keyword
 *
 * @return \WP_Error|array
 *
 */
function wpcp_test_article_acceptance( $article, $campaign_id, $keyword ) {
    if ( ! is_array( $article ) || empty( $article ) || is_wp_error($article) ) {
        return $article;
    }

    //test content length
    $is_required_length = wpcp_get_post_meta( $campaign_id, '_min_words', 0 );
    if ( ! empty( $is_required_length ) ) {
        $words_count = str_word_count( $article['content'] );
        if ( $words_count < $is_required_length ) {
            return new WP_Error( 'lack-of-content', sprintf( __( "Post is rejected due to less content. Required %d Found %d", 'wpcp' ), $is_required_length, $words_count ) );
        }
    }


    //skip post wihtout images
    $is_required_img = wpcp_get_post_meta( $campaign_id, '_skip_no_image', 0 );
    if ( ! empty( $is_required_img ) && empty( $article['image'] ) ) {
        return new WP_Error( 'no-image-found', __( 'Post is rejected because could not find any image in the post.', 'wpcp' ) );
    }

    //skip post if not eng
    $is_skip_non_english = wpcp_get_post_meta( $campaign_id, '_skip_not_eng', 0 );
    if ( $is_skip_non_english && ! preg_match( '/[^A-Za-z0-9]/', $article['content'] ) ) {
        return new WP_Error( 'invalid-language', __( 'Post is rejected because language is not english.', 'wpcp' ) );
    }

    //skip if duplicate title
    $is_duplicate_title = wpcp_get_post_meta( $campaign_id, '_skip_duplicate_title', 0 );
    if ( ! empty( $is_duplicate_title ) ) {
        $post_type    = wpcp_get_post_meta( $campaign_id, '_post_type', 0 );
        $is_duplicate = get_page_by_title( $article['title'], OBJECT, $post_type );
        if ( $is_duplicate ) {
            return new WP_Error( 'duplicate-post', __( 'Post is rejected because post with same title exit.', 'wpcp' ) );
        }
    }

    return $article;
}
