(function($)
{
	/**
	 * SproutField class
	 * Handles the buttons for creating new groups and fields inside a the drag and drop UI
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
		// The dragula instance
		drake: null,

		/**
		 * The constructor.
		 * @param - All the tabs of the form fieldLayout.
		 */
		init: function(tabs)
		{
			var that = this;

			this.initButtons();
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
				this.resetField(field, group);
			}, this));

			// DRAGULA
			this.formLayout  = this.getId('left-copy');
			this.fieldsLayout = this.getId('right-copy');
			// Drag from right to left
			this.drake = dragula([this.formLayout, this.fieldsLayout], {
				copy: function (el, source) {
					return source === that.fieldsLayout;
				},
				accepts: function (el, target) {
					return target !== that.fieldsLayout
				},
			})
			.on('drop', function (el,target, source) {
				// Reorder fields
				if ($(target).attr("id") == $(source).attr("id"))
				{
					// just if we need check when the field is reorder
					// not needed because the order is saved from the hidden field
					// when the form is saved
				}
				if (target && source === that.fieldsLayout)
				{
					// get the tab name by the first div fields
					var tab       = $(el).closest(".sproutforms-tab-fields");
					var tabName   = tab.data('tabname');
					var tabId     = tab.data('tabid');
					var fieldType = $(el).data("type");

					that.createDefaultField(fieldType, tabId, tabName, el);
				}
			});

			// Add the drop containers for each tab
			for (var i = 0; i < tabs.length; i++)
			{
				this.drake.containers.push(this.getId('sproutforms-tab-container-'+tabs[i].id));
			}
			// manually - remove when add tabs dynamically
			this.drake.containers.push(this.getId('left-copy-2'));
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
				$(el).attr('id', 'sproutfield-'+defaultField.id);
				// Lets update the the name and icon
				$(el).find('.sproutforms-icon').html(defaultField.icon);
				$(el).find('label').html(defaultField.name);
				// let's add the settings urls
				var $field = $([
					'<ul class="settings">',
					'<span>', name, '</span>',
					'<li><a id="sproutform-field-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#">edit</a></li>',
					'<li><a href="#">delete</a></li>',
					'</ul>',
					'<input class="id-input" type="hidden" name="fieldLayout[',tabName,'][]" value="', defaultField.id, '">'
				].join('')).appendTo($(el));

				this.addListener($("#sproutform-field-"+defaultField.id), 'activate', 'editField');

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
			// @todo - add the delete actions
			var that = this;
			// get all the links stars with sproutform-field-
			$("a[id^='sproutform-field-']").each(function (i, el) {
				var fieldId = $(el).data('fieldid');

				if(fieldId)
				{
					that.addListener($("#sproutform-field-"+fieldId), 'activate', 'editField');
				}
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
		 * Renames | update icon | move field to another tab
		 * of an existing field after edit it
		 *
		 * @param field
		 * @param group
		 */
		resetField: function(field, group)
		{
			var el = $("#sproutfield-"+field.id);
			// Lets update the the name and icon
			$(el).find('.sproutforms-icon').html(field.icon);
			$(el).find('label').html(field.name);
			// Check if we need move the field to another tab
			var tab       = $(el).closest(".sproutforms-tab-fields");
			var tabName   = tab.data('tabname');
			var tabId     = tab.data('tabid');

			if (tabName != group.name)
			{
				// let's remove the hidden field just if the user change the tab
				$(el).find('.id-input').remove();
				// create the new hidden field and add it to the field div
				var $field = $([
					'<input class="id-input" type="hidden" name="fieldLayout[',group.name,'][]" value="', field.id, '">'
				].join('')).appendTo($(el));
				// move the field to another tab
				var newTab = $("#sproutforms-tab-container-"+group.id);
				// we need this?
				var copyEl = el;
				$(el).remove();
				$(copyEl).appendTo($(newTab));
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
