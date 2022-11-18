import('@vue/runtime-core')
// import('@vue/runtime-compiler')

var Encore = require('@symfony/webpack-encore')
const TsconfigPathsPlugin = require("tsconfig-paths-webpack-plugin")
const path = require('path')
const webpack = require('webpack')

// @see https://symfony.com/doc/current/frontend.html
Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build/')
    // .configureLoaderRule('fonts', loaderRule => {
    //     loaderRule.options.publicPath = './';
    // })
    // only needed for CDN's or sub-directory deploy
    // .setManifestKeyPrefix('build/')
    .addEntry('app', './assets/js/main.ts')

    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .enableSassLoader()
    .enableTypeScriptLoader()

    .enableVueLoader(() => {}, { runtimeCompilerBuild: false })
    .addPlugin(new webpack.DefinePlugin({
        __VUE_PROD_DEVTOOLS__: !Encore.isProduction()
        //__VUE_OPTIONS_API__: !Encore.isProduction()
    }))

    .configureBabel(function(babelConfig) {
        babelConfig.presets = [[
            "@babel/env",
            {
                "targets": {
                    "edge": "17",
                    "firefox": "60",
                    "chrome": "67",
                    "safari": "11.1"
                },
                "useBuiltIns": "usage",
                "corejs": "3.6.5"
            }
        ]]
    }, {
        // node_modules is not processed through Babel by default
        // but you can whitelist specific modules to process
        // includeNodeModules: [],
        exclude: /node_modules/
    })

    // Even thought we have TypeScript aliases, we still need to give
    // them to Webpack otherwise the babel-loader (for Vue.js) will not
    // find out path aliases.
    .addAliases({
        '@': path.resolve(__dirname, './assets/js'),
    })

const config = Encore.getWebpackConfig()

// Required for TypeScript to correctly resolve local namespaces.
// This plugins reads compilerOptions.paths directive from tsconfig.json
// file and will make Webpack resolve those correctly.
config.resolve.plugins = [new TsconfigPathsPlugin()]

module.exports = config
