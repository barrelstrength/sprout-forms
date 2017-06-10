function checkSproutEmailField(id, elementId, fieldHandle, fieldContext) {

	var sproutEmailFieldId = '#' + id;
	var sproutEmailButtonClass = '.' + id;

	// We use setTimeout to make sure our function works every time
	setTimeout(function() 
	{
		// Set up data for the controller.
		var data = {
			'elementId': elementId,
			'fieldContext': fieldContext,
			'fieldHandle': fieldHandle,
			'value': $(sproutEmailFieldId).val()
		};

		// Query the controller so the regex validation is all done through PHP.
		Craft.postActionRequest('sprout-fields/sprout-fields/email-validate', data, function(response) {
			if (response)
			{
				$(sproutEmailButtonClass).addClass('fade');
				$(sproutEmailButtonClass).html('<a href="mailto:' + data.value + '" target="_blank" class="sproutfields-icon">&#xf0e0;</a>');
			}
			else
			{
				$(sproutEmailButtonClass).removeClass('fade');
			}
		});

	}, 500);
}