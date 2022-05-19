<?php
/**
 * Metabox Functions
 *
 * @package     WP Content Pilot
 * @subpackage  metabox
 * @copyright   Copyright (c) 2019, MD Sultan Nasir Uddin(manikdrmc@gmail.com)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

defined( 'ABSPATH' ) || exit();


/**
 * Register metabox
 *
 * @since 1.0.0
 */
function wpcp_register_meta_boxes( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	if ( empty( $campaign_type ) ) {
		add_meta_box( 'campaign-selection', __( 'Select a campaign to start', 'wp-content-pilot' ), 'wpcp_campaign_selection_metabox_callback', 'wp_content_pilot', 'normal', 'high' );

		return '';
	}
	add_meta_box( 'wpcp-campaign-status', __( 'Campaign Status', 'wp-content-pilot' ), 'wpcp_campaign_status_metabox_callback', 'wp_content_pilot', 'normal', 'high' );
	add_meta_box( 'wpcp-campaign-options', __( 'Campaign Options', 'wp-content-pilot' ), 'wpcp_campaign_options_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-campaign-log', __( 'Campaign Log', 'wp-content-pilot' ), 'wpcp_campaign_log_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-post-template', __( 'Post Template', 'wp-content-pilot' ), 'wpcp_post_template_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-post-settings', __( 'Post Settings', 'wp-content-pilot' ), 'wpcp_post_settings_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-post-filters', __( 'Posts Filter', 'wp-content-pilot' ), 'wpcp_posts_filter_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-advanced-settings', __( 'Advanced Settings', 'wp-content-pilot' ), 'wpcp_advanced_settings_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
	add_meta_box( 'wpcp-campaign-actions', __( 'Actions', 'wp-content-pilot' ), 'wpcp_campaign_action_metabox_callback', 'wp_content_pilot', 'side', 'high' );
	//add_meta_box( 'wpcp-campaign-details', __( 'Campaign Details', 'wp-content-pilot' ), 'wpcp_campaign_details_metabox_callback', 'wp_content_pilot', 'side', 'high' );
	add_meta_box( 'wpcp-campaign-misc', __( 'Miscellaneous', 'wp-content-pilot' ), 'wpcp_campaign_miscellaneous_metabox_callback', 'wp_content_pilot', 'side', 'high' );
	add_meta_box( 'wpcp-campaign-posts', __( 'Campaign Posts', 'wp-content-pilot' ), 'wpcp_campaign_posts_metabox_callback', 'wp_content_pilot', 'side', 'high' );
	add_meta_box( 'wpcp-spinner-settings', __( 'Spinner Settings', 'wp-content-pilot' ), 'wpcp_spinner_metabox_callback', 'wp_content_pilot', 'normal', 'low' );
}

add_action( 'add_meta_boxes_wp_content_pilot', 'wpcp_register_meta_boxes', 10 );

/**
 * Conditionally render metabox
 *
 * @param $post_type
 * @param $context
 * @param $post
 */

function wpcp_conditional_metabox_remove( $post_type, $context, $post ) {
	$post_type = 'wp_content_pilot';
	remove_meta_box( 'submitdiv', $post_type, 'side' );
	remove_meta_box( 'commentsdiv', $post_type, 'normal' );
	remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
	remove_meta_box( 'slugdiv', $post_type, 'normal' );
}

add_action( 'do_meta_boxes', 'wpcp_conditional_metabox_remove', 10, 3 );


/**
 * Campaign selection metabox
 *
 * @param $post
 */
function wpcp_campaign_selection_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/campaign-selection.php', array( 'post' => $post ) );
}

/**
 * Render campaign status
 *
 * @param $post
 */
function wpcp_campaign_status_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/campaign-status.php', array( 'post' => $post ) );
}

function wpcp_campaign_log_metabox_callback( $post ) {
	?>
	<div class="wpcp-campaign-log-list"></div>
	<?php
}

function wpcp_campaign_options_metabox_callback( $post ) {
	$campaign_type = get_post_meta( $post->ID, '_campaign_type', true );
	do_action( 'wpcp_' . sanitize_key( $campaign_type ) . '_campaign_options_meta_fields', $post );
	do_action( 'wpcp_campaign_options_meta_fields', $campaign_type, $post );
}
function wpcp_spinner_metabox_callback($post){
	wpcp_get_views('metabox/spinner-settings.php');
}
function wpcp_post_template_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/post-template.php' );
}

function wpcp_post_settings_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/post-settings.php' );
}

function wpcp_posts_filter_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/post-filter.php' );
}

function wpcp_advanced_settings_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/advanced-settings.php' );
	do_action( 'wpcp_advanced_settings_metabox', $post );
}

function wpcp_campaign_action_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/action-metabox.php' );
}

function wpcp_campaign_details_metabox_callback( $post ) {

}

