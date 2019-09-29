const mix = require('laravel-mix');

mix
  // Base
  .sass(
    'src/web/assets/base/src/scss/sproutforms.scss',
    'src/web/assets/base/dist/css/sproutforms.css',
  )
  .copy('src/web/assets/base/src/images',
    'src/web/assets/base/dist/images')

  // Base
  .sass(
    'src/web/assets/charts/src/scss/charts-explorer.scss',
    'src/web/assets/charts/dist/css/charts-explorer.css',
  )

  // Entries
  .js([
    'src/web/assets/entries/src/js/SproutFormsEntriesIndex.js',
    'src/web/assets/entries/src/js/SproutFormsEntriesTableView.js',
  ], 'src/web/assets/entries/dist/js/entries.js')

  // Forms
  .js([
    'src/web/assets/forms/src/js/ConditionalModal.js',
    'src/web/assets/forms/src/js/EditableTable.js',
    'src/web/assets/forms/src/js/FieldLayoutEditor.js',
    'src/web/assets/forms/src/js/FieldModal.js',
    'src/web/assets/forms/src/js/FormSettings.js',
    'src/web/assets/forms/src/js/IntegrationModal.js',
  ], 'src/web/assets/forms/dist/js/forms.js')
  .sass(
    'src/web/assets/forms/src/scss/forms.scss',
    'src/web/assets/forms/dist/css/forms.css',
  )

  // Integrations
  .js([
    'src/web/assets/integrations/src/js/Integration.js',
  ], 'src/web/assets/integrations/dist/js/integration.js')
  .sass(
    'src/web/assets/integrations/src/scss/integrations.scss',
    'src/web/assets/integrations/dist/css/integrations.css',
  )

  // Form Templates
  .copy([
    'src/web/assets/formtemplates/src/js/addressfield.js',
  ], 'src/web/assets/formtemplates/dist/js/addressfield.js')
  .copy([
    'src/web/assets/formtemplates/src/js/accessibility.js',
  ], 'src/web/assets/formtemplates/dist/js/accessibility.js')
  .copy([
    'src/web/assets/formtemplates/src/js/rules.js',
  ], 'src/web/assets/formtemplates/dist/js/rules.js');

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
