/*!
 * Manage our groups
 * 
 * Based off the Craft fields.js file
 */

// (function($) {

var FormFieldLayoutDesigner = Garnish.Base.extend({}, Craft.FieldLayoutDesigner, {

	dog: "cat",

	// init: function()
	// {
	// 	console.log(this);
	// }
	// 
	initField: function($field) {
		
		var $editBtn = $field.find('.settings'),
			$menu = $('<div class="menu" data-align="center"/>').insertAfter($editBtn),
			$ul = $('<ul/>').appendTo($menu);

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

	}

});

// new FormFieldLayoutDesigner();

// })(jQuery);