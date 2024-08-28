<?php
/**
 * Campaign settings
 *
 * @since 3.0.0
 * @package WPContentPilot\Admin\Views
 *
 * @var Campaign $campaign Campaign object.
 */

use WPContentPilot\Campaign;

defined( 'ABSPATH' ) || exit;

$tabs = apply_filters( 'wpcp_campaign_settings_tabs', [
	'general'        => array(
		'label'    => __( 'General', 'wp-content-pilot' ),
		'target'   => 'campaign_options_general',
		'priority' => 10,
	),
	'template'       => array(
		'label'    => __( 'Template', 'wp-content-pilot' ),
		'target'   => 'campaign_options_template',
		'priority' => 20,
	),
	'attributes'     => array(
		'label'    => __( 'Attributes', 'wp-content-pilot' ),
		'target'   => 'campaign_options_attributes',
		'priority' => 25,
	),
	'images'         => array(
		'label'    => __( 'Images', 'wp-content-pilot' ),
		'target'   => 'campaign_options_images',
		'priority' => 30,
	),
	'taxonomies'     => array(
		'label'    => __( 'Taxonomies', 'wp-content-pilot' ),
		'target'   => 'campaign_options_taxonomies',
		'priority' => 40,
	),
	'rewriting'      => array(
		'label'    => __( 'Rewriting', 'wp-content-pilot' ),
		'target'   => 'campaign_options_rewriting',
		'priority' => 45,
	),
	'filters'        => array(
		'label'    => __( 'Filters', 'wp-content-pilot' ),
		'target'   => 'campaign_options_filters',
		'priority' => 50,
	),
	'search_replace' => array(
		'label'    => __( 'Search & Replace', 'wp-content-pilot' ),
		'target'   => 'campaign_options_search_replace',
		'priority' => 55,
	),
	'links'          => array(
		'label'    => __( 'Links', 'wp-content-pilot' ),
		'target'   => 'campaign_options_links',
		'priority' => 60,
	),
	'misc'           => array(
		'label'    => __( 'Miscellaneous', 'wp-content-pilot' ),
		'target'   => 'campaign_options_misc',
		'priority' => PHP_INT_MAX,
	),
] );

?>
<div class="wpcp-campaign-settings-tabs">
	<ul>
		<?php foreach ( $tabs as $tab_id => $tab ) : ?>
			<li class="wpcp-campaign-settings-tab" data-target="<?php echo esc_attr( $tab['target'] ); ?>">
				<a href="#<?php echo esc_attr( $tab['target'] ); ?>">
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<div class="wpcp-campaign-settings-panels">
	<?php foreach ( $tabs as $tab_id => $tab ) : ?>
		<div id="<?php echo esc_attr( $tab['target'] ); ?>" class="wpcp-campaign-settings-panel" style="display: none;">
			<?php

			if ( ! empty( $campaign->type ) ) {
				/**
				 * Fires after the campaign settings panel is rendered.
				 *
				 * @param Campaign $campaign Campaign object.
				 *
				 * @since 3.0.0
				 */
				do_action( 'wpcp_campaign_' . $tab_id . '_settings_panel_' . $campaign->type, null );
			}

			/**
			 * Fires after the campaign settings panel is rendered.
			 *
			 * @param Campaign $campaign Campaign object.
			 *
			 * @since 3.0.0
			 */
			do_action( 'wpcp_campaign_' . $tab_id . '_settings_panel', null );
			?>

		</div>
	<?php endforeach; ?>
</div>
