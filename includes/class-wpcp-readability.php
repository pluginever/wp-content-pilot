<?php
defined( 'ABSPATH' ) || die();

class WPCP_Readability {
    protected $url = '';
    
    protected $title = '';
    
    protected $content = '';
    
    protected $excerpt = '';
    
    protected $image = '';
    
    protected $author = '';
    
    protected $language = '';
    
    protected $pub_date = '';
    
    protected $tags = '';
    
    protected $categories = '';
    
    protected $meta_description = '';
    
    /**
     * @since 1.2.0
     * WPCP_Readability constructor.
     */
    public function __construct() {
    
    }
    
    
    /**
     * @param $html
     *
     * @return WP_Error
     * @since 1.2.0
     */
    public function parse( $html, $url ) {
        //set properties
        $this->url = esc_url( $url );
        
        //process full document
        $html = $this->pre_process_html( $html, $this->url );
        
        $readability                          = new Readability( $html, $this->url );
        $readability->debug                   = false;
        $readability->convertLinksToFootnotes = false;
        $result                               = $readability->init();
        
        if ( ! $result ) {
            wpcp_logger()->error( sprintf( __( 'Not readable [%s]', 'wp-content-pilot' ), $this->url ) );
            
            return new WP_Error( 'readability-error', __( 'Content is not readable', 'wp-content-pilot' ) );
        }

//		$title   = $readability->getTitle()->textContent;
//		$content = wpcp_remove_empty_tags_recursive( $readability->getContent()->innerHTML );
        $title   = $readability->getTitle()->textContent;
        $content = wpcp_remove_empty_tags_recursive( $readability->getContent()->innerHTML );
        
        $content = wpcp_remove_emoji( $content );
        $content = force_balance_tags( $content );
        $content = wpcp_remove_unauthorized_html( $content );
        $content = wpcp_remove_continue_reading( $content );
        
        //final content
        $this->content = $content;
        
        //parse meta
        $metas = $this->parse_meta( $html );
        
        //title
        if ( array_key_exists( 'og:title', $metas ) ) {
            $title = $metas['og:title'];
        } else if ( array_key_exists( 'twitter:title', $metas ) ) {
            $this->excerpt = $metas['twitter:title'];
        }
        $this->title = $this->clean_title( $title );
        
        
        //set image
        if ( array_key_exists( 'og:image', $metas ) ) {
            $this->image = esc_url( $metas['og:image'] );
        } elseif ( array_key_exists( 'twitter:image', $metas ) ) {
            $this->image = esc_url( $metas['twitter:image'] );
        } else {
            $content_dom = wpcp_str_get_html( $content );
            $img         = $content_dom->find( 'img', 0 );
            if ( $img && $src = $img->getAttribute( 'src' ) ) {
                $this->image = $src;
            }
        }
        
        //excerpt
        if ( array_key_exists( 'description', $metas ) ) {
            $this->excerpt = $metas['description'];
        } else if ( array_key_exists( 'og:description', $metas ) ) {
            $this->excerpt = $metas['og:description'];
        } else if ( array_key_exists( 'twitter:description', $metas ) ) {
            $this->excerpt = $metas['twitter:description'];
        }
        
        //language
        if ( array_key_exists( 'og:locale', $metas ) ) {
            $this->language = $metas['og:locale'];
        }
        
        //author
        //language
        if ( array_key_exists( 'author', $metas ) ) {
            $this->author = $metas['author'];
        }
        
    }
    
    /**
     * @param $html simple_html_dom
     * @param $url
     *
     * @return mixed
     * @since 1.2.0
     */
    protected function pre_process_html( $docuemnt, $url ) {
        if ( ! $docuemnt instanceof simple_html_dom ) {
            $docuemnt = wpcp_str_get_html( $docuemnt );
        }
        //fix image link
        /* @var $img simple_html_dom_node */
        foreach ( $docuemnt->find( 'img' ) as $img ) {
            $urls = [
                $img->getAttribute( 'src' ),
                $img->getAttribute( 'srcset' ),
                $img->getAttribute( 'data-src' ),
                $img->getAttribute( 'data-original' ),
                $img->getAttribute( 'data-orig' ),
                $img->getAttribute( 'data-url' ),
            ];
            
            $src = array_filter( $urls );
            $src = reset( $src );
            $img->setAttribute( 'src', $this->to_absolute_uri( $src, $url ) );
            $img->removeAttribute( 'class' );
            if ( empty( $img->getAttribute( 'src' ) ) ) {
                $img->remove();
            }
        }
        
        //fix relative url
        foreach ( $docuemnt->find( 'a' ) as $a ) {
            /* @var $a simple_html_dom_node */
            $a->setAttribute( 'href', $this->to_absolute_uri( $a->getAttribute( 'href' ), $url ) );
        }
        
        return $docuemnt->outertext;
    }
    
