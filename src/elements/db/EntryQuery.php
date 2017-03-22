<?php
namespace barrelstrength\sproutforms\elements\db;

use Craft;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;

class EntryQuery extends ElementQuery
{

	/**
	 * @var int
	 */
	public $statusId;

	/**
	 * @var string
	 */
	public $ipAddress;

	/**
	 * @var string
	 */
	public $userAgent;

	/**
	 * @var int
	 */
	public $formId;

	public $formHandle;

	public $formName;

	public $formGroupId;

	/**
	 * @inheritdoc
	 */
	public function __set($name, $value)
	{
		parent::__set($name, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function __construct($elementType, array $config = [])
	{
		// Default orderBy
		if (!isset($config['orderBy'])) {
			$config['orderBy'] = 'sproutforms_entries.dateCreated';
		}

		parent::__construct($elementType, $config);
	}

	/**
	 * Sets the [[statusId]] property.
	 *
	 * @param int
	 *
	 * @return static self reference
	 */
	public function statusId($value)
	{
		$this->statusId = $value;

		return $this;
	}

	/**
	 * Sets the [[formId]] property.
	 *
	 * @param int
	 *
	 * @return static self reference
	 */
	public function formId($value)
	{
		$this->formId = $value;

		return $this;
	}

	/**
	 * Sets the [[formName]] property.
	 *
	 * @param int
	 *
	 * @return static self reference
	 */
	public function formName($value)
	{
		$this->formName = $value;

		return $this;
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	protected function beforePrepare(): bool
	{
		$this->joinElementTable('sproutforms_entries');

		// Figure out which content table to use
		$this->contentTable = null;

		if ($this->id && $this->formId)
		{
			$form = SprotForms::$api->forms->getFormById($this->formId);

			if ($form)
			{
				$this->contentTable = $form->getContentTable();
			}
		}

		$this->query->select([
			'sproutforms_entries.statusId',
			'sproutforms_entries.formId',
			'sproutforms_entries.ipAddress',
			'sproutforms_entries.userAgent',
			'sproutforms_entries.dateCreated',
			'sproutforms_entries.dateUpdated',
			'sproutforms_entries.uid',
			'forms.id as formId',
			'forms.name as formName',
			'forms.groupId as formGroupId'
		]);

		$this->query->innerJoin('{{%sproutforms_entrystatuses}} entrystatuses', '[[entrystatuses.id]] = [[sproutforms_entries.statusId]]');
		$this->query->innerJoin('{{%sproutforms_forms}} forms', '[[forms.id]] = [[sproutforms_entries.formId]]');

		$this->joinContentTableAndAddContentSelects($this);

		if ($this->id) {
			$this->subQuery->andWhere(Db::parseParam(
				'sproutforms_entries.id', $this->id)
			);
		}

		if ($this->formId) {
			$this->subQuery->andWhere(Db::parseParam(
				'sproutforms_entries.formId', $this->formId)
			);
		}

		if ($this->statusId) {
			$this->subQuery->andWhere(Db::parseParam(
				'sproutforms_entries.statusId', $this->statusId)
			);
		}

		if ($this->formHandle) {
			$this->subQuery->andWhere(Db::parseParam(
				'forms.handle', $this->formHandle)
			);
		}

		if ($this->formName) {
			$this->subQuery->andWhere(Db::parseParam(
				'forms.name', $this->formName)
			);
		}

		return parent::beforePrepare();
	}

	/**
	 * Updates the query command, criteria, and select fields when a source is available
	 *
	 * @param EntryQuery            $query
	 */
	protected function joinContentTableAndAddContentSelects(
		EntryQuery $entryQuery
	)
	{
		// Do we have a source selected in the sidebar?
		// If so, we have a form id and we can use that to fetch the content table
		if ($entryQuery->formId || $entryQuery->formHandle)
		{
			$form = null;

			if ($entryQuery->formId)
			{
				$form = SproutForms::$api->forms->getFormById($entryQuery->formId);
			}
			else
			{
				if ($entryQuery->formHandle)
				{
					$form = SproutForms::$api->forms->getFormByHandle($entryQuery->formHandle);
				}
			}

			if ($form)
			{
				$fields      = $form->getFields();
				$fieldPrefix = Craft::$app->content->fieldColumnPrefix;
				$select      = $entryQuery->query->select;
				$select[]    = "{$form->handle}.title";

				// Added support for filtering any sproutform content table
				foreach ($fields as $key => $field)
				{
					if ($field->hasContentColumn())
					{
						$select[] = "{$form->handle}.{$fieldPrefix}{$field->handle} as {$field->handle}";
						$handle = $field->handle;

						if (isset($entryQuery->$handle))
						{
							$entryQuery->subQuery->andWhere(Db::parseParam(
								$form->handle . "." . $fieldPrefix . $field->handle,
								$entryQuery->$handle)
							);
						}
					}
				}

				$entryQuery->query->innerJoin(
					$form->getContentTable() . ' as ' . $form->handle,
					'[['.$form->handle . '.elementId]] = [[subquery.elementsId]]'
				);
			}
		}
	}
}
