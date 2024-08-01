const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const path = require('path');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

module.exports = [
		{
		...defaultConfig,
		entry: {
			...defaultConfig.entry(),
			'css/wp-content-pilot': './src/css/wp-content-pilot.scss',
			'js/wp-content-pilot': './src/js/wp-content-pilot.js',
			// Vendor -> ion slider assets.
			'vendor/ionslider/css/ion.rangeSlider': './src/vendor/ionslider/ion.rangeSlider.css',
			'vendor/ionslider/js/ion.rangeSlider': './src/vendor/ionslider/ion.rangeSlider.js',
			// Vendor -> Select2 assets.
			'vendor/select2/css/select2': './src/vendor/select2/select2.css',
			'vendor/select2/js/select2': './src/vendor/select2/select2.js',
			// Vendor -> tiptip assets.
			'vendor/tiptip/jquery.tiptip.min': './src/vendor/tiptip/jquery.tiptip.min.js',
		},
		output: {
			...defaultConfig.output,
			filename: '[name].js',
			path: __dirname + '/assets/',
		},
		plugins: [
			...defaultConfig.plugins,
			// Copy images to the build folder.
			new CopyWebpackPlugin({
				patterns: [
					{
						from: path.resolve(__dirname, 'src/images'),
						to: path.resolve(__dirname, 'assets/images'),
					}
				]
			}),

			new RemoveEmptyScriptsPlugin({
				stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
				remove: /\.(js)$/,
			}),
		],
	},
];
