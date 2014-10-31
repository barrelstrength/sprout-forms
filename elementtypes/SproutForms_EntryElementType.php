<?php
namespace Craft;

class SproutForms_EntryElementType extends BaseElementType
{
	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout Forms Entries');
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
	 * Returns whether this element type has titles.
	 *
	 * @return bool
	 */
	public function hasTitles()
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
			'title'			=> Craft::t('Title'),
			'formName'		=> Craft::t('Form Name'),
			'dateCreated'	=> Craft::t('Date Created'),
			'dateUpdated'	=> Craft::t('Date Updated'),
		);
	}

	/**
	 * Returns the content table name that should be joined in for an elements query.
	 *
	 * @param ElementCriteriaModel
	 *
	 * @throws Exception
	 * @return string
	 */
	public function getContentTableForElementsQuery(ElementCriteriaModel $criteria)
	{
		if ($criteria->id && $criteria->formId)
		{
			$form = SproutForms_FormRecord::model()->findById($criteria->formId);

			if ($form)
			{
				return sprintf('sproutformscontent_%s', trim(strtolower($form->handle)));
			}
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
			'title'			=> AttributeType::String,
			'formId'		=> AttributeType::Number,
			'formName'		=> AttributeType::String,
			'formGroupId'	=> AttributeType::Number,
		);
	}

	/**
	 * Defines which model attributes should be searchable.
	 *
	 * @return array
	 */
	public function defineSearchableAttributes()
	{
		return array('id', 'title', 'formName');
	}

	/**
	 * Modifies an element query targeting elements of this type.
	 *
	 * @param DbCommand $query
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return mixed
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query->addSelect('
			entries.id,
			entries.ipAddress,
			entries.userAgent,
			entries.dateCreated,
			entries.dateUpdated,
			entries.uid,
			forms.id as formId,
			forms.name as formName,
			forms.groupId as formGroupId
		');

		$query->join('sproutforms_entries entries', 'entries.id = elements.id');
		$query->join('sproutforms_forms forms', 'forms.id = entries.formId');

		if ($criteria->id)
		{
			$query->andWhere(DbHelper::parseParam('entries.id', $criteria->id, $query->params));
		}
		if ($criteria->formId)
		{
			$query->andWhere(DbHelper::parseParam('entries.formId', $criteria->formId, $query->params));
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
}
