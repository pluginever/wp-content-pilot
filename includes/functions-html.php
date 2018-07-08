<?php

/**
 * Make dom
 *
 * @since 1.0.0
 *
 * @param $html
 *
 * @return \PHPHtmlParser\Dom
 *
 */
function wpcp_html_make_dom( $html ) {
    if ( ! $html instanceof PHPHtmlParser\Dom ) {
        $dom = new \PHPHtmlParser\Dom();
        $dom->setOptions( [
            'preserveLineBreaks' => true,
            'enforceEncoding'    => true,
            'cleanupInput'       => true,
        ] );
        $dom->load( $html );

        return $dom;
    }

    return $html;
}

/**
 * Fix html links
 *
 * @since 1.0.0
 *
 * @param $html
 * @param $host
 * @param bool $return_html
 * @param array $article
 * @param null $campaign_id
 *
 * @return string|\PHPHtmlParser\Dom
 *
 */
function wpcp_html_fix_links( $html, $host, $return_html = false, $article = [], $campaign_id = null ) {
    wpcp_log( 'dev', 'wpcp_html_fix_links' );
    $html  = wpcp_html_make_dom( $html );
    $nodes = $html->find( 'a[href], img[src], iframe[src]' );
    foreach ( $nodes as $node ) {

        if ( ( strpos( $node->getAttribute( 'href' ), '#' ) !== false ) || ( strpos( $node->getAttribute( 'href' ), '#' ) !== false ) ) {
            continue;
        }
        if ( ( strpos( $node->getAttribute( 'src' ), '#' ) !== false ) || ( strpos( $node->getAttribute( 'src' ), '#' ) !== false ) ) {
            continue;
        }

        $href = $node->getAttribute( 'href' );
        if ( isset( $href ) ) {
            $href = apply_filters( 'wpcp_content_links', $href, $article, $campaign_id );
            if ( empty( $href ) ) {
                $node->delete();
                wpcp_log( 'dev', 'removing link' );
                continue;
            }
            if ( ! wp_http_validate_url( $href ) ) {
                $node->setAttribute( 'src', wpcp_convert_rel_2_abs_url( $href, $host ) );
            }
        }

        $src = $node->getAttribute( 'src' );
        if ( isset( $src ) ) {
            $src = apply_filters( 'wpcp_content_links', $src, $article, $campaign_id );
            if ( empty( $src ) ) {
                $node->delete();
                wpcp_log( 'dev', 'removing image' );
                continue;
            }
            if ( ! wp_http_validate_url( $src ) ) {
                $node->setAttribute( 'src', wpcp_convert_rel_2_abs_url( $src, $host ) );
            }
        }
    }

    if ( $return_html ) {
        return $html->outerHtml;
    }

    return $html;
}

/**
 * Remove any bad tag exists in html
 * @since 1.0.0
 *
 * @param $html
 * @param bool $return_html
 *
 * @return mixed|\PHPHtmlParser\Dom
 *
 */