function wpcp_campaign_miscellaneous_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/campaign-miscellaneous.php', [ 'campaign_id' => $post->ID ] );
}

function wpcp_campaign_posts_metabox_callback( $post ) {
	wpcp_get_views( 'metabox/campaign-posts.php', [ 'campaign_id' => $post->ID ] );
}

function wpcp_update_campaign_settings( $post_id ) {
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	//save post meta
	$posted = empty( $_POST ) ? [] : $_POST;
	if ( empty( $posted['_campaign_type'] ) ) {
		return false;
	}

	$frequency_unit = ! empty( $posted['_frequency_unit'] ) && in_array( $posted['_frequency_unit'], [
		'minutes',
		'hours',
		'days'
	] ) ? sanitize_key( $posted['_frequency_unit'] ) : 'hours';

	if ( $frequency_unit == 'minutes' ) {
		$every = MINUTE_IN_SECONDS;
	} elseif ( $frequency_unit == 'hours' ) {
		$every = HOUR_IN_SECONDS;
	} else {
		$every = DAY_IN_SECONDS;
	}


	$campaign_target    = empty( $posted['_campaign_target'] ) ? 10 : absint( $posted['_campaign_target'] );
	$campaign_frequency = empty( $posted['_campaign_frequency'] ) ? 5 : absint( $posted['_campaign_frequency'] );

	update_post_meta( $post_id, '_campaign_type', empty( $posted['_campaign_type'] ) ? 'feed' : sanitize_text_field( $posted['_campaign_type'] ) );
	update_post_meta( $post_id, '_campaign_target', $campaign_target );
	update_post_meta( $post_id, '_campaign_frequency', $campaign_frequency );
	update_post_meta( $post_id, '_frequency_unit', $frequency_unit );
	//update_post_meta( $post_id, '_run_every', ( $every * absint( $posted['_campaign_frequency'] ) ) );
	update_post_meta( $post_id, '_run_every', ( $every * $campaign_frequency ) );
	update_post_meta( $post_id, '_campaign_status', empty( $posted['_campaign_status'] ) ? 'inactive' : sanitize_text_field( $posted['_campaign_status'] ) );

	update_post_meta( $post_id, '_keywords', empty( $posted['_keywords'] ) ? '' : sanitize_text_field( $posted['_keywords'] ) );
	update_post_meta( $post_id, '_content_type', empty( $posted['_content_type'] ) ? 'html' : sanitize_text_field( $posted['_content_type'] ) );

	update_post_meta( $post_id, '_set_featured_image', empty( $posted['_set_featured_image'] ) ? '' : sanitize_text_field( $posted['_set_featured_image'] ) );
	update_post_meta( $post_id, '_remove_images', empty( $posted['_remove_images'] ) ? '' : sanitize_text_field( $posted['_remove_images'] ) );
	update_post_meta( $post_id, '_excerpt', empty( $posted['_excerpt'] ) ? '' : sanitize_text_field( $posted['_excerpt'] ) );
	update_post_meta( $post_id, '_strip_links', empty( $posted['_strip_links'] ) ? '' : sanitize_text_field( $posted['_strip_links'] ) );
	update_post_meta( $post_id, '_allow_comments', empty( $posted['_allow_comments'] ) ? '' : sanitize_text_field( $posted['_allow_comments'] ) );
	update_post_meta( $post_id, '_allow_pingbacks', empty( $posted['_allow_pingbacks'] ) ? '' : sanitize_text_field( $posted['_allow_pingbacks'] ) );
	update_post_meta( $post_id, '_use_original_date', empty( $posted['_use_original_date'] ) ? '' : sanitize_text_field( $posted['_use_original_date'] ) );
	update_post_meta( $post_id, '_skip_no_image', empty( $posted['_skip_no_image'] ) ? '' : sanitize_text_field( $posted['_skip_no_image'] ) );
	update_post_meta( $post_id, '_enable_duplicate_title', empty( $posted['_enable_duplicate_title'] ) ? '' : sanitize_text_field( $posted['_enable_duplicate_title'] ) );
	update_post_meta( $post_id, '_skip_duplicate_title', empty( $posted['_skip_duplicate_title'] ) ? '' : sanitize_text_field( $posted['_skip_duplicate_title'] ) );
	update_post_meta( $post_id, '_clean_title', empty( $posted['_clean_title'] ) ? '' : sanitize_text_field( $posted['_clean_title'] ) );

	update_post_meta( $post_id, '_spin_article', empty( $posted['_spin_article'] ) ? '' : sanitize_text_field( $posted['_spin_article'] ) );
	update_post_meta( $post_id, '_post_title', empty( $posted['_post_title'] ) ? '' : sanitize_text_field( $posted['_post_title'] ) );
	update_post_meta( $post_id, '_post_template', empty( $posted['_post_template'] ) ? '' : wp_kses_post( $posted['_post_template'] ) );
	update_post_meta( $post_id, '_post_type', empty( $posted['_post_type'] ) ? 'post' : wp_kses_post( $posted['_post_type'] ) );
	update_post_meta( $post_id, '_post_status', empty( $posted['_post_status'] ) ? 'publish' : wp_kses_post( $posted['_post_status'] ) );
	update_post_meta( $post_id, '_author', empty( $posted['_author'] ) ? '' : intval( $posted['_author'] ) );
	update_post_meta( $post_id, '_categories', empty( $posted['_categories'] ) ? '' : $posted['_categories'] );
	update_post_meta( $post_id, '_tags', empty( $posted['_tags'] ) ? '' : $posted['_tags'] );

	update_post_meta( $post_id, '_title_limit', empty( $posted['_title_limit'] ) ? '' : esc_attr( $posted['_title_limit'] ) );
	update_post_meta( $post_id, '_content_limit', empty( $posted['_content_limit'] ) ? '' : esc_attr( $posted['_content_limit'] ) );

	update_post_meta($post_id,'_spinner_action',empty($posted['_spinner_action']) ? 'unique_variation': sanitize_key($posted['_spinner_action']));
	update_post_meta($post_id,'_spinner_auto_protected_terms',empty($posted['_spinner_auto_protected_terms']) ? false: sanitize_key($posted['_spinner_auto_protected_terms']));
	update_post_meta($post_id,'_spinner_confidence_level',empty($posted['_spinner_confidence_level']) ? 'high': sanitize_key($posted['_spinner_confidence_level']));
	update_post_meta($post_id,'_spinner_nested_spintax',empty($posted['_spinner_nested_spintax']) ? true: sanitize_key($posted['_spinner_nested_spintax']));
	update_post_meta($post_id,'_spinner_auto_sentences',empty($posted['_spinner_auto_sentences']) ? false: sanitize_key($posted['_spinner_auto_sentences']));
	update_post_meta($post_id,'_spinner_auto_paragraphs',empty($posted['_spinner_auto_paragraphs']) ? false: sanitize_key($posted['_spinner_auto_paragraphs']));
	update_post_meta($post_id,'_spinner_auto_new_paragraphs',empty($posted['_spinner_auto_new_paragraphsv']) ? false: sanitize_key($posted['_spinner_auto_new_paragraphs']));
	update_post_meta($post_id,'_spinner_auto_sentence_trees',empty($posted['_spinner_auto_sentence_trees']) ? false: sanitize_key($posted['_spinner_auto_sentence_trees']));
	update_post_meta($post_id,'_spinner_use_only_synonyms',empty($posted['_spinner_use_only_synonyms']) ? false: sanitize_key($posted['_spinner_use_only_synonyms']));
	update_post_meta($post_id,'_spinner_reorder_paragraphs',empty($posted['_spinner_reorder_paragraphs']) ? false: sanitize_key($posted['_spinner_reorder_paragraphs']));

	update_post_meta($post_id,'_enable_polylang',empty($posted['_enable_polylang']) ? '': sanitize_text_field($posted['_enable_polylang']));
	update_post_meta($post_id,'_polylang_language_code',empty($posted['_polylang_language_code']) ? '': sanitize_text_field($posted['_polylang_language_code']));
	update_post_meta($post_id,'_not_save_featured_image',empty($posted['_not_save_featured_image']) ? '': sanitize_text_field($posted['_not_save_featured_image']));

	do_action( 'wpcp_update_campaign_settings', $post_id, $posted );
	do_action( 'wpcp_' . sanitize_key( $posted['_campaign_type'] ) . '_update_campaign_settings', $post_id, $posted );
}

