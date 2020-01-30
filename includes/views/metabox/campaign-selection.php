<?php
$modules = apply_filters( 'wpcp_campaign_modules_list', array(
	array(
		'name'     => 'Article',
		'keywords' => 'article, articles, blog, posts, news',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/article.png' )
	),
	array(
		'name'     => 'Feed',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/feed.png' )
	),
	array(
		'name'     => 'youtube',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/youtube.png' )
	),
	array(
		'name'     => 'Flickr',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/flickr.png' )
	),
	array(
		'name'     => 'Envato',
		'keywords' => 'job',
		'disabled' => false,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/envato.png' )
	),
	array(
		'name'     => 'BestBuy',
		'keywords' => 'bestbuy, affiliation, affiliate',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/bestbuy.png' )
	),
	array(
		'name'     => 'CareerJet',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/careerjet.png' )
	),
	array(
		'name'     => 'ClickBank',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/clickbank.png' )
	),
	array(
		'name'     => 'Craigslist',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/craigslist.png' )
	),
	array(
		'name'     => 'Ebay',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/ebay.png' )
	),
	array(
		'name'     => 'Ezine Articles',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/ezine.png' )
	),
	array(
		'name'     => 'FaceBook',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/facebook.png' )
	),
	array(
		'name'     => 'Instragram',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/instragram.png' )
	),
	array(
		'name'     => 'Itunes',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/itunes.png' )
	),
	array(
		'name'     => 'Pinterest',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/pinterest.png' )
	),
	array(
		'name'     => 'Quora',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/quora.png' )
	),
	array(
		'name'     => 'Reddit',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/reddit.png' )
	),
	array(
		'name'     => 'Twitter',
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
		'name'     => 'Walmart',
		'keywords' => 'job',
		'disabled' => true,
		'image'    => esc_url( WPCP_ASSETS_URL . '/images/modules/walmart.png' )
	)
) );
?>
<style>
	#wpcp-campaign-actions, #side-sortables {
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
