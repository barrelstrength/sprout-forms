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
class SproutForms_FormElementType extends BaseElementType
{
	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Forms');
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
		if ($criteria->id && is_numeric($criteria->id))
		{
			return craft()->sproutForms_forms->getContentTableName($criteria->id);
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
			->addSelect('forms.name, forms.handle')
			->join('sproutforms_forms forms', 'forms.id = elements.id');

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
			$html = craft()->templates->render('entries/_titlefield', array(
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
