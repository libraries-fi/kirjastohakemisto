const VueLoaderPlugin = require('vue-loader/lib/plugin')
const autoprefixer = require('autoprefixer')
const path = require('path')

module.exports = {
  mode: 'development',
  entry: {
    main: './frontend/main.js'
  },
  output: {
    path: path.resolve(__dirname, 'public'),
    filename: 'dev/[name].js'
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          'style-loader',
          'css-loader',
          {
            loader: 'postcss-loader',
            options: {
              plugins: [autoprefixer]
            }
          },
          'sass-loader'
        ]
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.yaml$/,
        use: [{
          loader: 'js-yaml-loader',
          options: {}
        }]
      },
      {
        test: /\.svg$/,
        use: ['svg-inline-loader']
      }
    ]
  },
  plugins: [
    new VueLoaderPlugin,
    autoprefixer,
  ],
  resolve: {
    extensions: ['.js', '.vue', '.json'],
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      '@': path.resolve('./frontend'),
      'messages.fi.yaml': path.resolve('translations/messages.fi.yaml'),
      'messages.en.yaml': path.resolve('translations/messages.en.yaml'),
    }
  }
}
