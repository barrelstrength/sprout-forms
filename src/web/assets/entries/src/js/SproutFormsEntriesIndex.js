if (typeof Craft.SproutForms === typeof undefined) {
    Craft.SproutForms = {};
}

/**
 * Class Craft.SproutForms.EntriesIndex
 */
Craft.SproutForms.EntriesIndex = Craft.BaseElementIndex.extend({
    getViewClass: function(mode) {
        switch (mode) {
            case 'table':
                return Craft.SproutForms.EntriesTableView;
            default:
                return this.base(mode);
        }
    },
    getDefaultSort: function() {
        return ['dateCreated', 'desc'];
    }
});

// Register the SproutForms EntriesIndex class
Craft.registerElementIndexClass('barrelstrength\\sproutforms\\elements\\Entry', Craft.SproutForms.EntriesIndex);