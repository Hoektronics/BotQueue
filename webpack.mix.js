const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss')

require('laravel-mix-purgecss');

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

// let purgeCssConfig = {
//     extractors: [
//         {
//             extractor: class {
//                 static extract(content) {
//                     return content.match(/[A-Za-z0-9-_:/]+/g)
//                 }
//             },
//             extensions: ['html', 'js', 'php', 'vue'],
//         }
//     ],
// };
//
// let purgeAppCss = new PurgeCss({
//     ...purgeCssConfig,
//     content: [
//         './resources/views/**/*.blade.php',
//         './resources/js/**/*.vue',
//         './app/Services/**/*.php'
//     ],
//     css: ['public/css/app.css'],
// });
//
// let purgeEmailCss = PurgeCss({
//     ...purgeCssConfig,
//     content: [
//         './resources/views/emails/**/*.blade.php',
//     ],
//     css: ['public/css/email.css'],
// });

mix.js('resources/js/app.js', 'public/js');

mix.sass('resources/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false,
        postCss: [ tailwindcss('tailwind.config.js') ],
    })
    .purgeCss({
        enabled: true,
        css: ['public/css/app.css'],
        content: [
            'resources/views/**/*.blade.php',
            'resources/js/**/*.vue',
            'app/Services/**/*.php'
        ],
    })

mix.sass('resources/sass/email.scss', 'public/css')
    .options({
        processCssUrls: false,
        postCss: [ tailwindcss('tailwind.config.js') ],
    })
    .purgeCss({
        enabled: true,
        css: ['public/css/email.css'],
        content: [
            'resources/views/emails/**/*.blade.php',
        ],
    });