add_action( 'save_post_wp_content_pilot', 'wpcp_update_campaign_settings' );


function wpcp_keyword_field() {
	echo WPCP_HTML::textarea_input( array(
		'label'       => __( 'Keywords', 'wp-content-pilot' ),
		'name'        => '_keywords',
		'placeholder' => 'Bonsai tree care',
		'desc'        => __( 'Separate keywords with commas.', 'wp-content-pilot' ),
		'attrs'       => array(
			'rows'     => 3,
			'required' => 'required'
		),
	) );
}

function wpcp_keyword_suggestion_field() {
	echo WPCP_HTML::text_input( array(
		'label'         => __( 'Keyword Suggestion', 'wp-content-pilot' ),
		'name'          => '_keyword_suggestion',
		'placeholder'   => 'Enter Keyword Here',
		'wrapper_class' => 'pro',
		'after'         => '<span id="wpcp-keyword-suggester"></span>',
		'attrs'         => array(
			'disabled' => 'disabled',
			'list'     => 'wpcp-keyword-suggester',
		)
	) );
}


function wpcp_strip_links_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'   => __( 'Strip original links from the post', 'wp-content-pilot' ),
		'name'    => '_strip_links',
		'tooltip' => __( 'Remove hyperlinks found in the article', 'wp-content-pilot' ),
	) );
}

