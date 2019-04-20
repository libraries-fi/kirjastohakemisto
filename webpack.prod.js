const config = require('./webpack.config.js')
const merge = require('webpack-merge')
const path = require('path')
// const TerserPlugin = require('terser-webpack-plugin')

delete config.entry.bootstrap

module.exports = merge(config, {
  mode: 'production',
  output: {
    path: path.resolve(__dirname, 'public', 'dist')
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            cacheDirectory: true,
            presets: [
              ['@babel/preset-env', {
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
  }
  // ,
  // optimization: {
  //   minimizer: [new TerserPlugin()]
  // }
})

module.exports.entry.unshift('@babel/polyfill')
