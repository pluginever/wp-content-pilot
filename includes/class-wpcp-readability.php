<?php
defined( 'ABSPATH' ) || die();

/**
 * Class WPCP_Readability.
 *
 * @since 1.0.0
 */
class WPCP_Readability {

	/**
	 * URL.
	 *
	 * @var string $url URL.
	 *
	 * @since 1.0.0
	 */
	protected $url = '';

	/**
	 * The title.
	 *
	 * @var string $title The title.
	 *
	 * @since 1.0.0
	 */
	protected $title = '';

	/**
	 * Content.
	 *
	 * @var string $content Content.
	 *
	 * @since 1.0.0
	 */
	protected $content = '';

	/**
	 * Excerpt.
	 *
	 * @var string $excerpt Excerpt.
	 *
	 * @since 1.0.0
	 */
	protected $excerpt = '';

	/**
	 * Image.
	 *
	 * @var string $image Image.
	 *
	 * @since 1.0.0
	 */
	protected $image = '';

	/**
	 * Author.
	 *
	 * @var string $author Author.
	 *
	 * @since 1.0.0
	 */
	protected $author = '';

	/**
	 * Language.
	 *
	 * @var string $categories Language.
	 *
	 * @since 1.0.0
	 */
	protected $language = '';

	/**
	 * Publish date.
	 *
	 * @var string $pub_date Publish date.
	 *
	 * @since 1.0.0
	 */
	protected $pub_date = '';

	/**
	 * Tags.
	 *
	 * @var string|mixed $tags Tags.
	 *
	 * @since 1.0.0
	 */
	protected $tags = '';

	/**
	 * Categories.
	 *
	 * @var string|mixed $categories Categories.
	 *
	 * @since 1.0.0
	 */
	protected $categories = '';

	/**
	 * Meta description.
	 *
	 * @var string $meta_description Meta description.
	 *
	 * @since 1.0.0
	 */
	protected $meta_description = '';

	/**
	 * WPCP_Readability constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {}

	/**
	 * Parse HTML.
	 *
	 * @param string $html HTML text.
	 * @param string $url URL.
	 *
	 * @since 1.2.0
	 * @return WP_Error
	 */
	public function parse( $html, $url ) {
		// Set properties.
		$this->url = esc_url( $url );

		// Process full document.
		$html = $this->pre_process_html( $html, $this->url );

		$readability                          = new Readability( $html, $this->url );
		$readability->debug                   = false;
		$readability->convertLinksToFootnotes = false;
		$result                               = $readability->init();

		if ( ! $result ) {
			wpcp_logger()->error( sprintf( /* translators: URL. */ __( 'Not readable [%s]', 'wp-content-pilot' ), $this->url ) );

			return new WP_Error( 'readability-error', __( 'Content is not readable', 'wp-content-pilot' ) );
		}

		$title = $readability->getTitle()->textContent;
		// TODO: We need to fix the property not found issue.
		$content = wpcp_remove_empty_tags_recursive( $readability->getContent()->innerHTML );

		$content = wpcp_remove_emoji( $content );
		$content = force_balance_tags( $content );
		$content = wpcp_remove_unauthorized_html( $content );
		$content = wpcp_remove_continue_reading( $content );

		// Final content.
		$this->content = $content;

		// Parse meta.
		$metas = $this->parse_meta( $html );

		// Title.
		if ( array_key_exists( 'og:title', $metas ) ) {
			$title = $metas['og:title'];
		} elseif ( array_key_exists( 'twitter:title', $metas ) ) {
			$this->excerpt = $metas['twitter:title'];
		}
		$this->title = $this->clean_title( $title );

		// Set image.
		if ( array_key_exists( 'og:image', $metas ) ) {
			$this->image = esc_url( $metas['og:image'] );
		} elseif ( array_key_exists( 'twitter:image', $metas ) ) {
			$this->image = esc_url( $metas['twitter:image'] );
		} else {
			$content_dom = wpcp_str_get_html( $content );
			$img         = $content_dom->find( 'img', 0 );
			if ( $img && $img->getAttribute( 'src' ) ) {
				$this->image = $img->getAttribute( 'src' );
			}
		}

		// Excerpt.
		if ( array_key_exists( 'description', $metas ) ) {
			$this->excerpt = $metas['description'];
		} elseif ( array_key_exists( 'og:description', $metas ) ) {
			$this->excerpt = $metas['og:description'];
		} elseif ( array_key_exists( 'twitter:description', $metas ) ) {
			$this->excerpt = $metas['twitter:description'];
		}

		// Language.
		if ( array_key_exists( 'og:locale', $metas ) ) {
			$this->language = $metas['og:locale'];
		}

		// Author.
		if ( array_key_exists( 'author', $metas ) ) {
			$this->author = $metas['author'];
		}
	}

	/**
	 * Process HTML.
	 *
	 * @param string $document document contents.
	 * @param string $url URL.
	 *
	 * @since 1.2.0
	 * @return mixed
	 */
	protected function pre_process_html( $document, $url ) {
		if ( ! $document instanceof simple_html_dom ) {
			$document = wpcp_str_get_html( $document );
		}
		// Fix image link.
		/* @var object $img simple_html_dom_node */
		foreach ( $document->find( 'img' ) as $img ) {
			$urls = array(
				$img->getAttribute( 'src' ),
				$img->getAttribute( 'srcset' ),
				$img->getAttribute( 'data-src' ),
				$img->getAttribute( 'data-original' ),
				$img->getAttribute( 'data-orig' ),
				$img->getAttribute( 'data-url' ),
			);

			$src = array_filter( $urls );
			$src = reset( $src );
			$img->setAttribute( 'src', $this->to_absolute_uri( $src, $url ) );
			$img->removeAttribute( 'class' );
			if ( empty( $img->getAttribute( 'src' ) ) ) {
				$img->remove();
			}
		}

		// Fix relative url.
		foreach ( $document->find( 'a' ) as $a ) {
			/* @var object $a simple_html_dom_node. */
			$a->setAttribute( 'href', $this->to_absolute_uri( $a->getAttribute( 'href' ), $url ) );
		}

		return $document->outertext;
	}

