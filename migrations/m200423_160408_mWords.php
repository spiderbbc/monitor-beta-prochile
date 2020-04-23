<?php

use yii\db\Migration;

/**
 * Class m200423_160408_mWords
 */
class m200423_160408_mWords extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_words}}',[
            'id'              => $this->primaryKey(),
            'name'            => $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%m_words}}');
    }
    
}
