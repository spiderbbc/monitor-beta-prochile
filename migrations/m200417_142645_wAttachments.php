<?php

use yii\db\Migration;

/**
 * Class m200417_142645_wAttachments
 */
class m200417_142645_wAttachments extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_attachments}}',[
            'id'              => $this->primaryKey(),
            'content_id'      => $this->integer()->notNull(),
            'title'           => $this->string(),
            'type'            => $this->string(),
            'src_url'         => $this->text(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);


        

         // creates index for column `content_id`
        $this->createIndex(
            'idx-w_attachments-content_id',
            'w_attachments',
            'content_id'
        );

        // relation
        // add foreign key for table `seriesId`
        $this->addForeignKey(
            'fk-w_attachments-content_id',
            'w_attachments',
            'content_id',
            'w_content',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%w_attachments}}');
    }
}