	/**
	 * Parse meta.
	 *
	 * @param string $html HTML string.
	 *
	 * @since 1.2.0
	 * @return array
	 */
	protected function parse_meta( $html ) {
		$values = array();
		// Match "description", or Twitter's "twitter:description" (Cards) in name attribute.
		$name_pattern = '/^\s*((twitter)\s*:\s*)?(description|title|image|author)\s*$/i';

		// Match Facebook's Open Graph title & description properties.
		$property_pattern = '/^\s*og\s*:\s*(description|title|image|author)\s*$/i';
		$dom              = wpcp_str_get_html( $html );

		foreach ( $dom->find( 'meta' ) as $meta ) {
			/* @var object $meta simple_html_dom_node. */
			$element_name     = $meta->getAttribute( 'name' );
			$element_property = $meta->getAttribute( 'property' );

			$name = null;
			if ( preg_match( $name_pattern, $element_name ) ) {
				$name = $element_name;
			} elseif ( preg_match( $property_pattern, $element_property ) ) {
				$name = $element_property;
			} elseif ( ! empty( $element_property ) ) {
				$name = $element_property;
			}
			if ( $name ) {
				$content = $meta->getAttribute( 'content' );
				if ( $content ) {
					// Convert to lowercase and remove any whitespace so we can match below.
					$name            = preg_replace( '/\s/', '', strtolower( $name ) );
					$values[ $name ] = trim( $content );
				}
			}
		}

		return $values;
	}

	/**
	 * Clean title text.
	 *
	 * @param string $original_title The title.
	 *
	 * @since 1.2.0
	 * @return string
	 */
	protected function clean_title( $original_title ) {

		if ( null === $original_title ) {
			return null;
		}

		$new_title                         = $original_title;
		$title_had_hierarchical_separators = false;

		/*
		 * If there's a separator in the title, first remove the final part
		 *
		 * Sanity warning: if you eval this match in PHPStorm's "Evaluate expression" box, it will return false
		 * I can assure you it works properly if you let the code run.
		 */
		if ( preg_match( '/ [\|\-\\\\\/>»] /i', $new_title ) ) {
			$title_had_hierarchical_separators = (bool) preg_match( '/ [\\\\\/>»] /', $new_title );
			$new_title                         = preg_replace( '/(.*)[\|\-\\\\\/>»] .*/i', '$1', $original_title );
			// If the resulting title is too short (3 words or fewer), remove the first part instead.
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
		$new_title_word_count      = count( preg_split( '/\s+/', $new_title ) );
		$original_title_word_count = count( preg_split( '/\s+/', preg_replace( '/[\|\-\\\\\/>»]+/', '', $original_title ) ) ) - 1;

		if ( $new_title_word_count <= 4
			&& ( ! $title_had_hierarchical_separators || $new_title_word_count !== $original_title_word_count )
		) {
			$new_title = $original_title;
		}

		return $new_title;
	}

	/**
	 * Convert link to absolute url.
	 *
	 * @param string $uri URI.
	 * @param string $url URL.
	 *
	 * @since 1.2.0
	 * @return string
	 */
	protected function to_absolute_uri( $uri, $url ) {
		$scheme    = ! empty( wp_parse_url( $url, PHP_URL_SCHEME ) ) ? wp_parse_url( $url, PHP_URL_SCHEME ) : 'http';
		$path_base = $scheme . '://' . wp_parse_url( $url, PHP_URL_HOST ) . dirname( wp_parse_url( $url, PHP_URL_PATH ) ) . '/';
		$pre_path  = $scheme . '://' . wp_parse_url( $path_base, PHP_URL_HOST );

		// If this is already an absolute URI, return it.
		if ( preg_match( '/^[a-zA-Z][a-zA-Z0-9\+\-\.]*:/', $uri ) ) {
			return $uri;
		}

		// Scheme-rooted relative URI.
		if ( '//' === substr( $uri, 0, 2 ) ) {
			return $scheme . '://' . substr( $uri, 2 );
		}

		// Prepath-rooted relative URI.
		if ( '/' === substr( $uri, 0, 1 ) ) {
			return trailingslashit( $pre_path ) . ltrim( $uri, '/' );
		}

		// Dotslash relative URI.
		if ( 0 === strpos( $uri, './' ) ) {
			return $path_base . substr( $uri, 2 );
		}
		// Ignore hash URIs.
		if ( '#' === substr( $uri, 0, 1 ) ) {
			return $uri;
		}

		// Standard relative URI; add entire path. pathBase already includes a trailing "/".
		return $path_base . $uri;
	}

	/**
	 * Get the title.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the content.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Get image.
	 *
	 * @since 1.0.0
	 * @return string|mixed
	 */
	public function get_image() {
		return $this->image;
	}

	/**
	 * Get the post excerpt.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_excerpt() {
		return $this->excerpt;
	}

	/**
	 * Get the author.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_author() {
		return $this->author;
	}

	/**
	 * Get language.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_language() {
		return $this->language;
	}

	/**
	 * Get publish date.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_pub_date() {
		return $this->pub_date;
	}
}
