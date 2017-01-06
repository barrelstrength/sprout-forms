<?php
namespace Craft;

class SproutForms_EntrySproutImportElementImporter extends BaseSproutImportElementImporter
{

	/**
	 * @return null|string
	 */
	public function getName()
	{
		return Craft::t("Sprout Forms Entries");
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
	 */
	public function hasSeedGenerator()
	{
		return true;
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

	/**
	 * @return string
	 */
	public function getSettingsHtml()
	{
		$forms = sproutForms()->forms->getAllForms();

		$formOptions[''] = Craft::t("Select a form...");

		foreach ($forms as $form)
		{
			$formOptions[$form->id] = $form->name;
		}

		return craft()->templates->render('sproutforms/_integrations/sproutimport/entries/settings', array(
			'id'          => $this->getModelName(),
			'formOptions' => $formOptions
		));
	}

	/**
	 * Generate mock data for a Channel or Structure.
	 *
	 * Singles are not supported.
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function getMockData($quantity, $settings)
	{
		$saveIds = array();
		$formId  = $settings['formId'];

		$form = sproutForms()->forms->getFormById($formId);

		if (!empty($quantity))
		{
			for ($i = 1; $i <= $quantity; $i++)
			{
				$fakerDate = $this->fakerService->dateTimeThisYear('now');

				$formEntry              = new SproutForms_EntryModel();
				$formEntry->formId      = $form->id;
				$formEntry->ipAddress   = "127.0.0.1";
				$formEntry->userAgent   = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36";
				$formEntry->dateCreated = date("Y-m-d H:i:s",$fakerDate->getTimestamp());
				$formEntry->dateUpdated = date("Y-m-d H:i:s",$fakerDate->getTimestamp());

				$fieldTypes = $form->getFields();

				$fields = $this->getFieldsWithMockData($fieldTypes);

				$formEntry->setContentFromPost($fields);

				sproutForms()->entries->saveEntry($formEntry);

				// Avoid duplication of saveIds
				if (!in_array($formEntry->id, $saveIds) && $formEntry->id !== false)
				{
					$saveIds[] = $formEntry->id;
				}
			}
		}

		return $saveIds;
	}

	public function getFieldsWithMockData($fields)
	{
		$fieldsWithMockData = array();

		if (!empty($fields))
		{
			foreach ($fields as $field)
			{
				$fieldHandle        = $field->handle;
				$fieldType          = $field->type;
				$fieldImporterClass = sproutImport()->getFieldImporterClassByType($fieldType);

				if ($fieldImporterClass != null)
				{
					$fieldImporterClass->setModel($field);

					$fieldsWithMockData[$fieldHandle] = $fieldImporterClass->getMockData();
				}
			}
		}

		return $fieldsWithMockData;
	}

	/**
	 * @return array
	 */
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