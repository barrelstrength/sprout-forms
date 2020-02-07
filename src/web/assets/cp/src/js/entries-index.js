/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

/* global Craft */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

/**
 * Class Craft.SproutForms.EntriesIndex
 */
Craft.SproutForms.EntriesIndex = Craft.BaseElementIndex.extend({
  getViewClass: function(mode) {
    if (mode === 'table') {
      return Craft.SproutForms.EntriesTableView;
    } else {
      return this.base(mode);
    }
  },
  getDefaultSort: function() {
    return ['dateCreated', 'desc'];
  }
});

// Register the SproutForms EntriesIndex class
Craft.registerElementIndexClass('barrelstrength\\sproutforms\\elements\\Entry', Craft.SproutForms.EntriesIndex);