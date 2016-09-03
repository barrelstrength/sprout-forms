<?php

namespace Craft;

class SproutForms_FormsSproutImportFieldImporter extends BaseSproutImportFieldImporter
{
	/**
	 * @return string
	 */
	public function getModelName()
	{
		return 'SproutForms_Forms';
	}

	/**
	 * @return mixed
	 */
	public function getMockData()
	{
		$settings   = $this->model->settings;
		$limit      = sproutImport()->mockData->getLimit($settings['limit'], 1);
		$sources    = $settings['sources'];
		$attributes = array();

		$groupIds = sproutImport()->mockData->getElementGroupIds($sources);

		if (!empty($groupIds) and $groupIds != '*')
		{
			$attributes = array(
				'groupId' => $groupIds
			);
		}

		$elementIds = sproutImport()->mockData->getMockRelations("SproutForms_Form", $attributes, $limit);

		return $elementIds;
	}
}