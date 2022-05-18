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
	'label'         => __( 'Translator', 'wp-content-pilot' ),
	'type'          => 'select',
	'name'          => '_translator',
	'default'       => 'deepl',
	'wrapper_class' => 'pro',
	'options'       => array(
		''       => __( 'No Translation', 'wp-content-pilot' ),
		'yandex' => 'Yandex',
		'deepl'  => 'deepL',
	),
	'attrs'         => array(
		'disabled' => 'disabled',
	)
) );

echo WPCP_HTML::select_input( array(
	'label'         => __( 'Translate To', 'wp-content-pilot' ),
	'name'          => '_translate_to',
	'class'         => 'wpcp-select2',
	'options'       => array(
		''      => __( 'No Translation', 'wp-content-pilot' ),
		'af'    => __( 'Afrikaans', 'wp-content-pilot' ),
		'sq'    => __( 'Albanian', 'wp-content-pilot' ),
		'am'    => __( 'Amharic', 'wp-content-pilot' ),
		'ar'    => __( 'Arabic', 'wp-content-pilot' ),
		'hy'    => __( 'Armenian', 'wp-content-pilot' ),
		'az'    => __( 'Azerbaijani', 'wp-content-pilot' ),
		'ba'    => __( 'Bashkir', 'wp-content-pilot' ),
		'eu'    => __( 'Basque', 'wp-content-pilot' ),
		'be'    => __( 'Belarusian', 'wp-content-pilot' ),
		'bn'    => __( 'Bengali', 'wp-content-pilot' ),
		'bs'    => __( 'Bosnian', 'wp-content-pilot' ),
		'bg'    => __( 'Bulgarian', 'wp-content-pilot' ),
		'my'    => __( 'Burmese', 'wp-content-pilot' ),
		'ca'    => __( 'Catalan', 'wp-content-pilot' ),
		'ceb'   => __( 'Cebuano', 'wp-content-pilot' ),
		'zh'    => __( 'Chinese', 'wp-content-pilot' ),
		'hr'    => __( 'Croatian', 'wp-content-pilot' ),
		'cs'    => __( 'Czech', 'wp-content-pilot' ),
		'da'    => __( 'Danish', 'wp-content-pilot' ),
		'nl'    => __( 'Dutch', 'wp-content-pilot' ),
		'en'    => __( 'English', 'wp-content-pilot' ),
		'eo'    => __( 'Esperanto', 'wp-content-pilot' ),
		'et'    => __( 'Estonian', 'wp-content-pilot' ),
		'fi'    => __( 'Finnish', 'wp-content-pilot' ),
		'fr'    => __( 'French', 'wp-content-pilot' ),
		'gl'    => __( 'Galician', 'wp-content-pilot' ),
		'ka'    => __( 'Georgian', 'wp-content-pilot' ),
		'de'    => __( 'German', 'wp-content-pilot' ),
		'el'    => __( 'Greek', 'wp-content-pilot' ),
		'gu'    => __( 'Gujarati', 'wp-content-pilot' ),
		'ht'    => __( 'Haitian Creole', 'wp-content-pilot' ),
		'he'    => __( 'Hebrew', 'wp-content-pilot' ),
		'mrj'   => __( 'Hill Mari', 'wp-content-pilot' ),
		'hi'    => __( 'Hindi', 'wp-content-pilot' ),
		'hu'    => __( 'Hungarian', 'wp-content-pilot' ),
		'is'    => __( 'Icelandic', 'wp-content-pilot' ),
		'id'    => __( 'Indonesian', 'wp-content-pilot' ),
		'ga'    => __( 'Irish', 'wp-content-pilot' ),
		'it'    => __( 'Italian', 'wp-content-pilot' ),
		'ja'    => __( 'Japanese', 'wp-content-pilot' ),
		'jv'    => __( 'Javanese', 'wp-content-pilot' ),
		'kn'    => __( 'Kannada', 'wp-content-pilot' ),
		'kk'    => __( 'Kazakh', 'wp-content-pilot' ),
		'km'    => __( 'Khmer', 'wp-content-pilot' ),
		'ko'    => __( 'Korean', 'wp-content-pilot' ),
		'ky'    => __( 'Kyrgyz', 'wp-content-pilot' ),
		'lo'    => __( 'Lao', 'wp-content-pilot' ),
		'la'    => __( 'Latin', 'wp-content-pilot' ),
		'lv'    => __( 'Latvian', 'wp-content-pilot' ),
		'lt'    => __( 'Lithuanian', 'wp-content-pilot' ),
		'lb'    => __( 'Luxembourgish', 'wp-content-pilot' ),
		'mk'    => __( 'Macedonian', 'wp-content-pilot' ),
		'mg'    => __( 'Malagasy', 'wp-content-pilot' ),
		'ms'    => __( 'Malay', 'wp-content-pilot' ),
		'ml'    => __( 'Malayalam', 'wp-content-pilot' ),
		'mt'    => __( 'Maltese', 'wp-content-pilot' ),
		'mi'    => __( 'Maori', 'wp-content-pilot' ),
		'mr'    => __( 'Marathi', 'wp-content-pilot' ),
		'mhr'   => __( 'Mari', 'wp-content-pilot' ),
		'mn'    => __( 'Mongolian', 'wp-content-pilot' ),
		'ne'    => __( 'Nepali', 'wp-content-pilot' ),
		'no'    => __( 'Norwegian', 'wp-content-pilot' ),
		'pap'   => __( 'Papiamento', 'wp-content-pilot' ),
		'fa'    => __( 'Persian', 'wp-content-pilot' ),
		'pl'    => __( 'Polish', 'wp-content-pilot' ),
		'pt'    => __( 'Portuguese', 'wp-content-pilot' ),
		'pt-BR' => __( 'Portuguese (Brazilian)', 'wp-content-pilot' ),
		'pa'    => __( 'Punjabi', 'wp-content-pilot' ),
		'ro'    => __( 'Romanian', 'wp-content-pilot' ),
		'ru'    => __( 'Russian', 'wp-content-pilot' ),
		'gd'    => __( 'Scottish Gaelic', 'wp-content-pilot' ),
		'sr'    => __( 'Serbian', 'wp-content-pilot' ),
		'si'    => __( 'Sinhala', 'wp-content-pilot' ),
		'sk'    => __( 'Slovak', 'wp-content-pilot' ),
		'sl'    => __( 'Slovenian', 'wp-content-pilot' ),
		'es'    => __( 'Spanish', 'wp-content-pilot' ),
		'su'    => __( 'Sundanese', 'wp-content-pilot' ),
		'sw'    => __( 'Swahili', 'wp-content-pilot' ),
		'sv'    => __( 'Swedish', 'wp-content-pilot' ),
		'tl'    => __( 'Tagalog', 'wp-content-pilot' ),
		'tg'    => __( 'Tajik', 'wp-content-pilot' ),
		'ta'    => __( 'Tamil', 'wp-content-pilot' ),
		'tt'    => __( 'Tatar', 'wp-content-pilot' ),
		'te'    => __( 'Telugu', 'wp-content-pilot' ),
		'th'    => __( 'Thai', 'wp-content-pilot' ),
		'tr'    => __( 'Turkish', 'wp-content-pilot' ),
		'udm'   => __( 'Udmurt', 'wp-content-pilot' ),
		'uk'    => __( 'Ukrainian', 'wp-content-pilot' ),
		'ur'    => __( 'Urdu', 'wp-content-pilot' ),
		'uz'    => __( 'Uzbek', 'wp-content-pilot' ),
		'vi'    => __( 'Vietnamese', 'wp-content-pilot' ),
		'cy'    => __( 'Welsh', 'wp-content-pilot' ),
		'xh'    => __( 'Xhosa', 'wp-content-pilot' ),
		'yi'    => __( 'Yiddish', 'wp-content-pilot' ),
	),
	'tooltip'       => __( 'Select a language to translate.', 'wp-content-pilot' ),
	'wrapper_class' => 'pro',
	'attrs'         => array(
		'disabled' => 'disabled'
	)
) );

if ( is_plugin_active( 'polylang/polylang.php' ) || is_plugin_active( 'polylang-pro/polylang.php' ) ) {
	echo WPCP_HTML::checkbox_input( array(
			'label' => __( 'Enable Polylang for published posts', 'wp-content-pilot' ),
			'name'  => '_enable_polylang'
		)
	);
	echo WPCP_HTML::text_input( array(
		'label' => __( 'Two letter language codes.', 'wp-content-pilot' ),
		'name'  => '_polylang_language_code',
		'desc'  => __( 'Just give 2 letter language code. Like "de" for german, "bn" for bangla', 'wp-content-pilot' )
	) );
}