    /**
     * Parse meta
     *
     * @param $html
     *
     * @return array
     * @since 1.2.0
     */
    protected function parse_meta( $html ) {
        $values = [];
        // Match "description", or Twitter's "twitter:description" (Cards)
        // in name attribute.
        $namePattern = '/^\s*((twitter)\s*:\s*)?(description|title|image|author)\s*$/i';
        
        // Match Facebook's Open Graph title & description properties.
        $propertyPattern = '/^\s*og\s*:\s*(description|title|image|author)\s*$/i';
        $dom             = wpcp_str_get_html( $html );
        
        foreach ( $dom->find( 'meta' ) as $meta ) {
            /* @var $meta simple_html_dom_node */
            $elementName     = $meta->getAttribute( 'name' );
            $elementProperty = $meta->getAttribute( 'property' );
            
            $name = null;
            if ( preg_match( $namePattern, $elementName ) ) {
                $name = $elementName;
            } elseif ( preg_match( $propertyPattern, $elementProperty ) ) {
                $name = $elementProperty;
            } elseif ( ! empty( $elementProperty ) ) {
                $name = $elementProperty;
            }
            if ( $name ) {
                $content = $meta->getAttribute( 'content' );
                if ( $content ) {
                    // Convert to lowercase and remove any whitespace
                    // so we can match below.
                    $name            = preg_replace( '/\s/', '', strtolower( $name ) );
                    $values[ $name ] = trim( $content );
                }
            }
        }
        
        return $values;
    }
    
    /**
     * @param $original_title
     *
     * @return string
     * @since 1.2.0
     */
    protected function clean_title( $original_title ) {
        
        if ( $original_title === null ) {
            return null;
        }
        
        $new_title                      = $original_title;
        $titleHadHierarchicalSeparators = false;
        
        /*
         * If there's a separator in the title, first remove the final part
         *
         * Sanity warning: if you eval this match in PHPStorm's "Evaluate expression" box, it will return false
         * I can assure you it works properly if you let the code run.
         */
        if ( preg_match( '/ [\|\-\\\\\/>»] /i', $new_title ) ) {
            $titleHadHierarchicalSeparators = (bool) preg_match( '/ [\\\\\/>»] /', $new_title );
            $new_title                      = preg_replace( '/(.*)[\|\-\\\\\/>»] .*/i', '$1', $original_title );
            // If the resulting title is too short (3 words or fewer), remove
            // the first part instead:
            if ( count( preg_split( '/\s+/', $new_title ) ) < 3 ) {
                $new_title = preg_replace( '/[^\|\-\\\\\/>»]*[\|\-\\\\\/>»](.*)/i', '$1', $original_title );
            }
        } elseif ( strpos( $new_title, ': ' ) !== false ) {
            if ( count( preg_split( '/\s+/', $new_title ) ) < 3 ) {
                $new_title = substr( $original_title, strpos( $original_title, ':' ) + 1 );
            }
        }
        
        $new_title = trim( $new_title );
        
        /*
         * If we now have 4 words or fewer as our title, and either no
         * 'hierarchical' separators (\, /, > or ») were found in the original
         * title or we decreased the number of words by more than 1 word, use
         * the original title.
         */
        $new_titleWordCount      = count( preg_split( '/\s+/', $new_title ) );
        $original_titleWordCount = count( preg_split( '/\s+/', preg_replace( '/[\|\-\\\\\/>»]+/', '', $original_title ) ) ) - 1;
        
        if ( $new_titleWordCount <= 4
             && ( ! $titleHadHierarchicalSeparators || $new_titleWordCount !== $original_titleWordCount )
        ) {
            $new_title = $original_title;
        }
        
        return $new_title;
    }
    
    /**
     * Convert link to absolute url
     *
     * @param $uri
     *
     * @return string
     * @since 1.2.0
     */
    protected function to_absolute_uri( $uri, $url ) {
        $scheme   = ! empty( parse_url( $url, PHP_URL_SCHEME ) ) ? parse_url( $url, PHP_URL_SCHEME ) : 'http';
        $pathBase = $scheme . '://' . parse_url( $url, PHP_URL_HOST ) . dirname( parse_url( $url, PHP_URL_PATH ) ) . '/';
        $prePath  = $scheme . '://' . parse_url( $pathBase, PHP_URL_HOST );
        
        
        // If this is already an absolute URI, return it.
        if ( preg_match( '/^[a-zA-Z][a-zA-Z0-9\+\-\.]*:/', $uri ) ) {
            return $uri;
        }
        
        // Scheme-rooted relative URI.
        if ( substr( $uri, 0, 2 ) === '//' ) {
            return $scheme . '://' . substr( $uri, 2 );
        }
        
        // Prepath-rooted relative URI.
        if ( substr( $uri, 0, 1 ) === '/' ) {
            return trailingslashit( $prePath ) . ltrim( $uri, '/' );
        }
        
        // Dotslash relative URI.
        if ( strpos( $uri, './' ) === 0 ) {
            return $pathBase . substr( $uri, 2 );
        }
        // Ignore hash URIs:
        if ( substr( $uri, 0, 1 ) === '#' ) {
            return $uri;
        }
        
        // Standard relative URI; add entire path. pathBase already includes a
        // trailing "/".
        return $pathBase . $uri;
    }
    
    
    public function get_title() {
        return $this->title;
    }
    
    public function get_content() {
        return $this->content;
    }
    
    public function get_image() {
        return $this->image;
    }
    
    public function get_excerpt() {
        return $this->excerpt;
    }
    
    public function get_author() {
        return $this->author;
    }
    
    public function get_language() {
        return $this->language;
    }
    
    public function get_pub_date() {
        return $this->pub_date;
    }
}
