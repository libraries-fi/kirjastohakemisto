const path = require("path");
const ExtractTextPlugin = require("extract-text-webpack-plugin");

const precss = require("precss");
const autoprefixer = require("autoprefixer");

module.exports = {
  entry: {
    hakemisto: "./js/init.webpack.js",
    style: "./scss/hakemisto.scss"
  },
  output: {
    path: path.resolve(__dirname, "dist"),
    filename: "[name].js"
  },
  module: {
    rules: [
      // {
      //   test: /\.scss/,
      //   use: [
      //     "css-loader",
      //     {
      //       loader: "postcss-loader",
      //       options: {
      //         plugins: [precss, autoprefixer]
      //       }
      //     },
      //     "sass-loader"
      //   ]
      // },
      // {
      //   test: /\.css$/,
      //   loader: "css-loader",
      // },

      {
        test: /\.(scss|css)$/,
        use: ExtractTextPlugin.extract({
          use: [
            {loader: "css-loader"},
            {
              loader: "postcss-loader",
              options: {
                plugins: function() {
                  return [
                    require("precss"),
                    require("autoprefixer")
                  ]
                }
              }
            },
            {
              loader: "sass-loader"
            }
          ]
        })
      },
      {
        test: /\.yaml$/,
        use: [
          {
            loader: "file-loader",
            options: {
              name: "[path][name].json",
              context: path.resolve("../translations")
            }
          },
          {
            loader: "yaml-loader"
          }
        ]
      }
    ]
  },
  plugins: [
    new ExtractTextPlugin({filename: "hakemisto.css"}),
  ]
};
