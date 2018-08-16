const merge = require("webpack-merge");
const config = require("./webpack.config.js");

const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

module.exports = merge(config, {
  // mode: "production",
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            cacheDirectory: true,
            presets: ["env"]
          }
        }
      }
    ]
  },
  plugins: [new UglifyJsPlugin]
});
