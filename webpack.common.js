/*
 * Copyright (C) 2019 Gigadrive Group - All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

var {resolve} = require("path");
var glob = require("glob");

module.exports = {
	//entry: () => glob.sync("./src/Gigadrive/WebsiteBundle/Resources/public/assets/**/{index.ts,index.scss}"),
	entry: "./assets/ts/index.ts",
	devtool: false,
	module: {
		rules: [
			{
				test: /\.tsx?$/,
				use: "ts-loader",
				exclude: /node_modules/
			},
			{
				test: /\.css$/,
				use: [
					{loader: "style-loader"},
					{loader: "css-loader"}
				]
			},
			{
				test: /\.scss$/,
				use: [
					{loader: "style-loader"},
					{loader: "css-loader"},
					{
						loader: "sass-loader", options: {
							includePaths: [
								resolve(__dirname, "node_modules")
							]
						}
					}
				]
			},
			{
				test: /\.png$/,
				use: "url-loader"
			},
			{
				test: /\.jpg$/,
				use: "file-loader"
			},
			{
				test: /\.(woff|woff2)(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "url-loader?limit=10000&mimetype=application/font-woff"}]
			},
			{
				test: /\.ttf(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "url-loader?limit=10000&mimetype=application/octet-stream"}]
			},
			{
				test: /\.eot(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "file-loader"}]
			},
			{
				test: /\.svg(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "url-loader?limit=10000&mimetype=image/svg+xml"}]
			}
		]
	},
	resolve: {
		extensions: [".tsx", ".ts", ".js", ".jsx"]
	},
	output: {
		filename: "bundle.js",
		path: resolve(__dirname, "public/build/")
	},
	node: {
		console: true,
		fs: "empty",
		net: "empty",
		tls: "empty"
	},
	performance: {
		hints: false,
		maxEntrypointSize: 512000,
		maxAssetSize: 512000
	}
};