<?php
namespace Craft;

class SproutForms_FormElementType extends BaseElementType
{
	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout Forms');
	}

	/**
	 * Returns whether this element type has content.
	 *
	 * @return bool
	 */
	public function hasContent()
	{
		return false;
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
		$sources = array(
			'*' => array(
				'label'    => Craft::t('All Forms'),
			)
		);

		$groups = craft()->sproutForms_groups->getAllFormGroups();

		foreach ($groups as $group)
		{
			$key = 'group:'.$group->id;

			$sources[$key] = array(
				'label'    => $group->name,
				'data'     => array('id' => $group->id),
				'criteria' => array('groupId' => $group->id)
			);
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
			'name'     => Craft::t('Name'),
			'handle'   => Craft::t('Handle'),
			'numberOfFields' => Craft::t('Number of Fields'),
			'totalEntries' => Craft::t('Total Entries'),
		);
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
		switch ($attribute)
		{
			case 'handle':
			{
				return '<code>'.$element->handle.'</code>';
			}

			case 'numberOfFields':
			{
				$totalFields = craft()->db->createCommand()
            				->select('COUNT(*)')
            				->from('fieldlayoutfields')
            				->where('layoutId=:layoutId', array(':layoutId' => $element->fieldLayoutId))
            				->queryScalar();
            				
				return $totalFields;
			}

			case 'totalEntries':
			{
				$totalEntries = craft()->db->createCommand()
            				->select('COUNT(*)')
            				->from('sproutforms_entries')
            				->where('formId=:formId', array(':formId' => $element->id))
            				->queryScalar();

				return $totalEntries;
			}

			default:
			{
				return parent::getTableAttributeHtml($element, $attribute);
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
			'groupId'	                => AttributeType::Number,
			'fieldLayoutId'           => AttributeType::Number,
			'name'                    => AttributeType::String,
			'handle'                  => AttributeType::String,
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
			'name', 
			'handle'
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
			->addSelect('forms.id,
									 forms.fieldLayoutId,
									 forms.groupId,
									 forms.name, 
									 forms.handle, 
									 forms.titleFormat,
									 forms.displaySectionTitles,
									 forms.redirectUri,
									 forms.submitAction,
									 forms.submitButtonText,
									 forms.notificationRecipients,
									 forms.notificationSubject,
									 forms.notificationSenderName,
									 forms.notificationSenderEmail,
									 forms.notificationReplyToEmail
			')
			->join('sproutforms_forms forms', 'forms.id = elements.id');

		if ($criteria->handle)
		{
			$query->andWhere(DbHelper::parseParam('forms.handle', $criteria->handle, $query->params));
		}
		if ($criteria->groupId)
		{
			$query->join('sproutforms_formgroups formgroups', 'formgroups.id = forms.groupId');
			$query->andWhere(DbHelper::parseParam('forms.groupId', $criteria->groupId, $query->params));
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
		return SproutForms_FormModel::populateModel($row);
	}

	/**
	 * Returns the HTML for an editor HUD for the given element.
	 *
	 * @param BaseElementModel $element
	 * @return string
	 */
	public function getEditorHtml(BaseElementModel $element)
	{
		if ($element->getType()->hasTitleField)
		{
			$html = craft()->templates->render('_cp/fields/titlefield', array(
				'entry' => $element
			));
		}
		else
		{
			$html = '';
		}

		$html .= parent::getEditorHtml($element);

		return $html;
	}
}
