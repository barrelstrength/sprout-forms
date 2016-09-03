<?php

namespace Craft;

class SproutForms_EntrySproutImportFieldImporter extends BaseSproutImportFieldImporter
{
	/**
	 * @return string
	 */
	public function getModelName()
	{
		return 'SproutForms_Entry';
	}

	/**
	 * @return mixed
	 */
	public function getMockData()
	{
		$settings   = $this->model->settings;
		$limit      = sproutImport()->mockData->getLimit($settings['limit']);
		$sources    = $settings['sources'];
		$attributes = array();

		$formIds = sproutImport()->mockData->getElementGroupIds($sources);

		if (!empty($groupIds) and $groupIds != '*')
		{
			$attributes = array(
				'formId' => $formIds
			);
		}

		$elementIds = sproutImport()->mockData->getMockRelations("SproutForms_Entry", $attributes, $limit);

		return $elementIds;
	}
}