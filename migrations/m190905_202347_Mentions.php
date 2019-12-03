<?php

use yii\db\Migration;

/**
 * Class m190905_202347_Mentions
 */
class m190905_202347_Mentions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%mentions}}',[
            'id'              => $this->primaryKey(),
            'alert_mentionId' => $this->integer()->notNull(),
            'origin_id'       => $this->integer()->notNull(),
            'created_time'    => $this->integer()->notNull(),
            'mention_data'    => $this->json(),
            'subject'         => $this->text(),
            'message'         => $this->text()->notNull(),
            'message_markup'  => $this->string(800),
            'url'             => $this->string(),
            'domain_url'      => $this->string(),
            'location'        => $this->string(),
            'social_id'       => $this->bigInteger(64)->defaultValue(0),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `alert_mentionId`
        $this->createIndex(
            'idx-mentions-alert_mentionId',
            'mentions',
            'alert_mentionId'
        );

        // add foreign key for table `alerts_mencions`
        $this->addForeignKey(
            'fk-mentions-alert_mentionId',
            'mentions',
            'alert_mentionId',
            'alerts_mencions',
            'id',
            'CASCADE',
            'CASCADE'
        );


        // creates index for column `alert_mentionId`
        $this->createIndex(
            'idx-mentions_users_mentions',
            'mentions',
            'origin_id'
        );

        $this->addForeignKey(
            'fx-mentions_users_mentions',
            'mentions',
            'origin_id',
            'users_mentions',
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
        $this->dropTable('{{%mentions}}');
    }
}
