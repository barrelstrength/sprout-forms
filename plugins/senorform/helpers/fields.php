<?php 

/*
 * ------------------------------------------------------
*  The following functions return form fields
* ------------------------------------------------------
*/

/**
 * Returns <select>
 *
 * @param SenorForm_FieldModel $obj
 * @param bool if true, return default wrapping html including error div, label, etc.
 * @return string
 */
function _sf_dropdown($obj, $include_all = false)
{
	$options = array('<option value="">Please Select</option>');

	if( ! empty($obj->settings['options']))
	{
		foreach($obj->settings['options'] as $k=>$v)
		{
			// hack $_POST
			$options[] = '<option value="' . $v['value'] . '" ' . (isset($_POST[$obj->handle]) && $_POST[$obj->handle] == $v['label'] ? ' selected="selected" ' : '') . '>' . $v['label'] . '</option>';
		}
	}
	
	$select = '<select name="' . $obj->handle . '">' . implode("\r\n", $options) . '</select>';
	
	if( ! $include_all)
	{
		return array('html' => $select);
	}

	$out['html'] = $select;
	return $out;
}

/**
 * Returns <input type="checkbox">
 *
 * @param SenorForm_FieldModel $obj
 * @param bool if true, return default wrapping html including error div, label, etc.
 * @return string
 */
function _sf_checkboxes($obj, $include_all = false)
{
	$checkboxes = array();
	if( ! empty($obj->settings['options']))
	{
		foreach($obj->settings['options'] as $k=>$v)
		{
			// hack $_POST
			$checkboxes[] = '<label class="checkbox-label" ><input type="checkbox" name="' . $obj->handle . '[' . $k . ']" value="' . $v['value'] . '" ' . (isset($_POST[$obj->handle]) && in_array($v['label'], $_POST[$obj->handle]) ? ' checked="checked" ' : '') . '>' . $v['label'] . '</label>';
		}
	}

	$checkboxes = implode(" ", $checkboxes);

	if( ! $include_all)
	{
		return array('html' => $checkboxes);
	}

	$out['html'] = $checkboxes;
	return $out;
}

/**
 * Returns <input type="radio">
 *
 * @param SenorForm_FieldModel $obj
 * @param bool if true, return default wrapping html including error div, label, etc.
 * @return string
 */
function _sf_radiobuttons($obj, $include_all = false)
{
	$buttons = array();
	if( ! empty($obj->settings['options']))
	{
		foreach($obj->settings['options'] as $k=>$v)
		{
			// hack $_POST
			$buttons[] = '<label class="radio-label" ><input type="radio" name="' . $obj->handle . '[' . $k . ']" value="' . $v['value'] . '" ' . (isset($_POST[$obj->handle]) && in_array($v['label'], $_POST[$obj->handle]) ? ' checked="checked" ' : '') . '>' . $v['label'] . '</label>';
		}
	}

	$buttons = implode(" ", $buttons);

	if( ! $include_all)
	{
		return array('html' => $buttons);
	}

	$out['html'] = $buttons;
	return $out;
}

/**
 * Returns <input type="text">
 *
 * @param SenorForm_FieldModel $obj
 * @param bool if true, return default wrapping html including error div, label, etc.
 * @return string
 */
function _sf_plaintext($obj, $include_all = false)
{
	$plaintext = '<input type="text" name="' . $obj->handle . '" value="' . (isset($_POST[$obj->handle]) ? $_POST[$obj->handle] : ( isset($obj->settings['hint']) ? $obj->settings['hint'] : '') ) . '" />';

	if( ! $include_all)
	{
		return array('html' => $plaintext);
	}

	$out['html'] = $plaintext;
	return $out;
}