const config = require('./webpack.base.js')
const merge = require('webpack-merge')
const path = require('path')
// const TerserPlugin = require('terser-webpack-plugin')

module.exports = merge(config, {
  mode: 'development',
  output: {
    path: path.resolve(__dirname, 'public', 'dev'),
    filename: '[name].js'
  },
  module: {
    rules: [
      {
        test: /\.(png|jpg|gif|eot|woff|woff2|ttf)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 200,
              publicPath: '/dev/'
            }
          }
        ]
      }
    ]
  }
})

