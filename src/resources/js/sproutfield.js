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
		$pane: null,

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
		init: function(currentTabs)
		{
			var that = this;

			this.$pane = new Craft.Pane($(".pane"));

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
			for (var i = 0; i < currentTabs.length; i++)
			{
				this.drake.containers.push(this.getId('sproutforms-tab-container-'+currentTabs[i].id));
			}
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
					'<span id="toggle-required"> </span>',
					'<ul class="settings">',
					'<span>', name, '</span>',
					'<li><a id="sproutform-field-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#">edit</a></li>',
					'<li><a id="sproutform-remove-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#">remove</a></li>',
					'<li><a id="sproutform-required-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#">Make required</a></li>',
					'</ul>',
					'<input class="id-input" type="hidden" name="fieldLayout[',tabName,'][]" value="', defaultField.id, '">'
				].join('')).appendTo($(el));

				this.addListener($("#sproutform-field-"+defaultField.id), 'activate', 'editField');
				this.addListener($("#sproutform-remove-"+defaultField.id), 'activate', 'removeField');
				this.addListener($("#sproutform-required-"+defaultField.id), 'activate', 'toggleRequiredField');

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
					that.addListener($("#sproutform-remove-"+fieldId), 'activate', 'removeField');
					that.addListener($("#sproutform-required-"+fieldId), 'activate', 'toggleRequiredField');
				}
			});

			// get all the delete buttons
			$("a[id^='delete-tab-']").each(function (i, el) {
				var tabId = $(el).data('tabid');

				if(tabId)
				{
					that.addListener($("#delete-tab-"+tabId), 'activate', 'deleteTab');
				}
			});

			// listener to the new tab button
			this.addListener($("#sproutforms-add-tab"), 'activate', 'addNewTab');
		},

		deleteTab: function(option)
		{
			var option = option.currentTarget;
			var tabId  = $(option).data('tabid');
			var userResponse = this.confirmDeleteTab();

			if (userResponse)
			{
				$("#sproutforms-tab-"+tabId).slideUp(500, function() { $(this).remove(); });
				$("#tab-"+tabId).closest( "li" ).slideUp(500, function() { $(this).remove(); });
			}
		},

		addNewTab: function()
		{
			var newName = this.promptForGroupName('');
			var response = true;
			var $tabs = $("[id^='sproutforms-tab-']");
			var formId = $("#formId").val();
			// validate with current names and set the sortOrder
			$tabs.each(function (i, el) {
				var tabname = $(el).data('tabname');

				if(tabname == newName)
				{
					response = false;
					return false;
				}
			});

			if (response && newName && formId)
			{
				var data = {
					name: newName,
					// Minus the add tab button
					sortOrder: $tabs.length,
					formId : formId
				};

				Craft.postActionRequest('sprout-forms/fields/add-tab', data, $.proxy(function(response, textStatus)
				{
					if (response.success)
					{
						var tab = response.tab;
						Craft.cp.displayNotice(Craft.t('sproutforms','Tab: '+tab.name+' created'));
						// first insert the new tab before the add tab button
						var href = '#sproutforms-tab-'+tab.id;
						$('<li><a id="tab-'+tab.id+'" class="tab" href="'+href+'" tabindex="0">'+tab.name+'</a></li>').insertBefore("#sproutforms-add-tab");
						var $newDivTab = $('#tab-'+tab.id);

						var $dropDiv = $([
							'<div id="sproutforms-tab-'+tab.id+'" data-tabname="'+tab.name+'" data-tabid="'+tab.id+'" class="hidden sproutforms-tab-fields">',
							'<div class="parent">',
							'<h1>Drag and drop here</h1>',
							'<a id="delete-tab-'+tab.id+'" data-tabid="'+tab.id+'">Delete tab</a>',
							'<div class="sprout-wrapper">',
							'<div id="sproutforms-tab-container-'+tab.id+'" class="sprout-container">',
							'</div>',
							'</div>',
							'</div>',
							'</div>'
						].join('')).appendTo($("#sproutforms-main-container"));
						// Convert our new tab into dragula vampire :)
						this.drake.containers.push(this.getId('sproutforms-tab-container-'+tab.id));

						this.$pane.tabs[href] = {
								$tab: $newDivTab,
								$target: $(href)
						};

						this.$pane.addListener($newDivTab, 'activate', 'selectTab');
						this.addListener($("#delete-tab-"+tab.id), 'activate', 'deleteTab');
					}
					else
					{
						console.log(response.errors);
						Craft.cp.displayError(Craft.t('sproutforms','Unable to create a new Tab'));
					}
				}, this));

			}
			else
			{
				Craft.cp.displayError(Craft.t('sproutforms','Wrong Tab Name - Please try again'));
			}

		},

		promptForGroupName: function(oldName)
		{
			return prompt(Craft.t('sproutforms','What do you want to name your new Tab?'), oldName);
		},

		confirmDeleteTab: function()
		{
			return confirm("Are you sure you want to delete this tab, all of it's fields, and all of it's data?");
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

		removeField: function(option)
		{
			var option = option.currentTarget;

			var fieldId = $(option).data('fieldid');

			//Remove the div of the field
			$("#sproutfield-"+fieldId).slideUp(500, function() { $(this).remove(); });

			// Added behavior, store an array of deleted field IDs
			// that will be processed by the sproutForms/forms/saveForm method
			$deletedFieldsContainer = $('#deletedFieldsContainer');
			$('<input type="hidden" name="deletedFields[]" value="' + fieldId + '">').appendTo($deletedFieldsContainer);
		},

		toggleRequiredField: function(option)
		{
			var option      = option.currentTarget;
			var fieldId     = $(option).data('fieldid');
			var $divField   = $("#sproutfield-"+fieldId);
			var $toggleLink = $("#sproutform-required-"+fieldId);

			var $field = $divField.find("#toggle-required");
			if ($field.hasClass('sproutfield-required'))
			{
				$field.removeClass('sproutfield-required');
				$field.find('.required-input').remove();

				setTimeout(function() {
					$toggleLink.text(Craft.t('sproutforms', 'Make required'));
				}, 500);
			}
			else
			{
				$field.addClass('sproutfield-required');
				$('<input class="required-input" type="hidden" name="requiredFields[]" value="' + fieldId + '">').appendTo($field);

				setTimeout(function() {
					$toggleLink.text(Craft.t('sproutforms', 'Make not required'));
				}, 500);
			}
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
				// move element to new div - like ctrl+x
				$(el).detach().appendTo($(newTab));
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
