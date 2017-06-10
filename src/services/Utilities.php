<?php
namespace barrelstrength\sproutforms\services;

use craft\base\Component;
use craft\base\Element;

class Utilities extends Component
{
	/**
	 * Returns current Field Type context to properly get field settings
	 *
	 * @param $field Email Field Object
	 * @param Element $element
	 *
	 * @return string
	 */
	public function getFieldContext($field, Element $element)
	{
		$context = 'global';

		if ($field->context)
		{
			$context = $field->context;
		}

		if ($element)
		{
			$context = $element->getFieldContext();
		}

		return $context;
	}

	public function isAnyOptionsSelected($options, $value = null)
	{
		if (!empty($options))
		{
			foreach ($options as $option)
			{
				if ($option->selected == true || ($value != null && $value == $option->value))
				{
					return true;
				}
			}
		}

		return false;
	}
}

