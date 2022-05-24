<?php
$modules = apply_filters( 'wpcp_campaign_modules_list', array(
	array(
		'label'    => 'Article',
		'name'     => 'article',
		'keywords' => 'article, articles, blog, posts, news',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/article.png' )
	),
	array(
		'label'    => 'Feed',
		'name'     => 'feed',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/feed.png' )
	),
	array(
		'label'    => 'Youtube',
		'name'     => 'youtube',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/youtube.png' )
	),
	array(
		'label'    => 'Flickr',
		'name'     => 'flickr',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/flickr.png' )
	),
	array(
		'label'    => 'Envato',
		'name'     => 'envato',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/envato.png' )
	),
	array(
		'label'    => 'Amazon',
		'name'     => 'amazon',
		'keywords' => 'bestbuy, affiliation, affiliate',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/amazon.png' )
	),
	array(
		'label'    => 'BestBuy',
		'name'     => 'bestBuy',
		'keywords' => 'bestbuy, affiliation, affiliate',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/bestbuy.png' )
	),
	array(
		'label'    => 'CareerJet',
		'name'     => 'careerjet',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/careerjet.png' )
	),
	array(
		'label'    => 'ClickBank',
		'name'     => 'clickBank',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/clickbank.png' )
	),
	array(
		'label'    => 'Craigslist',
		'name'     => 'craigslist',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/craigslist.png' )
	),
	array(
		'label'    => 'Ebay',
		'name'     => 'ebay',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/ebay.png' )
	),
	array(
		'label'    => 'Ezine Articles',
		'name'     => 'ezine-articles',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/ezine.png' )
	),
	array(
		'label'    => 'FaceBook',
		'name'     => 'faceBook',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/facebook.png' )
	),
	array(
		'label'    => 'Instagram',
		'name'     => 'instagram',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/instragram.png' )
	),
	array(
		'label'    => 'Itunes',
		'name'     => 'itunes',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/itunes.png' )
	),
	array(
		'label'    => 'Pinterest',
		'name'     => 'pinterest',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/pinterest.png' )
	),
//	array(
//		'label'    => 'Quora',
//		'name'     => 'quora',
//		'keywords' => 'job',
//		'disabled' => true,
//		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/quora.png' )
//	),
	array(
		'label'    => 'Reddit',
		'name'     => 'reddit',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/reddit.png' )
	),
	array(
		'label'    => 'Twitter',
		'name'     => 'twitter',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/twitter.png' )
	),
	array(
		'name'     => 'SoundCloud',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/soundcloud.png' )
	),
	array(
		'label'    => 'Walmart',
		'name'     => 'walmart',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/walmart.png' )
	),
	array(
		'label'    => 'Gearbest',
		'name'     => 'gearbest',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/gearbest.png' )
	),
	array(
		'label'    => 'Vimeo',
		'name'     => 'vimeo',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/vimeo.png' )
	),
	array(
		'label'    => 'Yelp',
		'name'     => 'yelp',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/yelp.png' )
	),
	array(
		'label'    => 'Etsy',
		'name'     => 'etsy',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/etsy.png' )
	),
	array(
		'label'    => 'Daily Motion',
		'name'     => 'dailymotion',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/dailymotion.png' ),

	),
	array(
		'label'    => 'Tiktok',
		'name'     => 'tiktok',
		'keywords' => 'tiktok,video',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/tiktok.png' ),
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
               value="<?php _e( 'Start Campaign', 'wp-content-pilot' ); ?>">
    </p>
</div>
