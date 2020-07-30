<?php
defined( 'ABSPATH' ) || exit();
global $post;
echo WPCP_HTML::start_double_columns();
echo WPCP_HTML::select_input(array(
	'label' => __('Action','wp-content-pilotwp-content-pilot'),
	'name' => '_spinner_action',
	'options' => array(
		'api_quota' => __('Api Quota','wp-content-pilot'),
		'text_with_spintax' => __('Text with Spintax','wp-content-pilot'),
		'unique_variation' => __('Unique Variation','wp-content-pilot'),
		'unique_variation_from_spintax' => __('Unique Variation from Spintax','wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' => 'api_quota',
	'desc' => __('The action that you\'re requesting from the Spin Rewriter server.','wp-content-pilot'),
));
echo WPCP_HTML::select_input(array(
	'label' => __('Auto Protected Terms','wp-content-pilot'),
	'name' => '_spinner_auto_protected_terms',
	'options' => array(
		false => __('False','wp-content-pilot'),
		true => __('True','wp-content-pilot')
	),
	'default'=> false,
	'desc' => __('Should Spin Rewriter automatically protect all Capitalized Words except for those in the title of your original text?','wp-content-pilot')
));
echo WPCP_HTML::select_input(array(
	'label' => __('Confidence level','wp-content-pilot'),
	'name' => '_spinner_confidence_level',
	'options' => array(
		'low'=> __("Low",'wp-content-pilot'),
		'medium' => __("Medium",'wp-content-pilot'),
		'high' => __('High','wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' =>'medium',
	'desc'=> __('The confidence level of the One-Click Rewrite process.','wp-content-pilot'),
	
));
echo WPCP_HTML::select_input(array(
	'label' => __('Nested spintax','wp-content-pilot'),
	'name' => '_spinner_nested_spintax',
	'options' => array(
		false=> __("False",'wp-content-pilot'),
		true => __("True",'wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' =>false,
	'desc'=> __('Should Spin Rewriter also spin single words inside already spun phrases?','wp-content-pilot'),

));
echo WPCP_HTML::select_input(array(
	'label' => __('Auto sentences','wp-content-pilot'),
	'name' => '_spinner_auto_sentences',
	'options' => array(
		false => __("False",'wp-content-pilot'),
		true => __("True",'wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' => false,
	'desc'=> __('Should Spin Rewriter spin complete sentences?','wp-content-pilot'),

));
echo WPCP_HTML::select_input(array(
	'label' => __('Auto paragraphs','wp-content-pilot'),
	'name' => '_spinner_auto_paragraphs',
	'options' => array(
		false => __("False",'wp-content-pilot'),
		true => __("True",'wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' => false,
	'desc'=> __('Should Spin Rewriter spin entire paragraphs?','wp-content-pilot'),
));
echo WPCP_HTML::select_input(array(
	'label' => __('Auto new paragraphs','wp-content-pilot'),
	'name' => '_spinner_auto_new_paragraphs',
	'options' => array(
		false => __("False",'wp-content-pilot'),
		true => __("True",'wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' => false,
	'desc'=> __('Should Spin Rewriter automatically write additional paragraphs on its own?','wp-content-pilot'),
));
echo WPCP_HTML::select_input(array(
	'label' => __('Auto sentence trees','wp-content-pilot'),
	'name' => '_spinner_auto_sentence_trees',
	'options' => array(
		false => __("False",'wp-content-pilot'),
		true => __("True",'wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' => false,
	'desc'=> __('Should Spin Rewriter automatically change the entire structure of phrases and sentences?','wp-content-pilot'),
));
echo WPCP_HTML::select_input(array(
	'label' => __('Use Only Synonyms','wp-content-pilot'),
	'name' => '_spinner_use_only_synonyms',
	'options' => array(
		false => __("False",'wp-content-pilot'),
		true => __("True",'wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' => false,
	'desc'=> __('Should Spin Rewriter use only synonyms of the original words instead of the original words themselves?They work along side with action values "unique variation and unique variation from spintax"','wp-content-pilot'),
));
echo WPCP_HTML::select_input(array(
	'label' => __('Reorder paragraphs','wp-content-pilot'),
	'name' => '_spinner_reorder_paragraphs',
	'options' => array(
		false => __("False",'wp-content-pilot'),
		true => __("True",'wp-content-pilot'),
	),
	'class' => 'wpcp-select2',
	'default' => false,
	'desc'=> __('Should Spin Rewriter intelligently randomize the order of paragraphs and unordered lists when generating spun text? They work along side with action values "unique variation and unique variation from spintax"','wp-content-pilot'),
));

echo WPCP_HTML::end_double_columns();
