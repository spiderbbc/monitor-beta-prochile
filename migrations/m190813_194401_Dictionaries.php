<?php

use yii\db\Migration;

/**
 * Class m190813_194401_Dictionaries
 */
class m190813_194401_Dictionaries extends Migration
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

        $this->createTable('{{%dictionaries}}', [
            'id'                    => $this->primaryKey(),
            'name'                  => $this->string(45)->notNull(),
            'color'                 => $this->string(45)->notNull(),
            'createdAt'             => $this->integer(11),
            'updatedAt'             => $this->integer(11),
            'createdBy'             => $this->integer(11),
            'updatedBy'             => $this->integer(11),

        ], $tableOptions);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'Free Words',
            'color'                 => '#55e6c9',
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
        $this->dropTable('{{%dictionaries}}');
    }

}
