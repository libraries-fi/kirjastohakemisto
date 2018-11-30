const config = require("./webpack.config.js");
const merge = require("webpack-merge");
const path = require("path");
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

delete config.entry.bootstrap;

module.exports = merge(config, {
  mode: "production",
  output: {
    path: path.resolve(__dirname, 'public', 'dist'),
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            cacheDirectory: true,
            presets: [
              ["@babel/preset-env", {
                modules: false,
                targets: {
                  ie: 11
                }
              }]
            ]
          }
        }
      }
    ]
  },
  plugins: [new UglifyJsPlugin]
});

module.exports.entry.unshift('@babel/polyfill')
