let mix = require('laravel-mix');

const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const webpack = require('webpack');

mix
    .disableNotifications()
    .js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .webpackConfig({
        plugins: [
            new webpack.ContextReplacementPlugin(/moment[\/\\]locale$/, /fr/),
            new BundleAnalyzerPlugin({openAnalyzer: false})
        ]
    });
