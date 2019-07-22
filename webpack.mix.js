const mix = require('laravel-mix');
const PurgeCss = require('@fullhuman/postcss-purgecss');
const tailwindcss = require('tailwindcss');
const postcss_import = require('postcss-import');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

let purgeCssConfig = {
    extractors: [
        {
            extractor: class {
                static extract(content) {
                    return content.match(/[A-Za-z0-9-_:/]+/g)
                }
            },
            extensions: ['html', 'js', 'php', 'vue'],
        }
    ],
};

let purgeAppCss = PurgeCss({
    ...purgeCssConfig,
    content: [
        './resources/views/**/*.blade.php',
        './resources/assets/js/**/*.vue',
        './app/Services/**/*.php'
    ],
    css: ['public/css/app.css'],
});

let purgeEmailCss = PurgeCss({
    ...purgeCssConfig,
    content: [
        './resources/views/emails/**/*.blade.php',
    ],
    css: ['public/css/email.css'],
});

mix
    .js('resources/assets/js/app.js', 'public/js')
    .postCss('resources/assets/css/app.css', 'public/css/app.css', [
        tailwindcss,
        ...mix.inProduction() ? [purgeAppCss] : [],
        postcss_import,
    ])
    .postCss('resources/assets/css/email.css', 'public/css/email.css', [
        tailwindcss,
        purgeEmailCss,
        postcss_import,
    ]);