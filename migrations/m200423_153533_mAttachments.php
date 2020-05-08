<?php

use yii\db\Migration;

/**
 * Class m200423_153533_mAttachments
 */
class m200423_153533_mAttachments extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_attachments}}',[
            'id'              => $this->primaryKey(),
          //  'statisticId'     => $this->integer()->notNull(),
            'src_url'         => $this->text(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);


        

         /*// creates index for column `content_id`
        $this->createIndex(
            'idx-m_attachments-statisticId',
            'm_attachments',
            'statisticId'
        );

        // relation
        // add foreign key for table `seriesId`
        $this->addForeignKey(
            'fk-m_attachments-statisticId',
            'm_attachments',
            'statisticId',
            'm_statistics',
            'id',
            'CASCADE',
            'CASCADE'
        );*/

    }

    public function down()
    {
        $this->dropTable('{{%m_attachments}}');
    }
    
}
