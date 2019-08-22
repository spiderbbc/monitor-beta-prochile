<?php

use yii\db\Migration;

/**
 * Class m190819_214920_Mentions_PostFrom
 */
class m190819_214920_Mentions_PostFrom extends Migration
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

        $this->createTable('{{%mentions_postFrom}}',[
            'id'              => $this->primaryKey(),
            'alert_mentionId' => $this->integer()->notNull(),
            'user_object'     => $this->json()->notNull(),
            'post_from'       => $this->string()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        /*$this->insert('{{%mentions_postFrom}}', [
            'alert_mentionId' => 1,
            'user_object'     => "{'id': 1, 'name': 'Eduardo','username': '@spiderbbc'}",
            'post_from'       => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae ad, recusandae consectetur repellat.',
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);*/

        // creates index for column `alert_mentionId`
        $this->createIndex(
            'idx-mentions_postFrom-alert_mentionId',
            'mentions_postFrom',
            'alert_mentionId'
        );

        // add foreign key for table `alerts_mencions`
        $this->addForeignKey(
            'fk-mentions_postFrom-alert_mentionId',
            'mentions_postFrom',
            'alert_mentionId',
            'alerts_mencions',
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
        $this->dropTable('{{%mentions_postFrom}}');
    }
}
