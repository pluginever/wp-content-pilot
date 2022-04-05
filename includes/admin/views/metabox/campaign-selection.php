<?php
$modules = apply_filters( 'wpcp_campaign_modules_list', array(
	array(
		'label'    => 'Article',
		'name'     => 'article',
		'keywords' => 'article, articles, blog, posts, news',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/article.png' )
	),
	array(
		'label'    => 'Feed',
		'name'     => 'feed',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/dist/images/feed.png' )
	),
	array(
		'label'    => 'Youtube',
		'name'     => 'youtube',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/youtube.png' )
	),
	array(
		'label'    => 'Flickr',
		'name'     => 'flickr',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/flickr.png' )
	),
	array(
		'label'    => 'Envato',
		'name'     => 'envato',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/envato.png' )
	),
	array(
		'label'    => 'Amazon',
		'name'     => 'amazon',
		'keywords' => 'bestbuy, affiliation, affiliate',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/amazon.png' )
	),
	array(
		'label'    => 'BestBuy',
		'name'     => 'bestBuy',
		'keywords' => 'bestbuy, affiliation, affiliate',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/bestbuy.png' )
	),
	array(
		'label'    => 'CareerJet',
		'name'     => 'careerjet',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/careerjet.png' )
	),
	array(
		'label'    => 'ClickBank',
		'name'     => 'clickBank',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/clickbank.png' )
	),
	array(
		'label'    => 'Craigslist',
		'name'     => 'craigslist',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/craigslist.png' )
	),
	array(
		'label'    => 'Ebay',
		'name'     => 'ebay',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/ebay.png' )
	),
	array(
		'label'    => 'Ezine Articles',
		'name'     => 'ezine-articles',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/ezine.png' )
	),
	array(
		'label'    => 'FaceBook',
		'name'     => 'faceBook',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/facebook.png' )
	),
	array(
		'label'    => 'Instagram',
		'name'     => 'instagram',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/instragram.png' )
	),
	array(
		'label'    => 'Itunes',
		'name'     => 'itunes',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/itunes.png' )
	),
	array(
		'label'    => 'Pinterest',
		'name'     => 'pinterest',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/pinterest.png' )
	),
//	array(
//		'label'    => 'Quora',
//		'name'     => 'quora',
//		'keywords' => 'job',
//		'disabled' => true,
//		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/quora.png' )
//	),
	array(
		'label'    => 'Reddit',
		'name'     => 'reddit',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/reddit.png' )
	),
	array(
		'label'    => 'Twitter',
		'name'     => 'twitter',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/twitter.png' )
	),
	array(
		'name'     => 'SoundCloud',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/soundcloud.png' )
	),
	array(
		'label'    => 'Walmart',
		'name'     => 'walmart',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/walmart.png' )
	),
	array(
		'label'    => 'Gearbest',
		'name'     => 'gearbest',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/gearbest.png' )
	),
	array(
		'label'    => 'Vimeo',
		'name'     => 'vimeo',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/vimeo.png' )
	),
	array(
		'label'    => 'Yelp',
		'name'     => 'yelp',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/yelp.png' )
	),
	array(
		'label'    => 'Eventful',
		'name'     => 'eventful',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/eventful.png' )
	),
	array(
		'label'    => 'Etsy',
		'name'     => 'etsy',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/etsy.png' )
	),
	array(
		'label'    => 'Daily Motion',
		'name'     => 'dailymotion',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/dailymotion.png' ),

	),
	array(
		'label'    => 'Tiktok',
		'name'     => 'tiktok',
		'keywords' => 'tiktok,video',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/dist/images/tiktok.png' ),
	)
) );
?>
<style>
    #wpcp-campaign-actions, #side-sortables, #post-body-content {
        display: none;
    }

    #campaign-selection {
        overflow: hidden;
        padding-bottom: 30px;
    }
</style>
<div class="wpcp-choose-campaign-wrap">
    <div class="wpcp-choose-campaign-items">
		<?php
		foreach ( $modules as $module ) {
			$slug       = sanitize_title( $module['name'] );
			$disabled   = $module['disabled'] == true ? " disabled='disabled' " : '';
			$input      = sprintf( '<input type="radio" name="_campaign_type" id="%1$s" value="%2$s" %3$s required="required"/>', $slug, $slug, $disabled );
			$image_link = $module['image'];
			$label      = sprintf( '<label for="%1$s"><img src="%2$s" alt="%3$s-module"></label>', $slug, $image_link, $module['name'] );
			$wrap_class = $module['disabled'] == true ? 'disabled-item' : '';
			echo sprintf( '<div class="wpcp-choose-campaign-item ' . $wrap_class . '">' . $input . $label . '</div>' );
		}
		?>
    </div>
    <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary button-large"
               value="<?php _e( 'Submit', 'wp-content-pilot' ); ?>">
    </p>
</div>
