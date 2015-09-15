/**
 *@author: Andre L.
 *@email: andre@barrelstrengthdesign.com
 *
*/
function setCustomAttributes(json, id)
{
	if(json)
	{
		var fieldSettings = JSON.parse(json);
		// set dynamically html5 data attributes
		for (var key in fieldSettings)
		{
			document.getElementById('fields-'+id).setAttribute('data-'+key, fieldSettings[key]);
		}
	}
}