function wpcp_external_link_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Make permalink link directly to the source', 'wp-content-pilot' ),
		'name'          => '_external_post',
		'tooltip'       => __( 'Make post link directly to the source site, posts will not load at your site.', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_canonical_link_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Add canonical tag with the original post link ', 'wp-content-pilot' ),
		'name'          => '_canonical_tag',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_featured_image_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label' => __( 'Set first image as featured image', 'wp-content-pilot' ),
		'name'  => '_set_featured_image',
	) );
}

function wpcp_remove_images() {
	echo WPCP_HTML::checkbox_input( array(
		'label' => __( 'Remove images from content', 'wp-content-pilot' ),
		'name'  => '_remove_images',
	) );
}

function wpcp_featured_image_random_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Set random featured image if no image exists', 'wp-content-pilot' ),
		'name'          => '_random_featured_image',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_use_original_date_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Use original date if possible', 'wp-content-pilot' ),
		'name'          => '_use_original_date',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_use_excerpt_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label' => __( 'Use summary as excerpt', 'wp-content-pilot' ),
		'name'  => '_excerpt',
	) );
}

function wpcp_escape_duplicate_title() {
	echo WPCP_HTML::checkbox_input( array(
		'label' => __( 'Enable post with duplicate title', 'wp-content-pilot' ),
		'name'  => '_enable_duplicate_title',
	) );
}

function wpcp_clean_post_title() {
	echo WPCP_HTML::checkbox_input( array(
		'label' => __( 'Clean title', 'wp-content-pilot' ),
		'name'  => '_clean_title',
	) );
}

function wpcp_target_rel_field() {
	echo WPCP_HTML::checkbox_input( array(
		'label'         => __( 'Add rel nofollow & set target blank for all links', 'wp-content-pilot' ),
		'name'          => '_add_rel_no_follow_target',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		)
	) );
}

function wpcp_not_save_featured_image_field() {
	if( is_plugin_active('featured-image-from-url/featured-image-from-url.php' ) ) {
		echo WPCP_HTML::checkbox_input( array(
			'label'         => __( 'Don\'t save the featured image in server. Rather than use Featured Image from URL plugin', 'wp-content-pilot' ),
			'name'          => '_not_save_featured_image',
		) );
	}
}

add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_featured_image_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_remove_images', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_strip_links_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_use_excerpt_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_escape_duplicate_title', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_clean_post_title', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_use_original_date_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_external_link_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_featured_image_random_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_canonical_link_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_target_rel_field', 20 );
add_action( 'wpcp_campaign_options_meta_fields', 'wpcp_not_save_featured_image_field', 20 );


function wpcp_search_replace_metafield( $post ) {
	?>
	<div class="wpcp-repeater form-field wpcp-field _wpcp_custom_meta_fields-field pro">
		<label class="wpcp-label">Search Replace</label>

		<table class="striped widefat wp-list-table">
			<thead>
			<tr>
				<th>Search</th>
				<th>Replace</th>
				<th></th>
			</tr>
			</thead>
			<tbody data-repeater-list="_wpcp_search_replaces">
			<tr data-repeater-item>
				<td><input type="text" name="search" placeholder="Search" disabled/></td>
				<td><input type="text" name="replace" placeholder="Replace" disabled/></td>
				<td><input data-repeater-delete type="button" value="Delete" class="button" disabled/></td>
			</tr>
			</tbody>
		</table>
		<button data-repeater-create type="button" class="button" disabled>Add New</button>
	</div>
	<?php
}

add_action( 'wpcp_advanced_settings_metabox', 'wpcp_search_replace_metafield' );


function wpcp_post_meta_metafield( $post ) {
	?>
	<div class="wpcp-repeater form-field wpcp-field _wpcp_custom_meta_fields-field pro">
		<label class="wpcp-label">Post Meta</label>

		<table class="striped widefat wp-list-table">
			<thead>
			<tr>
				<th>Meta Key</th>
				<th>Meta Value</th>
				<th></th>
			</tr>
			</thead>
			<tbody data-repeater-list="_wpcp_custom_meta_fields">
			<tr data-repeater-item>
				<td><input type="text" name="meta_key" placeholder="Meta Key" disabled/></td>
				<td><input type="text" name="meta_value" placeholder="Meta Value" disabled/></td>
				<td><input data-repeater-delete type="button" value="Delete" class="button" disabled/></td>
			</tr>
			</tbody>
		</table>

		<button data-repeater-create type="button" class="button" disabled>Add New</button>
	</div>
	<?php
}

add_action( 'wpcp_advanced_settings_metabox', 'wpcp_post_meta_metafield' );
