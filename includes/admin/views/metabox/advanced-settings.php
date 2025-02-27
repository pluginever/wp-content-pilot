<?php
defined( 'ABSPATH' ) || exit();
global $post;

echo WPCP_HTML::text_input(
	array(
		'label'   => esc_html__( 'Limit Title', 'wp-content-pilot' ),
		'type'    => 'number',
		'name'    => '_title_limit',
		'tooltip' => esc_html__( 'Input the number of words to limit the title. Default full title.', 'wp-content-pilot' ),
	)
);

echo WPCP_HTML::text_input(
	array(
		'label'   => esc_html__( 'Limit Content', 'wp-content-pilot' ),
		'type'    => 'number',
		'name'    => '_content_limit',
		'tooltip' => esc_html__( 'Input the number of words to limit content. Default full content.', 'wp-content-pilot' ),
	)
);

echo WPCP_HTML::text_input(
	array(
		'label'         => esc_html__( 'Excerpt Length', 'wp-content-pilot' ),
		'type'          => 'number',
		'name'          => '_excerpt_length',
		'default'       => '55',
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);


echo WPCP_HTML::select_input(
	array(
		'label'         => esc_html__( 'Translator', 'wp-content-pilot' ),
		'type'          => 'select',
		'name'          => '_translator',
		'default'       => 'deepl',
		'wrapper_class' => 'pro',
		'options'       => array(
			''       => esc_html__( 'No Translation', 'wp-content-pilot' ),
			// 'yandex' => 'Yandex', // TODO: Remove this option, as it is not used due to the removal of the Yandex API key setting.
			'deepl'  => 'deepL',
		),
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);

echo WPCP_HTML::select_input(
	array(
		'label'         => esc_html__( 'Translate To', 'wp-content-pilot' ),
		'name'          => '_translate_to',
		'class'         => 'wpcp-select2',
		'options'       => array(
			''      => esc_html__( 'No Translation', 'wp-content-pilot' ),
			'af'    => esc_html__( 'Afrikaans', 'wp-content-pilot' ),
			'sq'    => esc_html__( 'Albanian', 'wp-content-pilot' ),
			'am'    => esc_html__( 'Amharic', 'wp-content-pilot' ),
			'ar'    => esc_html__( 'Arabic', 'wp-content-pilot' ),
			'hy'    => esc_html__( 'Armenian', 'wp-content-pilot' ),
			'az'    => esc_html__( 'Azerbaijani', 'wp-content-pilot' ),
			'ba'    => esc_html__( 'Bashkir', 'wp-content-pilot' ),
			'eu'    => esc_html__( 'Basque', 'wp-content-pilot' ),
			'be'    => esc_html__( 'Belarusian', 'wp-content-pilot' ),
			'bn'    => esc_html__( 'Bengali', 'wp-content-pilot' ),
			'bs'    => esc_html__( 'Bosnian', 'wp-content-pilot' ),
			'bg'    => esc_html__( 'Bulgarian', 'wp-content-pilot' ),
			'my'    => esc_html__( 'Burmese', 'wp-content-pilot' ),
			'ca'    => esc_html__( 'Catalan', 'wp-content-pilot' ),
			'ceb'   => esc_html__( 'Cebuano', 'wp-content-pilot' ),
			'zh'    => esc_html__( 'Chinese', 'wp-content-pilot' ),
			'hr'    => esc_html__( 'Croatian', 'wp-content-pilot' ),
			'cs'    => esc_html__( 'Czech', 'wp-content-pilot' ),
			'da'    => esc_html__( 'Danish', 'wp-content-pilot' ),
			'nl'    => esc_html__( 'Dutch', 'wp-content-pilot' ),
			'en'    => esc_html__( 'English', 'wp-content-pilot' ),
			'eo'    => esc_html__( 'Esperanto', 'wp-content-pilot' ),
			'et'    => esc_html__( 'Estonian', 'wp-content-pilot' ),
			'fi'    => esc_html__( 'Finnish', 'wp-content-pilot' ),
			'fr'    => esc_html__( 'French', 'wp-content-pilot' ),
			'gl'    => esc_html__( 'Galician', 'wp-content-pilot' ),
			'ka'    => esc_html__( 'Georgian', 'wp-content-pilot' ),
			'de'    => esc_html__( 'German', 'wp-content-pilot' ),
			'el'    => esc_html__( 'Greek', 'wp-content-pilot' ),
			'gu'    => esc_html__( 'Gujarati', 'wp-content-pilot' ),
			'ht'    => esc_html__( 'Haitian Creole', 'wp-content-pilot' ),
			'he'    => esc_html__( 'Hebrew', 'wp-content-pilot' ),
			'mrj'   => esc_html__( 'Hill Mari', 'wp-content-pilot' ),
			'hi'    => esc_html__( 'Hindi', 'wp-content-pilot' ),
			'hu'    => esc_html__( 'Hungarian', 'wp-content-pilot' ),
			'is'    => esc_html__( 'Icelandic', 'wp-content-pilot' ),
			'id'    => esc_html__( 'Indonesian', 'wp-content-pilot' ),
			'ga'    => esc_html__( 'Irish', 'wp-content-pilot' ),
			'it'    => esc_html__( 'Italian', 'wp-content-pilot' ),
			'ja'    => esc_html__( 'Japanese', 'wp-content-pilot' ),
			'jv'    => esc_html__( 'Javanese', 'wp-content-pilot' ),
			'kn'    => esc_html__( 'Kannada', 'wp-content-pilot' ),
			'kk'    => esc_html__( 'Kazakh', 'wp-content-pilot' ),
			'km'    => esc_html__( 'Khmer', 'wp-content-pilot' ),
			'ko'    => esc_html__( 'Korean', 'wp-content-pilot' ),
			'ky'    => esc_html__( 'Kyrgyz', 'wp-content-pilot' ),
			'lo'    => esc_html__( 'Lao', 'wp-content-pilot' ),
			'la'    => esc_html__( 'Latin', 'wp-content-pilot' ),
			'lv'    => esc_html__( 'Latvian', 'wp-content-pilot' ),
			'lt'    => esc_html__( 'Lithuanian', 'wp-content-pilot' ),
			'lb'    => esc_html__( 'Luxembourgish', 'wp-content-pilot' ),
			'mk'    => esc_html__( 'Macedonian', 'wp-content-pilot' ),
			'mg'    => esc_html__( 'Malagasy', 'wp-content-pilot' ),
			'ms'    => esc_html__( 'Malay', 'wp-content-pilot' ),
			'ml'    => esc_html__( 'Malayalam', 'wp-content-pilot' ),
			'mt'    => esc_html__( 'Maltese', 'wp-content-pilot' ),
			'mi'    => esc_html__( 'Maori', 'wp-content-pilot' ),
			'mr'    => esc_html__( 'Marathi', 'wp-content-pilot' ),
			'mhr'   => esc_html__( 'Mari', 'wp-content-pilot' ),
			'mn'    => esc_html__( 'Mongolian', 'wp-content-pilot' ),
			'ne'    => esc_html__( 'Nepali', 'wp-content-pilot' ),
			'no'    => esc_html__( 'Norwegian', 'wp-content-pilot' ),
			'pap'   => esc_html__( 'Papiamento', 'wp-content-pilot' ),
			'fa'    => esc_html__( 'Persian', 'wp-content-pilot' ),
			'pl'    => esc_html__( 'Polish', 'wp-content-pilot' ),
			'pt'    => esc_html__( 'Portuguese', 'wp-content-pilot' ),
			'pt-BR' => esc_html__( 'Portuguese (Brazilian)', 'wp-content-pilot' ),
			'pa'    => esc_html__( 'Punjabi', 'wp-content-pilot' ),
			'ro'    => esc_html__( 'Romanian', 'wp-content-pilot' ),
			'ru'    => esc_html__( 'Russian', 'wp-content-pilot' ),
			'gd'    => esc_html__( 'Scottish Gaelic', 'wp-content-pilot' ),
			'sr'    => esc_html__( 'Serbian', 'wp-content-pilot' ),
			'si'    => esc_html__( 'Sinhala', 'wp-content-pilot' ),
			'sk'    => esc_html__( 'Slovak', 'wp-content-pilot' ),
			'sl'    => esc_html__( 'Slovenian', 'wp-content-pilot' ),
			'es'    => esc_html__( 'Spanish', 'wp-content-pilot' ),
			'su'    => esc_html__( 'Sundanese', 'wp-content-pilot' ),
			'sw'    => esc_html__( 'Swahili', 'wp-content-pilot' ),
			'sv'    => esc_html__( 'Swedish', 'wp-content-pilot' ),
			'tl'    => esc_html__( 'Tagalog', 'wp-content-pilot' ),
			'tg'    => esc_html__( 'Tajik', 'wp-content-pilot' ),
			'ta'    => esc_html__( 'Tamil', 'wp-content-pilot' ),
			'tt'    => esc_html__( 'Tatar', 'wp-content-pilot' ),
			'te'    => esc_html__( 'Telugu', 'wp-content-pilot' ),
			'th'    => esc_html__( 'Thai', 'wp-content-pilot' ),
			'tr'    => esc_html__( 'Turkish', 'wp-content-pilot' ),
			'udm'   => esc_html__( 'Udmurt', 'wp-content-pilot' ),
			'uk'    => esc_html__( 'Ukrainian', 'wp-content-pilot' ),
			'ur'    => esc_html__( 'Urdu', 'wp-content-pilot' ),
			'uz'    => esc_html__( 'Uzbek', 'wp-content-pilot' ),
			'vi'    => esc_html__( 'Vietnamese', 'wp-content-pilot' ),
			'cy'    => esc_html__( 'Welsh', 'wp-content-pilot' ),
			'xh'    => esc_html__( 'Xhosa', 'wp-content-pilot' ),
			'yi'    => esc_html__( 'Yiddish', 'wp-content-pilot' ),
		),
		'tooltip'       => esc_html__( 'Select a language to translate.', 'wp-content-pilot' ),
		'wrapper_class' => 'pro',
		'attrs'         => array(
			'disabled' => 'disabled',
		),
	)
);

if ( is_plugin_active( 'polylang/polylang.php' ) || is_plugin_active( 'polylang-pro/polylang.php' ) ) {
	echo WPCP_HTML::checkbox_input(
		array(
			'label' => esc_html__( 'Enable Polylang for published posts', 'wp-content-pilot' ),
			'name'  => '_enable_polylang',
		)
	);
	echo WPCP_HTML::text_input(
		array(
			'label' => esc_html__( 'Two letter language codes.', 'wp-content-pilot' ),
			'name'  => '_polylang_language_code',
			'desc'  => esc_html__( 'Just give 2 letter language code. Like "de" for german, "bn" for bangla', 'wp-content-pilot' ),
		)
	);
}
