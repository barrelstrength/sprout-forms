(function($)
{
	/**
	 * QuickField class
	 * Handles the buttons for creating new groups and fields inside a FieldLayoutDesigner
	 */
	var QuickField = Garnish.Base.extend({

		$container:   null,
		$groupButton: null,
		$fieldButton: null,
		$settings:    null,

		fld:          null,
		modal:        null,

		/**
		 * The constructor.
		 *
		 * @param fld - An instance of Craft.FieldLayoutDesigner
		 */
		init: function(fld)
		{
			if(!(fld instanceof Craft.FieldLayoutDesigner))
			{
				// Fail silently - just means the quick field feature will not be initialised, no big deal
				return;
			}

			this.fld = fld;
			this.fld.$container.addClass('quick-field');

			this.$container   = $('<div class="newfieldbtn-container">').insertAfter($(".fld-tabs"));

			this.$fieldButton = $('<div class="btn add icon" tabindex="0">').text(Craft.t('New Field')).appendTo($(".buttons"));

			this.modal  = QuickField.FieldModal.getInstance();

			this.addListener(this.$fieldButton, 'activate', 'newField');

			this.modal.on('newField', $.proxy(function(e)
			{
				var field = e.field;
				var group = field.group;
				this.addField(field.id, field.name, group.name);
			}, this));

			this.modal.on('saveField', $.proxy(function(e)
			{
				var field = e.field;
				var group = field.group;
				this.resetField(field.id, group.name, field.name);
			}, this));
		},

		/**
		 * Event handler for the New Field button.
		 * Creates a modal window that contains new field settings.
		 */
		newField: function()
		{
			this.modal.show();
		},

		/**
		 * Adds a new unused (dashed border) field to the field layout designer.
		 *
		 * @param id
		 * @param name
		 * @param groupName
		 */
		addField: function(id, name, groupName)
		{
			var fld = this.fld;
			var grid = fld.tabGrid;
			var drag = fld.fieldDrag;
			var fields = fld.$allFields;
			var $group = this._getGroupByName(groupName);

			if($group)
			{
				var $groupContent = $group.children('.fld-tabcontent');
				var $field = $([
					'<div class="fld-field" data-id="', id, '">',
						'<span>', name, '</span>',
						'<input class="id-input" type="hidden" name="fieldLayout[',groupName,'][]" value="',id,'">',
						'<a class="settings icon" title="Edit"></a>',
					'</div>'
				].join('')).appendTo($groupContent);

				fld.initField($field);

				fld.$allFields = fields.add($field);

				$group.removeClass('hidden');
				drag.addItems($field);
				grid.refreshCols(true);

				Craft.cp.displayNotice(Craft.t('New field created.'));
			}
			else
			{
				// New field without tab or new field with renamed unsaved tab let's just display a message
				Craft.cp.displayError(Craft.t('Please Save the form after you rename any tab.'));
				//Save the form does not work because the field is not added to the tab.
			}
		},

		/**
		 * Renames and regroups an existing field on the field layout designer.
		 *
		 * @param id
		 * @param groupName
		 * @param name
		 */
		resetField: function(id, groupName, name)
		{
			var fld = this.fld;
			var grid = fld.unusedFieldGrid;
			var $container = fld.$container;
			var $group = this._getGroupByName(groupName);
			var $content = $group.children('.fld-tabcontent');
			var $field = $container.find('.fld-field[data-id="' + id + '"]');
			var $unusedField = $field.filter('.unused');
			var $currentGroup = $unusedField.closest('.fld-tab');
			var $span = $field.children('span');

			$span.text(name);

			if($currentGroup[0] !== $group[0])
			{
				$content.append($unusedField);
				grid.refreshCols(true);
			}
		},

		/**
		 * Finds the group tab element from it's name.
		 *
		 * @param name
		 * @returns {*}
		 * @private
		 */
		_getGroupByName: function(name)
		{
			var $container = this.fld.$tabContainer;
			var $groups = $container.children('.fld-tab');
			var $group = null;

			$groups.each(function()
			{
				var $this = $(this);
				var $tab = $this.children('.tabs').children('.tab.sel');
				var $span = $tab.children('span');
				if($span.text() === name)
				{
					$group = $this;
					return false;
				}
			});

			return $group;
		}
	});

	window.QuickField = QuickField;

})(jQuery);
