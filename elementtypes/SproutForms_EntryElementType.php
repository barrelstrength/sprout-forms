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
	 * @inheritDoc IElementType::hasStatuses()
	 *
	 * @return bool
	 */
	public function hasStatuses()
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
	 * Returns a list of statuses for this element type
	 *
	 * @return array
	 */
	public function getStatuses()
	{
		$statuses    = sproutForms()->entries->getAllEntryStatuses();
		$statusArray = array();

		foreach ($statuses as $status)
		{
			$key = $status['handle'] . ' ' . $status['color'];
			$statusArray[$key] = $status['name'];
		}

		return $statusArray;
	}

	/**
	 * Returns this element type's sources.
	 *
	 * @param string|null $context
	 *
	 * @return array|false
	 */
	public function getSources($context = null)
	{
		// Start with an option for everything
		$sources = array(
			'*' => array(
				'label' => Craft::t('All Entries'),
			)
		);

		$sources[] = array(
			'heading' => Craft::t("Forms")
		);

		// Prepare the data for our sources sidebar
		$groups = sproutForms()->groups->getAllFormGroups('id');
		$forms  = sproutForms()->forms->getAllForms();

		$noSources   = array();
		$prepSources = array();

		foreach ($forms as $form)
		{
			$saveData = sproutForms()->entries->isDataSaved($form);

			if ($saveData)
			{
				if ($form->groupId)
				{
					if (!isset($prepSources[$form->groupId]['heading']) && isset($groups[$form->groupId]))
					{
						$prepSources[$form->groupId]['heading'] = $groups[$form->groupId]->name;
					}

					$prepSources[$form->groupId]['forms'][$form->id] = array(
						'label'    => $form->name,
						'data'     => array('formId' => $form->id),
						'criteria' => array('formId' => $form->id)
					);
				}
				else
				{
					$noSources[$form->id] = array(
						'label'    => $form->name,
						'data'     => array('formId' => $form->id),
						'criteria' => array('formId' => $form->id)
					);
				}
			}
		}

		// Build our sources for forms with no group
		foreach ($noSources as $form)
		{
			$key           = "form:" . $form['data']['formId'];
			$sources[$key] = array(
				'label'    => $form['label'],
				'data'     => array(
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
			if (isset($source['heading']))
			{
				$sources[] = array(
					'heading' => $source['heading']
				);
			}

			foreach ($source['forms'] as $form)
			{
				$key           = "form:" . $form['data']['formId'];
				$sources[$key] = array(
					'label'    => $form['label'],
					'data'     => array(
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
	 * @inheritDoc IElementType::getAvailableActions()
	 *
	 * @param string|null $source
	 *
	 * @return array|null
	 */
	public function getAvailableActions($source = null)
	{
		$deleteAction    = array();
		$setStatusAction = array();

		if (craft()->userSession->checkPermission('editSproutFormsEntries'))
		{
			$deleteAction = craft()->elements->getAction('Delete');

			$deleteAction->setParams(
				array(
					'confirmationMessage' => Craft::t('Are you sure you want to delete the selected entries?'),
					'successMessage'      => Craft::t('Entries deleted.'),
				)
			);

			$setStatusAction = craft()->elements->getAction('SproutForms_SetStatus');
		}

		return array($deleteAction, $setStatusAction);
	}

	/**
	 * Returns the attributes that can be selected as table columns
	 *
	 * @return array
	 */
	public function defineAvailableTableAttributes()
	{
		$attributes = array(
			'title'       => array('label' => Craft::t('Title')),
			'formName'    => array('label' => Craft::t('Form Name')),
			'dateCreated' => array('label' => Craft::t('Date Created')),
			'dateUpdated' => array('label' => Craft::t('Date Updated')),
		);

		// Mix in custom fields defined on the SproutForms_Form Element
		foreach (craft()->elementIndexes->getAvailableTableFields('SproutForms_Form') as $field)
		{
			$attributes['field:' . $field->id] = array('label' => $field->name);
		}

		return $attributes;
	}

	/**
	 * Returns default table columns for table views
	 *
	 * @return array
	 */
	public function getDefaultTableAttributes($source = null)
	{
		$attributes = array();

		$attributes[] = 'title';

		if ($source == '*')
		{
			$attributes[] = 'formName';
		}

		$attributes[] = 'dateCreated';
		$attributes[] = 'dateUpdated';

		return $attributes;
	}

	/**
	 * @inheritDoc IElementType::getTableAttributeHtml()
	 *
	 * @param BaseElementModel $element
	 * @param string           $attribute
	 *
	 * @return string
	 */
	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		//@todo - can we improve this?
		try
		{
			switch ($attribute)
			{
				default:
				{
					return parent::getTableAttributeHtml($element, $attribute);
				}
			}
		}
		catch (Exception $e)
		{
			return '';
		}
	}

	/**
	 * Returns the attributes that can be sorted by in table views.
	 *
	 * @return array
	 */
	public function defineSortableAttributes()
	{
		return array(
			'formName'    => Craft::t('Form Name'),
			'elements.dateCreated' => Craft::t('Date Created'),
			'elements.dateUpdated' => Craft::t('Date Updated'),
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
		if ($criteria->formId || $criteria->formHandle)
		{
			$form = null;

			if ($criteria->formId)
			{
				$form = sproutForms()->forms->getFormById($criteria->formId);
			}
			else if ($criteria->formHandle)
			{
				$form = sproutForms()->forms->getFormByHandle($criteria->formHandle);
			}

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
			'order'        => array(AttributeType::String, 'default' => 'dateCreated desc'),
			'title'        => AttributeType::String,
			'entryStatus'  => AttributeType::Number,
			'statusId'     => AttributeType::Number,
			'formId'       => AttributeType::Number,
			'statusHandle' => AttributeType::String,
			'formHandle'   => AttributeType::String,
			'formGroupId'  => AttributeType::Number,
		);
	}

	/**
	 * @inheritDoc IElementType::getElementQueryStatusCondition()
	 *
	 * @param DbCommand $query
	 * @param string    $status
	 *
	 * @return array|false|string|void
	 */
	public function getElementQueryStatusCondition(DbCommand $query, $status)
	{
		$statusClasses = explode(' ', $status);

		if (count($statusClasses)>0)
		{
			$handle = $statusClasses[0];
			$query->andWhere(DbHelper::parseParam('entrystatuses.handle', $handle, $query->params));
		}
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
	 * @param DbCommand            $query
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return mixed
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$select =
			'entries.id,
			entries.ipAddress,
			entries.userAgent,
			entries.statusId,
			entries.uid,
			forms.id as formId,
			forms.name as formName,
			forms.groupId as formGroupId,
			entrystatuses.handle';

		$query->join('sproutforms_entries entries', 'entries.id = elements.id');
		$query->join('sproutforms_entrystatuses entrystatuses', 'entrystatuses.id = entries.statusId');
		$query->join('sproutforms_forms forms', 'forms.id = entries.formId');

		$query->addSelect($select);

		if ($criteria->id)
		{
			$query->andWhere(DbHelper::parseParam('entries.id', $criteria->id, $query->params));
		}
		if ($criteria->formId)
		{
			$query->andWhere(DbHelper::parseParam('entries.formId', $criteria->formId, $query->params));
		}
		if ($criteria->statusId)
		{
			$query->andWhere(DbHelper::parseParam('entries.statusId', $criteria->statusId, $query->params));
		}
		if ($criteria->statusHandle)
		{
			$query->andWhere(DbHelper::parseParam('entrystatuses.handle', $criteria->statusHandle, $query->params));
		}
		if ($criteria->formHandle)
		{
			$query->andWhere(DbHelper::parseParam('forms.handle', $criteria->formHandle, $query->params));
		}
		if ($criteria->order)
		{

			// If we are sorting by title and do not have a source
			// We won't be able to sort, so bail on it
			if (stripos($criteria->order, 'title') !== false && !$criteria->formId)
			{
				$criteria->order = null;
			}
		}
	}

	/**
	 * @inheritDoc IElementType::getFieldsForElementsQuery()
	 *
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return FieldModel[]
	 */
	public function getFieldsForElementsQuery(ElementCriteriaModel $criteria)
	{
		// Now assemble the actual fields list
		$fields = array();

		if ($criteria->formId || $criteria->formHandle)
		{
			$form = null;

			if ($criteria->formId)
			{
				$form = sproutForms()->forms->getFormById($criteria->formId);
			}
			else
			{
				if ($criteria->formHandle)
				{
					$form = sproutForms()->forms->getFormByHandle($criteria->formHandle);
				}
			}

			if ($form)
			{
				$fields = $form->getFields();
			}
		}

		return $fields;
	}

	/**
	 * Populates an element model based on a query result.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function populateElementModel($row)
	{
		return SproutForms_EntryModel::populateModel($row);
	}
}
