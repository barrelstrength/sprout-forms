<?php
namespace Craft;

class SproutForms_EntrySproutImportElementImporter extends BaseSproutImportElementImporter
{
	/**
	 * @return mixed
	 */
	public function getModel()
	{
		$model = 'Craft\\SproutForms_EntryModel';

		return new $model;
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
}