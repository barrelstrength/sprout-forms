const mix = require('laravel-mix');

require('laravel-mix-polyfill');

/**
 * @param {Object} mix
 * @param {method} mix.sass
 * @param {method} mix.polyfill
 */
mix
  // Forms
  .js([
    'src/web/assets/cp/src/js/editable-table.js',
    'src/web/assets/cp/src/js/form-settings.js',
    'src/web/assets/cp/src/js/field-layout-editor.js',
    'src/web/assets/cp/src/js/field-modal.js',
    'src/web/assets/cp/src/js/integration-modal.js',
    'src/web/assets/cp/src/js/integrations.js',
    'src/web/assets/cp/src/js/rule-modal.js',
  ], 'src/web/assets/cp/dist/js/sproutforms-cp.js')

  // Entries Index
  .js([
    'src/web/assets/cp/src/js/entries-index.js',
    'src/web/assets/cp/src/js/entries-table-view.js',
  ], 'src/web/assets/cp/dist/js/sprout-entries-index.js')
  .sass('src/web/assets/cp/src/scss/charts.scss',
    'src/web/assets/cp/dist/css/sproutforms-charts.css')
  .sass('src/web/assets/cp/src/scss/forms-ui.scss',
    'src/web/assets/cp/dist/css/sproutforms-forms-ui.css')
  .sass('src/web/assets/cp/src/scss/cp.scss',
    'src/web/assets/cp/dist/css/sproutforms-cp.css')
  .copy('src/web/assets/cp/src/images',
    'src/web/assets/cp/dist/images')

  // Form Templates
  .js([
    'src/web/assets/formtemplates/src/js/accessibility.js',
  ], 'src/web/assets/formtemplates/dist/js/accessibility.js')
  .js([
    'src/web/assets/formtemplates/src/js/addressfield.js',
  ], 'src/web/assets/formtemplates/dist/js/addressfield.js')
  .js([
    'src/web/assets/formtemplates/src/js/disable-submit-button.js',
  ], 'src/web/assets/formtemplates/dist/js/disable-submit-button.js')
  .js([
    'src/web/assets/formtemplates/src/js/rules.js',
  ], 'src/web/assets/formtemplates/dist/js/rules.js')
  .js([
    'src/web/assets/formtemplates/src/js/submit-handler.js',
  ], 'src/web/assets/formtemplates/dist/js/submit-handler.js')
  .polyfill();

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.dump(); <-- Dump the generated webpack config object to the console.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   terser: {}, // Terser-specific options. https://github.com/webpack-contrib/terser-webpack-plugin#options
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
