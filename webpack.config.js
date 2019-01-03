var path = require("path");
var glob = require("glob");

module.exports = {
    entry: () => glob.sync("./assets/**/*.{ts,less}"),
    devtool: false,
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                use: "ts-loader",
                exclude: /node_modules/
            },
            {
                test: /\.less$/,
                use: [
                    { loader: "style-loader" },
                    { loader: "css-loader" },
                    { loader: "less-loader", options: {
                        paths: [
                            path.resolve(__dirname,"node_modules")
                        ]
                    } }
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
                use: [ { loader: "url-loader?limit=10000&mimetype=application/font-woff" } ]
            },
            {
                test: /\.ttf(\?v=\d+\.\d+\.\d+)?$/,
                use: [ { loader: "url-loader?limit=10000&mimetype=application/octet-stream" } ]
            },
            {
                test: /\.eot(\?v=\d+\.\d+\.\d+)?$/,
                use: [ { loader: "file-loader" } ]
            },
            {
                test: /\.svg(\?v=\d+\.\d+\.\d+)?$/,
                use: [ { loader: "url-loader?limit=10000&mimetype=image/svg+xml" } ]
            }
        ]
    },
    resolve: {
        extensions: [".tsx",".ts",".js"]
    },
    output: {
        filename: "app.js",
        path: path.resolve(__dirname,"webroot/bundle")
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
}