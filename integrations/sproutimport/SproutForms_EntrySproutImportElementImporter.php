<?php
namespace Craft;

class SproutForms_EntrySproutImportElementImporter extends BaseSproutImportElementImporter
{
	/**
	 * @return mixed
	 */
	public function defineModel()
	{
		return 'SproutForms_EntryModel';
	}

	/**
	 * @return bool
	 * @throws Exception
	 * @throws \Exception
	 */
	public function save()
	{
		return craft()->sproutForms_entries->saveEntry($this->model);
	}

	public function getAllFieldHandles()
	{
		$fields = $this->model->getFields();

		$handles = array();
		if (!empty($fields))
		{
			foreach ($fields as $field)
			{
				$handles[] = $field->handle;
			}
		}

		return $handles;
	}
}