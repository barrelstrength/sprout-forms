(function($) {


var FormFieldsAdmin = Garnish.Base.extend(
{
	$formId: null,
	fieldLayoutTabs: null,

	init: function()
	{
		this.$formId = $('#formFields').data('formid');

		this.fieldLayoutTabs = $.map($('#formFields tr.field'), function(el) {
			return { 
				section: $(el).data('fieldlayout'),
				fieldId: $(el).data('id'),
			}
		});

		this.addListener($('#newSection'), 'activate', 'createNewSection');
	},

	createNewSection: function()
	{
		var name = this.promptForSectionName('');

		if (name)
		{
			var data = {
				name: name,
				formId: this.$formId,
				fieldLayoutTabs: this.fieldLayoutTabs
			};

			Craft.postActionRequest('sproutForms/forms/saveFormFields', data, $.proxy(function(response, textStatus)
			{
				if (textStatus == 'success')
				{
					if (response.success)
					{
						// location.href = Craft.getUrl('sproutforms/forms/edit/' + response.section.id + '#fields');
						location.href = Craft.getUrl('sproutforms/forms/edit/2422#fields');
					}
					else if (response.errors)
					{
						var errors = this.flattenErrors(response.errors);
						alert(Craft.t('Could not create the section:')+"\n\n"+errors.join("\n"));
					}
					else
					{
						Craft.cp.displayError();
					}
				}

			}, this));
		}
	},

	promptForSectionName: function(oldName)
	{
		return prompt(Craft.t('What do you want to name your section?'), oldName);
	},

});


Garnish.$doc.ready(function()
{
	Craft.FormFieldsAdmin = new FormFieldsAdmin();
});


})(jQuery);
