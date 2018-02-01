<?php

namespace Pluginever\WPCP\Core;

class Template_Tags_Replacer extends Find_Replace {

    /**
     * @var object
     *
     */
    private static $instance;

    /**
     * Initialization
     *
     * @since 1.0.0
     *
     *
     * @return object|\Pluginever\WPCP\Core\Template_Tags_Replacer
     *
     */
    public static function init() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Template_Tags_Replacer ) ) {
            self::$instance = new Template_Tags_Replacer;
        }

        return self::$instance;
    }

    /**
     * @since 1.0.0
     *
     * @param $content
     * @param array $tags
     * @param $article
     *
     * @return mixed
     *
     */
    public function replace_tags( $content, $article, $tags = [] ) {
        $tags      = array_merge( $this->set_template_tags( $article ), $tags );
        $flip_tags = array_flip( $tags );
        $flip_tags = array_map( function ( $tag ) {
            return '{' . $tag . '}';
        }, $flip_tags );

        $tags = array_flip( $flip_tags );
        $this->set_find_replacer( $tags );

        return $this->find_replace( $content );
    }


    /**
     * @since 1.0.0
     */
    protected function set_template_tags( $article ) {
        $tags = [
            'url'     => ! empty( $article['url'] ) ? $article['url'] : '',
            'host'    => ! empty( $article['host'] ) ? $article['host'] : '',
            'source'  => ! empty( $article['source'] ) ? $article['source'] : '',
            'title'   => ! empty( $article['title'] ) ? $article['title'] : '',
            'author'  => ! empty( $article['author'] ) ? strip_tags( $article['author'] ) : '',
            'content' => ! empty( $article['content'] ) ? $article['content'] : '',
        ];

        if ( isset( $article['image'] ) ) {
            $image         = $this->make_image_tag( [ 'src' => $article['image'] ] );
            $tags['image'] = $image;
        }
        //make images from array
        $images = [];

        foreach ( $article['images'] as $image_src ) {
            $images[] = $this->make_image_tag( [ 'src' => $image_src ] );
        }
        $tags['images'] = implode( ' ', $images );

        return apply_filters( 'wpcp_template_tags_array', $tags, $this, $article );
    }

    /**
     * Make image tag
     *
     * @since 1.0.0
     *
     * @param $attributes
     *
     * @return string
     *
     */
    public function make_image_tag( $attributes ) {
        $css_classes = apply_filters( 'wpcp_template_image_classes', [ 'wpcp-image', 'attachment-thumbnail' ] );
        if ( isset( $attributes['class'] ) && ! empty( $attributes['class'] ) && is_array( $attributes['class'] ) ) {
            $css_classes = array_merge( $css_classes, $attributes['class'] );
        }

        $attributes['class'] = implode( ' ', $css_classes );

        $attrs = [];

        foreach ( $attributes as $attribute => $value ) {
            $attrs[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
        }
        $attributes = implode( ' ', $attrs );

        return "<img {$attributes} />";
    }


}
