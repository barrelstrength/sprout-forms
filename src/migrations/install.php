<?php
namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutcore\SproutCore;
use barrelstrength\sproutforms\SproutForms;
use craft\db\Migration;

/**
 * Installation Migration
 */
class Install extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		// Generate sprout reports table if it exists.
		SproutCore::$app->reportsMigration->createTables();

		$this->createTables();
		$this->createIndexes();
		$this->addForeignKeys();
		$this->insertDefaultData();
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		SproutCore::$app->reportsMigration->dropTablesByDataSourceId('sproutforms.sproutformsentriesdatasource');

		$this->dropTable('{{%sproutforms_entries}}');
		$this->dropTable('{{%sproutforms_forms}}');
		$this->dropTable('{{%sproutforms_formgroups}}');
		$this->dropTable('{{%sproutforms_entrystatuses}}');
	}

	/**
	 * Creates the tables.
	 *
	 * @return void
	 */
	protected function createTables()
	{
		$this->createTable('{{%sproutforms_forms}}', [
			'id'                       => $this->primaryKey(),
			'fieldLayoutId'            => $this->integer(),
			'groupId'                  => $this->integer(),
			'name'                     => $this->string()->notNull(),
			'handle'                   => $this->string()->notNull(),
			'titleFormat'              => $this->string()->notNull(),
			'displaySectionTitles'     => $this->boolean()->defaultValue(false),
			'redirectUri'              => $this->string(),
			'submitAction'             => $this->string(),
			'submitButtonText'         => $this->string(),
			'savePayload'              => $this->boolean()->defaultValue(false),
			'notificationEnabled'      => $this->boolean()->defaultValue(false),
			'notificationRecipients'   => $this->string(),
			'notificationSubject'      => $this->string(),
			'notificationSenderName'   => $this->string(),
			'notificationSenderEmail'  => $this->string(),
			'notificationReplyToEmail' => $this->string(),
			'enableTemplateOverrides'  => $this->boolean()->defaultValue(false),
			'templateOverridesFolder'  => $this->string(),
			'enableFileAttachments'    => $this->boolean()->defaultValue(false),
			'dateCreated'              => $this->dateTime()->notNull(),
			'dateUpdated'              => $this->dateTime()->notNull(),
			'uid'                      => $this->uid(),
		]);

		$this->createTable('{{%sproutforms_formgroups}}', [
			'id'                       => $this->primaryKey(),
			'name'                     => $this->string()->notNull(),
			'dateCreated'              => $this->dateTime()->notNull(),
			'dateUpdated'              => $this->dateTime()->notNull(),
			'uid'                      => $this->uid(),
		]);

		$this->createTable('{{%sproutforms_entries}}', [
			'id'          => $this->primaryKey(),
			'formId'      => $this->integer()->notNull(),
			'statusId'    => $this->integer(),
			'ipAddress'   => $this->string(),
			'userAgent'   => $this->longText(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createTable('{{%sproutforms_entrystatuses}}', [
			'id'          => $this->primaryKey(),
			'name'        => $this->string()->notNull(),
			'handle'      => $this->string()->notNull(),
			'color'       => $this->enum('color',
					['green', 'orange', 'red', 'blue',
					'yellow', 'pink', 'purple', 'turquoise',
					'light', 'grey', 'black'
					])
					->notNull()->defaultValue('blue'),
			'sortOrder'   => $this->smallInteger()->unsigned(),
			'isDefault'   => $this->boolean(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);
	}

	/**
	 * Creates the indexes.
	 *
	 * @return void
	 */
	protected function createIndexes()
	{
		$this->createIndex(
			$this->db->getIndexName(
				'{{%sproutforms_forms}}',
				'fieldLayoutId',
				false, true
			),
			'{{%sproutforms_forms}}',
			'fieldLayoutId',
			false
		);

		$this->createIndex(
			$this->db->getIndexName(
				'{{%sproutforms_entries}}',
				'formId',
				false, true
			),
			'{{%sproutforms_entries}}',
			'formId',
			false
		);
	}

	/**
	 * Adds the foreign keys.
	 *
	 * @return void
	 */
	protected function addForeignKeys()
	{
		$this->addForeignKey(
			$this->db->getForeignKeyName(
				'{{%sproutforms_forms}}', 'fieldLayoutId'
			),
			'{{%sproutforms_forms}}', 'fieldLayoutId',
			'{{%fieldlayouts}}', 'id', 'SET NULL', null
		);

		$this->addForeignKey(
			$this->db->getForeignKeyName(
				'{{%sproutforms_forms}}', 'id'
			),
			'{{%sproutforms_forms}}', 'id',
			'{{%elements}}', 'id', 'CASCADE', null
		);

		$this->addForeignKey(
			$this->db->getForeignKeyName(
				'{{%sproutforms_entries}}', 'id'
			),
			'{{%sproutforms_entries}}', 'id',
			'{{%elements}}', 'id', 'CASCADE', null
		);

		$this->addForeignKey(
			$this->db->getForeignKeyName(
				'{{%sproutforms_entries}}', 'formId'
			),
			'{{%sproutforms_entries}}', 'formId',
			'{{%sproutforms_forms}}', 'id', 'CASCADE', null
		);
	}

	/**
	 * Populates the DB with the default data.
	 *
	 * @return void
	 */
	protected function insertDefaultData()
	{
		// populate default Entry Statuses
		$defaultEntryStatuses = [
			0 => [
				'name'      => 'Unread',
				'handle'    => 'unread',
				'color'     => 'blue',
				'sortOrder' => 1,
				'isDefault' => 1
			],
			1 => [
				'name'      => 'Read',
				'handle'    => 'read',
				'color'     => 'grey',
				'sortOrder' => 2,
				'isDefault' => 0
			]
		];

		foreach ($defaultEntryStatuses as $entryStatus)
		{
			$this->db->createCommand()->insert('{{%sproutforms_entrystatuses}}', [
				'name'      => $entryStatus['name'],
				'handle'    => $entryStatus['handle'],
				'color'     => $entryStatus['color'],
				'sortOrder' => $entryStatus['sortOrder'],
				'isDefault' => $entryStatus['isDefault']
			])->execute();
		}
	}
}