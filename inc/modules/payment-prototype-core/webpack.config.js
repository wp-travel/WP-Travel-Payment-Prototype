const defaultConfig = require("@wordpress/scripts/config/webpack.config");

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const {
	readdirSync,
	existsSync
} = require('fs')


module.exports = {
	...defaultConfig,
	entry: {
		'wp-travel-settings': [
			'./app/src/admin/settings/index.js',
		],
	},
	output: {
		...defaultConfig.output,
		path: path.resolve(process.cwd(), 'app/build')
	},
	plugins: [
		...defaultConfig.plugins,
		new MiniCssExtractPlugin({
			// Options similar to the same options in webpackOptions.output
			// all options are optional
			filename: '[name].css',
			chunkFilename: '[id].css',
			ignoreOrder: false, // Enable to remove warnings about conflicting order
		}),
	],
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.scss$/,
				use: [{
						loader: 'style-loader'
					},
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader'
					},
					{
						loader: 'postcss-loader'
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: true
						}
					}
				],
			},
		]
	}
};
