<?php
defined( 'ABSPATH' ) || exit();

$blocks   = array(
	array(
		'image'       => WPCP_ASSETS_URL . '/images/help/docs.svg',
		'title'       => __( 'Looking for something?', 'wp-content-pilot' ),
		'desc'        => __( 'We have detailed documentation on every aspects of Content Pilot.', 'wp-content-pilot' ),
		'url'         => 'https://wpcontentpilot.com/docs/',
		'button_text' => __( 'Documentation', 'wp-content-pilot' ),
	),
	array(
		'image'       => WPCP_ASSETS_URL . '/images/help/support.svg',
		'title'       => __( 'Need any assistance?', 'wp-content-pilot' ),
		'desc'        => __( 'Our expert support team is always ready to help you out.', 'wp-content-pilot' ),
		'url'         => 'https://wpcontentpilot.com/support/',
		'button_text' => __( 'Contact support', 'wp-content-pilot' ),
	),
	array(
		'image'       => WPCP_ASSETS_URL . '/images/help/bugs.svg',
		'title'       => __( 'Found a bug?', 'wp-content-pilot' ),
		'desc'        => __( 'Report any bug that you discovered, get instant solutions.', 'wp-content-pilot' ),
		'url'         => 'https://github.com/pluginever/wp-content-pilot',
		'button_text' => __( 'Report to github', 'wp-content-pilot' ),
	),
	array(
		'image'       => WPCP_ASSETS_URL . '/images/help/customization.svg',
		'title'       => __( 'Require customization?', 'wp-content-pilot' ),
		'desc'        => __( 'We would love to hear your integration and customization ideas.', 'wp-content-pilot' ),
		'url'         => 'https://wpcontentpilot.com/support/',
		'button_text' => __( 'Contact us', 'wp-content-pilot' ),
	),
	array(
		'image'       => WPCP_ASSETS_URL . '/images/help/like.svg',
		'title'       => __( 'Like the plugin?', 'wp-content-pilot' ),
		'desc'        => __( 'Your review is very important to us. It takes a minute and helps a lot. Thanks in advance!', 'wp-content-pilot' ),
		'url'         => 'https://wordpress.org/support/plugin/wp-content-pilot/reviews/?rate=5#new-post',
		'button_text' => __( 'Leave a review', 'wp-content-pilot' ),
	),
);
$features = array(
	array(
		'title' => __( 'Article', 'wp-content-pilot' ),
		'desc'  => __( 'Article module to auto import articles from web', 'wp-content-pilot' ),
		'free'  => true,
		'pro'   => true,
	),
	array(
		'title' => __( 'Envato', 'wp-content-pilot' ),
		'desc'  => __( 'Envato modules to import envato products and affiliation', 'wp-content-pilot' ),
		'free'  => true,
		'pro'   => true,
	),
	array(
		'title' => __( 'Youtube', 'wp-content-pilot' ),
		'desc'  => __( 'Youtube module to import youtube videos', 'wp-content-pilot' ),
		'free'  => true,
		'pro'   => true,
	),
	array(
		'title' => __( 'Feed', 'wp-content-pilot' ),
		'desc'  => __( 'RSS Feed module to import rss feeds', 'wp-content-pilot' ),
		'free'  => true,
		'pro'   => true,
	),
	array(
		'title' => __( 'Flickr', 'wp-content-pilot' ),
		'desc'  => __( 'Flickr module to import images', 'wp-content-pilot' ),
		'free'  => true,
		'pro'   => true,
	),
	array(
		'title' => __( 'Amazon', 'wp-content-pilot' ),
		'desc'  => __( 'Amazon module to import products & automatic affiliation', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'ClickBank', 'wp-content-pilot' ),
		'desc'  => __( 'ClickBank module to import products & automatic affiliation', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Ebay', 'wp-content-pilot' ),
		'desc'  => __( 'Ebay module to import products & automatic affiliation', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Facebook', 'wp-content-pilot' ),
		'desc'  => __( 'Facebook module to import Facebook posts, events', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Twitter', 'wp-content-pilot' ),
		'desc'  => __( 'Twitter module to import twitter posts', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Walmart', 'wp-content-pilot' ),
		'desc'  => __( 'Walmart module to import products & automatic affiliation', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Bestbuy', 'wp-content-pilot' ),
		'desc'  => __( 'Bestbuy module to import products & automatic affiliation', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Craiglist', 'wp-content-pilot' ),
		'desc'  => __( 'Craiglist module to import Craiglist listings', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Itunes', 'wp-content-pilot' ),
		'desc'  => __( 'Itunes module to import products & automatic affiliation', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'CareerJet', 'wp-content-pilot' ),
		'desc'  => __( 'CareerJet module to import jobs & automatic affiliation', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'EzineArticles', 'wp-content-pilot' ),
		'desc'  => __( 'EzineArticles module to import articles', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Reddit', 'wp-content-pilot' ),
		'desc'  => __( 'Reddit module to import articles', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'SoundCloud', 'wp-content-pilot' ),
		'desc'  => __( 'SoundCloud module to import audios', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Instagram', 'wp-content-pilot' ),
		'desc'  => __( 'Instagram module to import photos', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Pinterest', 'wp-content-pilot' ),
		'desc'  => __( 'Pinterest module to import photos', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Tiktok', 'wp-content-pilot' ),
		'desc'  => __( 'Tiktok module to import videos', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Search Replace', 'wp-content-pilot' ),
		'desc'  => __( 'Automatic search replace with regex support', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Custom Post Meta', 'wp-content-pilot' ),
		'desc'  => __( 'Insert custom post meta', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Readability', 'wp-content-pilot' ),
		'desc'  => __( 'Readability score controlled posts for clean articles', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Keyword Suggestion', 'wp-content-pilot' ),
		'desc'  => __( 'A tool to find a good keyword', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),
	array(
		'title' => __( 'Translation Support', 'wp-content-pilot' ),
		'desc'  => __( 'Translate articles in other language', 'wp-content-pilot' ),
		'free'  => false,
		'pro'   => true,
	),

);
?>
<style>
	.wpcp-help-page {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		margin: -15px;
	}

	.wpcp-help-page * {
		box-sizing: border-box;
	}

	.wpcp-help-page .help-block-wrap {
		flex: 0 0 20%;
		max-width: 20%;
		align-self: flex-start;
		padding: 15px;
	}

	.wpcp-help-page .help-block {
		border: 1px solid #ddd;
		border-radius: 3px;
		padding: 25px 15px;
		text-align: center;
		background: #fff;
	}

	.wpcp-help-page .help-block img {
		max-height: 70px;
	}

	.free-vs-pro h3 a {
		text-decoration: none;
		color: #ff7a03;
	}
	.free-vs-pro table {
		background-color: #fff;
		border: 1px solid #DDDDDD;
		border-radius: 3px;
		max-width: 700px;
	}

	.free-vs-pro table tr {
		padding: 0;
	}

	.free-vs-pro table tr:nth-child(even) {
		background-color: #f1f1f1;
	}

	.free-vs-pro table th {
		padding: 30px;
		border-left: 1px solid #DDDDDD;
		font-weight: 500;
		font-size: 18px;
	}

	.free-vs-pro table th + th,
	.free-vs-pro table td + td {
		text-align: center;
	}

	.free-vs-pro table td {
		padding: 10px 30px;
		border-left: 1px solid #DDDDDD;
		vertical-align: middle;
	}

	.free-vs-pro table th:first-child,
	.free-vs-pro table td:first-child {
		border-left: none;
	}

	.free-vs-pro table td strong {
		font-size: 16px;
	}

	.free-vs-pro table td p {
		margin-top: 5px;
	}

	.free-vs-pro table td .dashicons-yes {
		color: #1BAB0B;
	}

	.free-vs-pro table td .dashicons-no-alt {
		color: #EF2727;
	}

	.free-vs-pro .button-pro {
		background-color: #ff7a03;
		color: #fff;
		border-color: #ca5f00;
		height: auto;
		padding: 5px 20px;
		font-size: 16px;
	}

	.free-vs-pro .button-pro:hover {
		color: #fff;
		border-color: #ca5f00;
		background-color: #ff8518;
	}
</style>
<div class="wrap">

	<h2><?php esc_html_e( 'Content Pilot - Help', 'wp-content-pilot' ); ?> </h2>

	<div class="wpcp-help-page">
		<?php foreach ( $blocks as $block ) : ?>
			<div class="help-block-wrap">
				<div class="help-block">
					<img src="<?php echo esc_url_raw( $block['image'] ); ?>" alt="Looking for Something?">
					<h3><?php echo esc_html( $block['title'] ); ?></h3>
					<p><?php echo esc_html( $block['desc'] ); ?></p>
					<a target="_blank" href="<?php echo esc_url_raw( $block['url'] ); ?>" class="button button-primary">
						<?php echo esc_html( $block['button_text'] ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( ! defined( 'WPCP_PRO_VERSION' ) ) : ?>
		<div class="free-vs-pro">
			<h3>Are you looking for more? Checkout <a href="https://wpcontentpilot.com/" target="_blank"> Content Pilot Pro. </a> </h3>
			<table class="widefat">
				<tr>
					<th><?php esc_html_e( 'Features', 'wp-content-pilot' ); ?></th>
					<th><?php esc_html_e( 'Free', 'wp-content-pilot' ); ?></th>
					<th><?php esc_html_e( 'Pro', 'wp-content-pilot' ); ?></th>
				</tr>
				<?php foreach ( $features as $feature ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $feature['title'] ); ?></strong>
							<p><?php echo esc_html( $feature['desc'] ); ?></p>
						</td>
						<td>
							<?php if ( isset( $feature['free'] ) && $feature['free'] ) : ?>
								<span class="dashicons dashicons-yes"></span>
							<?php else : ?>
								<span class="dashicons dashicons-no-alt"></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( isset( $feature['pro'] ) && $feature['pro'] ) : ?>
								<span class="dashicons dashicons-yes"></span>
							<?php else : ?>
								<span class="dashicons dashicons-no-alt"></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td></td>
					<td></td>
					<td>
						<a href="https://wpcontentpilot.com/?utm_source=comparision-chart&utm_medium=button&utm_campaign=content-pilot&utm_content=Go%20Pro" class="button button-pro" target="_blank">Get Pro</a>
					</td>
				</tr>
			</table>
		</div>
	<?php endif; ?>
</div>

