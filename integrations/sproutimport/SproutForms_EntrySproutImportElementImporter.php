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
	 * @return mixed
	 */
	public function getModelName()
	{
		return 'SproutForms_Entry';
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