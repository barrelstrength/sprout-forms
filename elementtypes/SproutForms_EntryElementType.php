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

		// Prepare the data for our sources sidebar
		$groups = sproutForms()->groups->getAllFormGroups('id');
		$forms  = sproutForms()->forms->getAllForms();

		$noSources   = array();
		$prepSources = array();

		foreach ($forms as $form)
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

		// Build our sources for forms with no group
		foreach ($noSources as $form)
		{
			$key = "form:" . $form['data']['formId'];
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
			if(isset($source['heading']))
			{
				$sources[] = array(
					'heading' => $source['heading']
				);
			}

			foreach ($source['forms'] as $form)
			{
				$key = "form:" . $form['data']['formId'];
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
		$deleteAction = craft()->elements->getAction('Delete');

		$deleteAction->setParams(
			array(
				'confirmationMessage' => Craft::t('Are you sure you want to delete the selected entries?'),
				'successMessage'      => Craft::t('Entries deleted.'),
			)
		);

		return array($deleteAction);
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
			$attributes['field:'.$field->id] = array('label' => $field->name);
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
			return parent::getTableAttributeHtml($element, $attribute);
		}
		catch(Exception $e)
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
			'dateCreated' => Craft::t('Date Created'),
			'dateUpdated' => Craft::t('Date Updated'),
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
			'order'       => array(AttributeType::String, 'default' => 'dateCreated desc'),
			'title'       => AttributeType::String,
			'formId'      => AttributeType::Number,
			'formHandle'  => AttributeType::String,
			'formGroupId' => AttributeType::Number,
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
			entries.dateCreated,
			entries.dateUpdated,
			entries.uid,
			forms.id as formId,
			forms.name as formName,
			forms.groupId as formGroupId';

		$query->join('sproutforms_entries entries', 'entries.id = elements.id');
		$query->join('sproutforms_forms forms', 'forms.id = entries.formId');

		$this->joinContentTableAndAddContentSelects($query, $criteria, $select);
		$query->addSelect($select);

		if ($criteria->id)
		{
			$query->andWhere(DbHelper::parseParam('entries.id', $criteria->id, $query->params));
		}
		if ($criteria->formId)
		{
			$query->andWhere(DbHelper::parseParam('entries.formId', $criteria->formId, $query->params));
		}
		if ($criteria->formHandle)
		{
			$query->andWhere(DbHelper::parseParam('forms.handle', $criteria->formHandle, $query->params));
		}
		if ($criteria->order)
		{
			// Trying to order by date creates ambiguity errors
			// Let's make sure mysql knows what we want to sort by
			if (stripos($criteria->order, 'elements.') === false)
			{
				$criteria->order = str_replace('dateCreated', 'entries.dateCreated', $criteria->order);
				$criteria->order = str_replace('dateUpdated', 'entries.dateUpdated', $criteria->order);
			}

			// If we are sorting by title and do not have a source
			// We won't be able to sort, so bail on it
			if (stripos($criteria->order, 'title') !== false && !$criteria->formId)
			{
				$criteria->order = null;
			}
		}
	}

	/**
	 * Updates the query command, criteria, and select fields when a source is available
	 *
	 * @param DbCommand            $query
	 * @param ElementCriteriaModel $criteria
	 * @param string               $select
	 */
	protected function joinContentTableAndAddContentSelects(
		DbCommand &$query,
		ElementCriteriaModel &$criteria,
		&$select
	) {
		// Do we have a source selected in the sidebar?
		// If so, we have a form id and we can use that to fetch the content table
		if ($criteria->formId)
		{
			$form = sproutForms()->forms->getFormById($criteria->formId);

			if ($form)
			{
				$content = "{$form->handle}.title";
				$select  = empty($select) ? $content : $select.', '.$content;

				$query->join($form->getContentTable().' '.$form->handle, 'entries.formId = '.$form->id);
			}
		}
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
