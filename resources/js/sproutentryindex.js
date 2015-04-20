var EntryIndex = Craft.BaseElementIndex.extend();

EntryIndex.prototype.getDefaultSort = function()
{
	return ['dateCreated', 'desc'];
};

Craft.registerElementIndexClass('SproutForms_Entry', EntryIndex);
