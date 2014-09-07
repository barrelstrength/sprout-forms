/*!
 * Manage our Form Fields and Layout
 *
 * Based off similar FieldLayoutDesigner code in the craft.js file
 */

(function($) {

if (typeof Sprout == 'undefined')
{
	Sprout = {};
}

// new Craft.FieldLayoutDesigner('#fieldlayoutform', {
// 	customizableTabs: true
// });

Sprout.FormFieldLayoutDesigner = Craft.FieldLayoutDesigner.extend({

	formId: null,

	// constructor: function(name) {
 //    this.name = name;
 //  },

	init: function(container, settings) 
	{
		// ------------------------------------------------------------
		// @TODO - how do I just call the parent init() method here?
		// right now, I'm copying the whole method here and adding to it
		// ------------------------------------------------------------
		
		this.$container = $(container);
		this.setSettings(settings, Craft.FieldLayoutDesigner.defaults);

		this.$tabContainer = this.$container.children('.fld-tabs');
		this.$unusedFieldContainer = this.$container.children('.unusedfields');
		this.$newTabBtn = $('#newtabbtn');
		this.$allFields = this.$unusedFieldContainer.find('.fld-field');

		// Set up the layout grids
		this.tabGrid = new Craft.Grid(this.$tabContainer, Craft.FieldLayoutDesigner.gridSettings);
		this.unusedFieldGrid = new Craft.Grid(this.$unusedFieldContainer, Craft.FieldLayoutDesigner.gridSettings);

		var $tabs = this.$tabContainer.children();
		for (var i = 0; i < $tabs.length; i++)
		{
			this.initTab($($tabs[i]));
		}

		this.fieldDrag = new Craft.FieldLayoutDesigner.FieldDrag(this);

		if (this.settings.customizableTabs)
		{
			this.tabDrag = new Craft.FieldLayoutDesigner.TabDrag(this);

			this.addListener(this.$newTabBtn, 'activate', 'addTab');
		}

		// ------------------------------------------------------------
		// End copied init() parent method
		// ------------------------------------------------------------

		this.addListener($('#newfieldbtn a'), 'activate', 'confirmSavedChanges');
	},
	
	confirmSavedChanges: function(event) 
	{
		var response = confirm(Craft.t('Be sure to save any changes you made to your form before creating or editing a field. All changes to your form and field layout that have not been saved will be lost when you navigate away from this page.'));

		if (response == true) 
		{ 
			window.location = event.target.href;
		}	
		else
		{
			return false;
		}
	},
		
	initField: function($field)
	{
		var $editBtn = $field.find('.settings'),
			$menu = $('<div class="menu" data-align="center"/>').insertAfter($editBtn),
			$ul = $('<ul/>').appendTo($menu);

		this.formId = $('#form').data('formid');
		var fieldId = $field.data('id');

		var editUrl = Craft.getCpUrl('sproutforms/forms/'+this.formId+'/fields/edit/'+fieldId);
		
		$('<li><a href="' + editUrl + '" data-action="edit">'+Craft.t('Edit')+'</a></li>').appendTo($ul);

		if ($field.hasClass('fld-required'))
		{
			$('<li><a data-action="toggle-required">'+Craft.t('Make not required')+'</a></li>').appendTo($ul);
		}
		else
		{
			$('<li><a data-action="toggle-required">'+Craft.t('Make required')+'</a></li>').appendTo($ul);
		}

		$('<li><a data-action="remove">'+Craft.t('Remove')+'</a></li>').appendTo($ul);

		new Garnish.MenuBtn($editBtn, {
			onOptionSelect: $.proxy(this, 'onFieldOptionSelect')
		});
	},

	onFieldOptionSelect: function(option)
	{
		var $option = $(option),
			$field = $option.data('menu').$trigger.parent(),
			action = $option.data('action');

		switch (action)
		{
			case 'edit':
			{
				this.confirmSavedChanges();
				break;
			}
			case 'toggle-required':
			{
				this.toggleRequiredField($field, $option);
				break;
			}
			case 'remove':
			{
				this.removeField($field);
				break;
			}
		}
	},

	removeField: function($field)
	{
		// Make our field available to our parent function
		this.$field = $field;
		this.base($field);

		// Grab the fieldId in this context so we know what to delete
		var fieldId = this.$field.attr('data-id');

		// Added behavior, store an array of deleted field IDs
		// that will be processed by the sproutForms/forms/saveForm method
		$deletedFieldsContainer = $('#deletedFieldsContainer');
		$('<input type="hidden" name="deletedFields[]" value="'+fieldId+'">').appendTo($deletedFieldsContainer);
	},

});

})(jQuery);