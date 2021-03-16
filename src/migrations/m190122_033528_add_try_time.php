<?php

use yii\db\Migration;

/**
 * Class m190122_033528_add_try_time
 */
class m190122_033528_add_try_time extends Migration {

	/**
	 * {@inheritdoc}
	 */
	public function safeUp() {
		$this->addColumn('{{%email_message}}', 'try_time', \yii\db\Schema::TYPE_INTEGER . ' DEFAULT 0');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown() {
		echo "m190122_033528_add_try_time cannot be reverted.\n";
		return false;
	}
	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{

	}

	public function down()
	{
		echo "m190122_033528_add_try_time cannot be reverted.\n";

		return false;
	}
	*/
}
