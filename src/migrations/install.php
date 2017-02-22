<?php
namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Connection;
use craft\db\Migration;
use craft\elements\User;
use craft\helpers\StringHelper;

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
		$this->createTables();
		$this->createIndexes();
		$this->addForeignKeys();
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%sproutforms_entries}}');
		$this->dropTable('{{%sproutforms_forms}}');
		$this->dropTable('{{%sproutforms_formgroups}}');
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
	}
}