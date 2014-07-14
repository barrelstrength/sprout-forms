<?php
namespace Craft;

/**
 * Craft by Pixel & Tonic
 *
 * @package   Craft
 * @author    Pixel & Tonic, Inc.
 * @copyright Copyright (c) 2014, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @link      http://buildwithcraft.com
 */

/**
 * Matrix block element type
 */
class SproutForms_EntryElementType extends BaseElementType
{
	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Form Entries');
	}

	/**
	 * Returns whether this element type has content.
	 *
	 * @return bool
	 */
	public function hasContent()
	{
		return true;
	}

	/**
	 * Returns whether this element type stores data on a per-locale basis.
	 *
	 * @return bool
	 */
	public function isLocalized()
	{
		return false;
	}

	/**
	 * Returns this element type's sources.
	 *
	 * @param string|null $context
	 * @return array|false
	 */
	public function getSources($context = null)
	{
		// Start with an option for everything
		$sources = array(
			'*' => array(
				'label'    => Craft::t('All Entries'),
			)
		);

		// @TODO 
		// - figure out how to handle multiple content tables in the index page results
		// - figure out how to get these sorted by groupId
		// 
		// Prepare the data for our sources sidebar
		$groups = craft()->sproutForms_groups->getAllFormGroups('id');
		$forms = craft()->sproutForms_forms->getAllForms();

		$noSources = array();
		$prepSources = array();

		foreach ($forms as $form) 
		{
			if ($form->groupId) 
			{
				if (!isset($prepSources[$form->groupId]['heading']))
				{
					$prepSources[$form->groupId]['heading'] = $groups[$form->groupId]->name;	
				}
				
				$prepSources[$form->groupId]['forms'][$form->id] = array(
					'label' => $form->name,
					'data' => array('formId' => $form->id),
					'criteria' => array('formId' => $form->id)
				);
			}
			else
			{
				$noSources[$form->id] = array(
					'label' => $form->name,
					'data' => array('formId' => $form->id),
					'criteria' => array('formId' => $form->id)
				);
			}
		}

		usort($prepSources, 'self::_sortByGroupName');

		// Build our sources for forms with no group
		foreach ($noSources as $form) 
		{
			$sources[$form['data']['formId']] = array(
				'label' => $form['label'],
				'data' => array(
					'formId' => $form['data']['formId'],
				),
				'criteria' => array(
					'formId' => $form['criteria']['formId'],
				)
			);
		}

		// Build our sources sidebar for forms in groups
		foreach ($prepSources as $source) 
		{
			$sources[] = array(
				'heading' => $source['heading']
			);

			foreach ($source['forms'] as $form) 
			{
				$sources[] = array(
					'label' => $form['label'],
					'data' => array(
						'formId' => $form['data']['formId'],
					),
					'criteria' => array(
						'formId' => $form['criteria']['formId'],
					)
				);
			}
		}

		return $sources;
	}

	/**
	 * Returns the attributes that can be shown/sorted by in table views.
	 *
	 * @param string|null $source
	 * @return array
	 */
	public function defineTableAttributes($source = null)
	{
		return array(
			'entryId'      => Craft::t('Entry ID'),
			'formName'     => Craft::t('Form Name'),
			'dateCreated'  => Craft::t('Date Created'),
			'dateUpdated'  => Craft::t('Date Updated'),
		);
	}

	/**
	 * Returns the content table name that should be joined in for an elements query.
	 *
	 * @param ElementCriteriaModel
	 * @return string
	 */
	public function getContentTableForElementsQuery(ElementCriteriaModel $criteria)
	{
		// $groups = craft()->sproutForms_groups->getAllFormGroups('id');
		// $forms = craft()->sproutForms_forms->getAllForms();

		if ($criteria->id && is_numeric($criteria->formId))
		{
			$form = craft()->sproutForms_forms->getFormById($criteria->formId);
			return craft()->sproutForms_forms->getContentTableName($form);
		}
	}

	/**
	 * Defines any custom element criteria attributes for this element type.
	 *
	 * @return array
	 */
	public function defineCriteriaAttributes()
	{
		return array(
			'entryId' => AttributeType::Number,
			'groupId' => AttributeType::Number,
			'formId' => AttributeType::Number,
			'formName' => AttributeType::String,
		);
	}

	/**
	 * Defines which model attributes should be searchable.
	 *
	 * @return array
	 */
	public function defineSearchableAttributes()
	{
		return array(
			'entryId', 
			'formName'
		);
	}

	/**
	 * Modifies an element query targeting elements of this type.
	 *
	 * @param DbCommand $query
	 * @param ElementCriteriaModel $criteria
	 * @return mixed
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query
			->addSelect('entries.id AS entryId, forms.name AS formName')
			->join('sproutforms_entries entries', 'entries.id = elements.id')
			->join('sproutforms_forms forms', 'forms.id = entries.formId');

		if ($criteria->formId)
		{
			$query->andWhere(DbHelper::parseParam('forms.id', $criteria->formId, $query->params));
		}
	}

	/**
	 * Populates an element model based on a query result.
	 *
	 * @param array $row
	 * @return array
	 */
	public function populateElementModel($row)
	{
		return SproutForms_EntryModel::populateModel($row);
	}

	private function _sortByGroupName($a, $b) 
	{
		return strcmp($a['heading'], $b['heading']);
	}
}
