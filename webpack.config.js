const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const autoprefixer = require("autoprefixer");
const path = require("path");
const precss = require("precss");
const webpack = require("webpack");

module.exports = {
  mode: "development",
  entry: {
    hakemisto: "./public/js/init.webpack.js",
    style: "./public/scss/hakemisto.scss",
    assets: [
      "./node_modules/chosen-js/chosen.min.css",
      "./node_modules/font-awesome/css/font-awesome.min.css",
      "./node_modules/ol/ol.css"
    ]
  },
  output: {
    path: path.resolve(__dirname, "public/dev"),
  },
  module: {
    // noParse: [/\.min\.js/],
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          {
            loader: "postcss-loader",
            options: {
              plugins: [autoprefixer]
            }
          },
          "sass-loader"
        ]
      },
      {
        test: /\.css$/,
        use: [
          "style-loader",
          "css-loader"
        ]
      },
      {
        test: /\.yaml$/,
        use: ["js-yaml-loader"]
      },
      {
        test: /\.(png|jpg|gif|eot|woff|woff2|ttf|svg)$/,
        use: [
          {
            loader: "url-loader",
            options: {
              limit: 200,
            }
          }
        ]
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin,
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
    })
  ],
  resolve: {
    alias: {
      "browser.fi.json": path.resolve("translations/browser.fi.yaml"),
      "browser.sv.json": path.resolve("translations/browser.sv.yaml"),
      "browser.en.json": path.resolve("translations/browser.en.yaml"),
    }
  }
};