function wpcp_html_remove_bad_tags( $html, $return_html = false ) {

    $startsWithNodes = [
        'adspot',
        'conditionalAd-',
        'hidden-',
        'social-',
        'publication',
        'share-',
        'hp-',
        'ad-',
        'recommended-',
        'previous',
        'next',
        'copy',
        'similar',
    ];

    $equalsNodes = [
        'side',
        'links',
        'inset',
        'print',
        'fn',
        'ad',
        'hr'
    ];

    $endsWithNodes = [
        'meta',
        'tabs',
    ];

    $searchNodes = [
        'combx',
        'retweet',
        'mediaarticlerelated',
        'menucontainer',
        'navbar',
        'storytopbar-bucket',
        'utility-bar',
        'inline-share-tools',
        'comment', // not commented
        'PopularQuestions',
        'contact',
        'foot',
        'footer',
        'Footer',
        'footnote',
        'cnn_strycaptiontxt',
        'cnn_html_slideshow',
        'cnn_strylftcntnt',
        'shoutbox',
        'sponsor',
        'tags',
        'socialnetworking',
        'socialNetworking',
        'scroll', // not scrollable
        'cnnStryHghLght',
        'cnn_stryspcvbx',
        'pagetools',
        'post-attributes',
        'welcome_form',
        'contentTools2',
        'the_answers',
        'communitypromo',
        'promo_holder',
        'runaroundLeft',
        'subscribe',
        'vcard',
        'articleheadings',
        'date',
        'popup',
        'author-dropdown',
        'tools',
        'socialtools',
        'byline',
        'konafilter',
        'KonaFilter',
        'breadcrumbs',
        'wp-caption-text',
        'source',
        'legende',
        'ajoutVideo',
        'timestamp',
        'js_replies',
        'creative_commons',
        'topics',
        'pagination',
        'mtl',
        'author',
        'credit',
        'toc_container',
        'sharedaddy',
        'previousLink',
        'nextLink',
        'copyright',
        'sidebar',
        'flare',
        'facebook',
        'twitter',
//        'caption',
        'google',
        'more',
        'dropcap',
        'coments',
        'tab',
        'relatedposts',
        'related-posts',
        'reviewListing'
    ];

    $lists = [
        "[%s^='%s']" => $startsWithNodes,
        "[%s*='%s']" => $searchNodes,
        "[%s$='%s']" => $endsWithNodes,
        "[%s='%s']"  => $equalsNodes,
    ];

    $attrs = [
        'id',
        'class',
        'name',
    ];
    $doc   = wpcp_html_make_dom( $html );

    foreach ( $lists as $expr => $list ) {
        foreach ( $list as $value ) {
            foreach ( $attrs as $attr ) {
                $selector = sprintf( '*' . $expr, $attr, $value );
                foreach ( $doc->find( $selector ) as $nodes ) {
                    foreach ( $nodes as $node ) {
                        $node->delete();
                    }
                }
            }
        }
    }

    if ( $return_html ) {
        return $doc->outerHtml;
    }

    return $doc;
}

/**
 * Get all links
 * @since 1.0.0
 *
 * @param $html
 *
 * @return mixed
 *
 */
function wpcp_html_get_all_links( $html ) {
    preg_match_all( '/(href|src)="([^"]*)"/i', $html, $matched );

    return $links = array_pop( $matched );
}


/**
 * Remove hyperlinks
 *
 * @since 1.0.0
 *
 * @param $html
 * @param bool $keep_text
 *
 * @return null|string|string[]
 *
 */
function wpcp_html_remove_hyperlinks( $html, $keep_text = true ) {
    wpcp_log( 'dev', 'wpcp_html_remove_hyperlinks' );
    if ( $keep_text ) {
        return preg_replace( '#<a.*?>(.*?)</a>#i', '\1', $html );
    } else {
        return preg_replace( '#<a.*?>.*?</a>#i', '', $html );
    }
}

/**
 * Remove all images
 *
 * @since 1.0.0
 *
 * @param $html
 *
 * @return null|string|string[]
 *
 */
function wpcp_html_remove_images( $html ) {
    wpcp_log( 'dev', 'wpcp_html_remove_images' );

    return preg_replace( '#<img.*?>.*?>#i', '', $html );
}

/**
 * @since 1.0.0
 *
 * @param $sources
 * @param null $campaign_id
 *
 * @return string
 *
 */
function wpcp_html_make_image_tag( $sources ) {
    $html        = '';
    $css_classes = apply_filters( 'wpcp_template_image_classes', [ 'wpcp-image', 'attachment-thumbnail' ] );
    $classes     = implode( ' ', $css_classes );

    if ( is_array( $sources ) ) {
        foreach ( $sources as $source ) {
            $html .= "<img src='{$source}' class='$classes' /> ";
        }
    } else {
        $html .= "<img src='{$sources}' class='$classes' /> ";
    }

    return $html;
}
