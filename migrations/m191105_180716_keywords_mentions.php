<?php

use yii\db\Migration;

/**
 * Class m191105_180716_keywords_mentions
 */
class m191105_180716_keywords_mentions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%keywords_mentions}}',[
            'id'        => $this->primaryKey(),
            'keywordId' => $this->integer()->notNull(),
            'mentionId' => $this->integer()->notNull(),
            'createdAt' => $this->integer(),
            'updatedAt' => $this->integer(),
            'createdBy' => $this->integer(),
            'updatedBy' => $this->integer(),

        ],$tableOptions);

       


        // creates index for column `keywordId`
        $this->createIndex(
            'idx-keywords-mentions',
            'keywords_mentions',
            'keywordId'
        );

        // add foreign key for table `keywords`
        $this->addForeignKey(
            'fk-keywords-mentions',
            'keywords_mentions',
            'keywordId',
            'keywords',
            'id',
            'CASCADE',
            'CASCADE'
        );



        // creates index for column `mentionId`
        $this->createIndex(
            'idx-mentions-keywords',
            'keywords_mentions',
            'mentionId'
        );

        // add foreign key for table `mentions`
        $this->addForeignKey(
            'fk-mentions-keywords',
            'keywords_mentions',
            'mentionId',
            'mentions',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%keywords_mentions}}');
    }

}
