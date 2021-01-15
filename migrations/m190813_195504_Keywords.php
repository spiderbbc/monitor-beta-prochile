<?php

use yii\db\Migration;

/**
 * Class m190813_195504_Keywords
 */
class m190813_195504_Keywords extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%keywords}}', [
            'id'                    => $this->primaryKey(),
            'dictionaryId'          => $this->integer(),
            'name'                  => $this->string()->notNull()->unique(),
            'createdAt'             => $this->integer(),
            'updatedAt'             => $this->integer(),
            'createdBy'             => $this->integer(),
            'updatedBy'             => $this->integer(),

        ], $tableOptions);


        // creates index for column `dictionaryId`
        $this->createIndex(
            'idx-dictionary-dictionaries',
            'keywords',
            'dictionaryId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-dictionary-dictionaries',
            'keywords',
            'dictionaryId',
            'dictionaries',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%keywords}}');
    }

}
