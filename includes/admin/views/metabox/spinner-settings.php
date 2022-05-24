<?php
defined( 'ABSPATH' ) || exit();
global $post;
$email    = wpcp_get_settings( 'spinrewriter_email', 'wpcp_article_spinner' );
$api      = wpcp_get_settings( 'spinrewriter_api_key', 'wpcp_article_spinner' );
$disabled = empty( $email ) && empty( $api );
if ( $disabled ) {
	?>
	<p><?php esc_attr_e('Please configure spinner settings to use this section', 'wp-content-pilot');?>
	<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wp_content_pilot&page=wpcp-settings' ) ); ?>"><?php esc_attr_e('Settings', 'wp-content-pilot');?></a>
	</p>
	<?php
	return;
}
echo WPCP_HTML::start_double_columns();
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Action', 'wp-content-pilot' ),
	'name'    => '_spinner_action',
	'options' => array(
		'api_quota'                     => __( 'API quota', 'wp-content-pilot' ),
		'text_with_spintax'             => __( 'Text with spintax', 'wp-content-pilot' ),
		'unique_variation'              => __( 'Unique variation', 'wp-content-pilot' ),
		'unique_variation_from_spintax' => __( 'Unique variation from spintax', 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => 'unique_variation',
	'desc'    => __( 'The action that you\'re requesting from the spin rewriter server.', 'wp-content-pilot' ),
) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Auto Protected Terms', 'wp-content-pilot' ),
	'name'    => '_spinner_auto_protected_terms',
	'options' => array(
		false => __( 'No', 'wp-content-pilot' ),
		true  => __( 'Yes', 'wp-content-pilot' )
	),
	'default' => false,
	'desc'    => __( 'Should spin rewriter automatically protect all capitalized words except for those in the title of your original text?', 'wp-content-pilot' )
) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Confidence level', 'wp-content-pilot' ),
	'name'    => '_spinner_confidence_level',
	'options' => array(
		'low'    => __( "Low", 'wp-content-pilot' ),
		'medium' => __( "Medium", 'wp-content-pilot' ),
		'high'   => __( 'High', 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => 'high',
	'desc'    => __( 'The confidence level of the one-click rewrite process.', 'wp-content-pilot' ),

) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Nested spintax', 'wp-content-pilot' ),
	'name'    => '_spinner_nested_spintax',
	'options' => array(
		false => __( "No", 'wp-content-pilot' ),
		true  => __( "Yes", 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => false,
	'desc'    => __( 'Should spin rewriter also spin single words inside already spun phrases?', 'wp-content-pilot' ),

) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Auto sentences', 'wp-content-pilot' ),
	'name'    => '_spinner_auto_sentences',
	'options' => array(
		false => __( "No", 'wp-content-pilot' ),
		true  => __( "Yes", 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => false,
	'desc'    => __( 'Should spin rewriter spin complete sentences?', 'wp-content-pilot' ),

) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Auto paragraphs', 'wp-content-pilot' ),
	'name'    => '_spinner_auto_paragraphs',
	'options' => array(
		false => __( "No", 'wp-content-pilot' ),
		true  => __( "Yes", 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => false,
	'desc'    => __( 'Should spin rewriter spin entire paragraphs?', 'wp-content-pilot' ),
) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Auto new paragraphs', 'wp-content-pilot' ),
	'name'    => '_spinner_auto_new_paragraphs',
	'options' => array(
		false => __( "No", 'wp-content-pilot' ),
		true  => __( "Yes", 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => false,
	'desc'    => __( 'Should spin rewriter automatically write additional paragraphs on its own?', 'wp-content-pilot' ),
) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Auto sentence trees', 'wp-content-pilot' ),
	'name'    => '_spinner_auto_sentence_trees',
	'options' => array(
		false => __( "No", 'wp-content-pilot' ),
		true  => __( "Yes", 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => false,
	'desc'    => __( 'Should spin rewriter automatically change the entire structure of phrases and sentences?', 'wp-content-pilot' ),
) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Use Only Synonyms', 'wp-content-pilot' ),
	'name'    => '_spinner_use_only_synonyms',
	'options' => array(
		false => __( "No", 'wp-content-pilot' ),
		true  => __( "Yes", 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => false,
	'desc'    => __( 'Should spin rewriter use only synonyms of the original words instead of the original words themselves? They work along side with action values "unique variation and unique variation from spintax"', 'wp-content-pilot' ),
) );
echo WPCP_HTML::select_input( array(
	'label'   => __( 'Reorder paragraphs', 'wp-content-pilot' ),
	'name'    => '_spinner_reorder_paragraphs',
	'options' => array(
		false => __( "No", 'wp-content-pilot' ),
		true  => __( "Yes", 'wp-content-pilot' ),
	),
	'class'   => 'wpcp-select2',
	'default' => false,
	'desc'    => __( 'Should spin rewriter intelligently randomize the order of paragraphs and unordered lists when generating spun text? They work along side with action values "unique variation and unique variation from spintax"', 'wp-content-pilot' ),
) );

echo WPCP_HTML::end_double_columns();
