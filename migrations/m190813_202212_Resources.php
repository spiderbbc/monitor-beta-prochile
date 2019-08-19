<?php

use yii\db\Migration;

/**
 * Class m190813_202212_Alerts_Resources
 */
class m190813_202212_Resources extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%resources}}', [
            'id'                    => $this->primaryKey(),
            'name'                  => $this->string(40)->notNull(),
            'createdAt'             => $this->integer(),
            'updatedAt'             => $this->integer(),
            'createdBy'             => $this->integer(),
            'updatedBy'             => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%resources}}', [
            'name'                  => 'Twitter',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%resources}}', [
            'name'                  => 'Live Chat',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%resources}}', [
            'name'                  => 'Live Chat Conversations',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%resources}}', [
            'name'                  => 'Web page',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%resources}}');
    }

}
