<?php
defined( 'ABSPATH' ) || exit();
global $post;

echo WPCP_HTML::text_input( array(
	'label'   => __( 'Limit Title', 'wp-content-pilot' ),
	'type'    => 'number',
	'name'    => '_title_limit',
	'tooltip' => 'Input the number of words to limit the title. Default full title.',
) );

echo WPCP_HTML::text_input( array(
	'label'   => __( 'Limit Content', 'wp-content-pilot' ),
	'type'    => 'number',
	'name'    => '_content_limit',
	'tooltip' => 'Input the number of words to limit content. Default full content.',
) );

echo WPCP_HTML::text_input( array(
	'label'         => __( 'Excerpt Length', 'wp-content-pilot' ),
	'type'          => 'number',
	'name'          => '_excerpt_length',
	'default'       => '55',
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

echo WPCP_HTML::select_input( array(
	'label'         => __( 'Translate To', 'wp-content-pilot' ),
	'name'          => '_translate_to',
	'options'       => array(
		''      => __( 'No Translation', 'wp-content-pilot-pro' ),
		'af'    => __('Afrikaans', 'wp-content-pilot-pro'),
		'sq'    => __('Albanian', 'wp-content-pilot-pro'),
		'am'    => __('Amharic', 'wp-content-pilot-pro'),
		'ar'    => __('Arabic', 'wp-content-pilot-pro'),
		'hy'    => __('Armenian', 'wp-content-pilot-pro'),
		'az'    => __('Azerbaijani', 'wp-content-pilot-pro'),
		'bn'    => __('Bangla', 'wp-content-pilot-pro'),
		'eu'    => __('Basque', 'wp-content-pilot-pro'),
		'be'    => __('Belarusian', 'wp-content-pilot-pro'),
		'bs'    => __('Bosnian', 'wp-content-pilot-pro'),
		'bg'    => __('Bulgarian', 'wp-content-pilot-pro'),
		'my'    => __('Burmese', 'wp-content-pilot-pro'),
		'ca'    => __('Catalan', 'wp-content-pilot-pro'),
		'ceb'   => __('Cebuano', 'wp-content-pilot-pro'),
		'zh-CN' => __('Chinese (Simplified)', 'wp-content-pilot-pro'),
		'zh-TW' => __('Chinese (Traditional)', 'wp-content-pilot-pro'),
		'co'    => __('Corsican', 'wp-content-pilot-pro'),
		'hr'    => __('Croatian', 'wp-content-pilot-pro'),
		'cs'    => __('Czech', 'wp-content-pilot-pro'),
		'da'    => __('Danish', 'wp-content-pilot-pro'),
		'nl'    => __('Dutch', 'wp-content-pilot-pro'),
		'en'    => __('English', 'wp-content-pilot-pro'),
		'eo'    => __('Esperanto', 'wp-content-pilot-pro'),
		'et'    => __('Estonian', 'wp-content-pilot-pro'),
		'tl'    => __('Filipino', 'wp-content-pilot-pro'),
		'fi'    => __('Finnish', 'wp-content-pilot-pro'),
		'fr'    => __('French', 'wp-content-pilot-pro'),
		'gl'    => __('Galician', 'wp-content-pilot-pro'),
		'ka'    => __('Georgian', 'wp-content-pilot-pro'),
		'de'    => __('German', 'wp-content-pilot-pro'),
		'el'    => __('Greek', 'wp-content-pilot-pro'),
		'gu'    => __('Gujarati', 'wp-content-pilot-pro'),
		'ht'    => __('Haitian Creole', 'wp-content-pilot-pro'),
		'ha'    => __('Hausa', 'wp-content-pilot-pro'),
		'haw'   =>__('Hawaiian', 'wp-content-pilot-pro'),
		'iw'    => __('Hebrew', 'wp-content-pilot-pro'),
		'hi'    => __('Hindi', 'wp-content-pilot-pro'),
		'hmn'   => __('Hmong', 'wp-content-pilot-pro'),
		'hu'    => __('Hungarian', 'wp-content-pilot-pro'),
		'is'    => __('Icelandic', 'wp-content-pilot-pro'),
		'ig'    => __('Igbo', 'wp-content-pilot-pro'),
		'id'    => __('Indonesian', 'wp-content-pilot-pro'),
		'ga'    => __('Irish', 'wp-content-pilot-pro'),
		'it'    => __('Italian', 'wp-content-pilot-pro'),
		'ja'    => __('Japanese', 'wp-content-pilot-pro'),
		'jv'    => __('Javanese', 'wp-content-pilot-pro'),
		'kn'    => __('Kannada', 'wp-content-pilot-pro'),
		'kk'    => __('Kazakh', 'wp-content-pilot-pro'),
		'km'    => __('Khmer', 'wp-content-pilot-pro'),
		'ko'    => __('Korean', 'wp-content-pilot-pro'),
		'ku'    => __('Kurdish', 'wp-content-pilot-pro'),
		'ky'    => __('Kyrgyz', 'wp-content-pilot-pro'),
		'lo'    => __('Lao', 'wp-content-pilot-pro'),
		'la'    => __('Latin', 'wp-content-pilot-pro'),
		'lv'    => __('Latvian', 'wp-content-pilot-pro'),
		'lt'    => __('Lithuanian', 'wp-content-pilot-pro'),
		'lb'    => __('Luxembourgish', 'wp-content-pilot-pro'),
		'mk'    => __('Macedonian', 'wp-content-pilot-pro'),
		'mg'    => __('Malagasy', 'wp-content-pilot-pro'),
		'ms'    => __('Malay', 'wp-content-pilot-pro'),
		'ml'    => __('Malayalam', 'wp-content-pilot-pro'),
		'mt'    => __('Maltese', 'wp-content-pilot-pro'),
		'mi'    => __('Maori', 'wp-content-pilot-pro'),
		'mr'    => __('Marathi', 'wp-content-pilot-pro'),
		'mn'    => __('Mongolian', 'wp-content-pilot-pro'),
		'ne'    => __('Nepali', 'wp-content-pilot-pro'),
		'no'    => __('Norwegian', 'wp-content-pilot-pro'),
		'ny'    => __('Nyanja', 'wp-content-pilot-pro'),
		'ps'    => __('Pashto', 'wp-content-pilot-pro'),
		'fa'    => __('Persian', 'wp-content-pilot-pro'),
		'pl'    => __('Polish', 'wp-content-pilot-pro'),
		'pt'    => __('Portuguese', 'wp-content-pilot-pro'),
		'pa'    => __('Punjabi', 'wp-content-pilot-pro'),
		'ro'    => __('Romanian', 'wp-content-pilot-pro'),
		'ru'    => __('Russian', 'wp-content-pilot-pro'),
		'sm'    => __('Samoan', 'wp-content-pilot-pro'),
		'gd'    => __('Scottish Gaelic', 'wp-content-pilot-pro'),
		'sr'    => __('Serbian', 'wp-content-pilot-pro'),
		'sn'    => __('Shona', 'wp-content-pilot-pro'),
		'sd'    => __('Sindhi', 'wp-content-pilot-pro'),
		'si'    => __('Sinhala', 'wp-content-pilot-pro'),
		'sk'    => __('Slovak', 'wp-content-pilot-pro'),
		'sl'    => __('Slovenian', 'wp-content-pilot-pro'),
		'so'    => __('Somali', 'wp-content-pilot-pro'),
		'st'    => __('Southern Sotho', 'wp-content-pilot-pro'),
		'es'    => __('Spanish', 'wp-content-pilot-pro'),
		'su'    => __('Sundanese', 'wp-content-pilot-pro'),
		'sw'    => __('Swahili', 'wp-content-pilot-pro'),
		'sv'    => __('Swedish', 'wp-content-pilot-pro'),
		'tg'    => __('Tajik', 'wp-content-pilot-pro'),
		'ta'    => __('Tamil', 'wp-content-pilot-pro'),
		'te'    => __('Telugu', 'wp-content-pilot-pro'),
		'th'    => __('Thai', 'wp-content-pilot-pro'),
		'tr'    => __('Turkish', 'wp-content-pilot-pro'),
		'uk'    => __('Ukrainian', 'wp-content-pilot-pro'),
		'ur'    => __('Urdu', 'wp-content-pilot-pro'),
		'uz'    => __('Uzbek', 'wp-content-pilot-pro'),
		'vi'    => __('Vietnamese', 'wp-content-pilot-pro'),
		'cy'    => __('Welsh', 'wp-content-pilot-pro'),
		'fy'    => __('Western Frisian', 'wp-content-pilot-pro'),
		'xh'    => __('Xhosa', 'wp-content-pilot-pro'),
		'yi'    => __('Yiddish', 'wp-content-pilot-pro'),
		'yo'    => __('Yoruba', 'wp-content-pilot-pro'),
		'zu'    => __('Zulu', 'wp-content-pilot-pro'),
	),
	'tooltip'       => __( 'Select a language to translate.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

?>
<div class="wpcp-repeater form-field wpcp-field _wpcp_custom_meta_field-field pro">
	<label  class="wpcp-label">Search Replace</label>

	<table class="striped widefat wp-list-table">
		<thead>
		<tr>
			<th>Search</th>
			<th>Replace</th>
			<th></th>
		</tr>
		</thead>
		<tbody data-repeater-list="_wpcp_search_n_replace">
		<tr data-repeater-item>
			<td><input type="text" name="search" placeholder="Search"/></td>
			<td><input type="text" name="replace" placeholder="Replace"/></td>
			<td><input data-repeater-delete type="button" value="Delete" class="button"/></td>
		</tr>
		</tbody>
	</table>
	<button data-repeater-create type="button" class="button">Add New</button>
</div>


<div class="wpcp-repeater form-field wpcp-field _wpcp_custom_meta_field-field pro">
	<label  class="wpcp-label">Post Meta</label>

	<table class="striped widefat wp-list-table">
		<thead>
		<tr>
			<th>Meta Key</th>
			<th>Meta Value</th>
			<th></th>
		</tr>
		</thead>
		<tbody data-repeater-list="_wpcp_custom_meta_field">
		<tr data-repeater-item>
			<td><input type="text" name="meta_key" placeholder="Meta Key"/></td>
			<td><input type="text" name="meta_value" placeholder="Meta Value"/></td>
			<td><input data-repeater-delete type="button" value="Delete" class="button"/></td>
		</tr>
		</tbody>
	</table>

	<button data-repeater-create type="button" class="button">Add New</button>
</div>

