(function($)
{
	/**
	 * SproutField class
	 * Handles the buttons for creating new groups and fields inside a FieldLayoutDesigner
	 */
	var SproutField = Garnish.Base.extend({

		$container: null,
		$groupButton: null,
		$fieldButton: null,
		$settings: null,

		fld: null,
		modal: null,
		formLayout: null,
		fieldsLayout: null,

		/**
		 * The constructor.
		 *
		 * @param fld - An instance of Craft.FieldLayoutDesigner
		 */
		init: function()
		{
			var that = this;

			//this.initButtons();
			this.modal = SproutField.FieldModal.getInstance();

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
				// @todo - we need update the icon or div if the
				// field type is changed after saved, not just the name
				// think the how we handle the tabs
				//this.resetField(field.id, group.name, field.name);
			}, this));

			// DRAGULA
			this.formLayout  = this.getId('left-copy');
			this.fieldsLayout = this.getId('right-copy');
			// Drag from right to left
			dragula([this.formLayout, this.fieldsLayout], {
				copy: function (el, source) {
					return source === that.fieldsLayout;
				},
				accepts: function (el, target) {
					return target !== that.fieldsLayout
				},
			})
			.on('drop', function (el,target, source) {
				if (target && source === that.fieldsLayout)
				{
					// get the tab name by the first div fields
					var tab       = $(el).closest("#sproutforms-tab");
					var tabName   = tab.data('tabname');
					var tabId     = tab.data('tabid');
					var fieldType = $(el).data("type");

					that.createDefaultField(fieldType, tabId, tabName, el);
				}
			});
		},

		createDefaultField: function(type, tabId, tabName, el)
		{
			var defaultField = null;

			var formId = $("#formId").val();
			var data = {
				'type': type,
				'formId': formId,
				'tabId': tabId
			};

			Craft.postActionRequest('sprout-forms/fields/create-field', data, $.proxy(function(response, textStatus)
			{
				if (textStatus === 'success')
				{
					this.initFieldOnDrop(response.field, tabName, el);
				}
			}, this));
		},

		initFieldOnDrop: function(defaultField, tabName, el)
		{
			if(defaultField != null && defaultField.hasOwnProperty("id"))
			{
				$(el).attr('data-id', defaultField.id);
				// let's update the dragula fields divs
				var $field = $([
					'<ul class="settings">',
					'<span>', name, '</span>',
					'<input class="id-input" type="hidden" name="fieldLayout[',tabName,'][]" value="', defaultField.id, '">',
					'<li><a id="field-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#">edit</a></li>',
					'<li><a href="#">delete</a></li>',
					'</ul>'
				].join('')).appendTo($(el));

				this.addListener($("#field-"+defaultField.id), 'activate', 'editField');

				console.log($(el).data("type"));
			}
			else
			{
				Craft.cp.displayError(Craft.t('sproutforms','Something went wrong when creating the field :('));

				$(el).remove();
			}
		},

		getId: function(id)
		{
			return document.getElementById(id);
		},

		/**
		 * Adds edit buttons to existing fields.
		 */
		initButtons: function()
		{
			var that = this;

			var $fields = this.fld.$container.find('.fld-field');

			$fields.each(function()
			{
				var $field = $(this);

				var $editBtn = $field.find('.settings');

				new Garnish.MenuBtn($editBtn, {
					onOptionSelect: $.proxy(that, 'onFieldOptionSelect')
				});

			});
		},

		onFieldOptionSelect: function(option)
		{
			var $option = $(option),
					$field  = $option.data('menu').$anchor.parent(),
					action  = $option.data('action');

			switch (action)
			{
				case 'toggle-required':
				{
					this.fld.toggleRequiredField($field, $option);
					break;
				}
				case 'remove':
				{
					this.fld.removeField($field);
					break;
				}
				case 'edit':
				{
					this.editField($field);
					break;
				}
			}
		},

		/**
		 * Event handler for the New Field button.
		 * Creates a modal window that contains new field settings.
		 */
		newField: function()
		{
			this.modal.show();
		},

		editField: function(option)
		{
			var option = option.currentTarget;

			var fieldId = $(option).data('fieldid');
			// Make our field available to our parent function
			this.$field = $(option);
			this.base($(option));

			this.modal.editField(fieldId);
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
			var fld    = this.fld;
			var grid   = fld.tabGrid;
			var drag   = fld.fieldDrag;
			var fields = fld.$allFields;
			var $group = this._getGroupByName(groupName);

			if ($group)
			{
				var $groupContent   = $group.children('.fld-tabcontent');
				var encodeGroupName = encodeURIComponent(groupName);
				var $field = $([
					'<div class="fld-field" data-id="', id, '">',
					'<span>', name, '</span>',
					'<input class="id-input" type="hidden" name="fieldLayout[', encodeGroupName, '][]" value="', id, '">',
					'<a class="settings icon" title="Edit"></a>',
					'</div>'
				].join('')).appendTo($groupContent);

				fld.initField($field);

				this.addFieldListener($field);

				fld.$allFields = fields.add($field);

				$group.removeClass('hidden');
				drag.addItems($field);
				grid.refreshCols(true);

				Craft.cp.displayNotice(Craft.t('sproutforms','New field created.'));
			}
			else
			{
				// New field without tab or new field with renamed unsaved tab let's just display a message
				Craft.cp.displayError(Craft.t('sproutforms','Please Save the form after you rename any tab.'));
				//Save the form does not work because the field is not added to the tab.
			}
		},

		addFieldListener: function($field)
		{
			var $editBtn = $field.find('.settings');

			new Garnish.MenuBtn($editBtn, {
				onOptionSelect: $.proxy(this, 'onFieldOptionSelect')
			});
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
			var grid = fld.tabGrid;
			var $container = fld.$container;
			var $group = this._getGroupByName(groupName);
			var $content = $group.children('.fld-tabcontent');
			var $field = $container.find('.fld-field[data-id="' + id + '"]');
			var $currentGroup = $field.closest('.fld-tab');
			var $span = $field.children('span');

			$span.text(name);

			if ($currentGroup[0] !== $group[0])
			{
				$content.append($field);
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
				if ($span.text() === name)
				{
					$group = $this;
					return false;
				}
			});

			return $group;
		}
	});

	window.SproutField = SproutField;

})(jQuery);
