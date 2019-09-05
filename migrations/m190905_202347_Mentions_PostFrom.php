<?php

use yii\db\Migration;

/**
 * Class m190905_202347_Mentions_PostFrom
 */
class m190905_202347_Mentions_PostFrom extends Migration
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
            'id'                => $this->primaryKey(),
            'alert_mentionId'   => $this->integer()->notNull(),
            'user_id'           => $this->integer()->notNull(),
            'social_id'         => $this->bigInteger(64)->defaultValue(0),
            'post_from'         => $this->string()->notNull(),
            'post_from_markup'  => $this->string()->notNull(),
            'url'               => $this->string(),
            'created_at'        => $this->datetime(),
            'geo_lat'           => $this->decimal(),
            'geo_long'          => $this->decimal(),
            'screen_name'       => $this->string(),
            'name'              => $this->string(),
            'profile_image_url' => $this->string(),

        ],$tableOptions);

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


        // creates index for column `alert_mentionId`
        $this->createIndex(
            'idx-mentions_postFrom_users_mentions',
            'mentions_postFrom',
            'user_id'
        );

        $this->addForeignKey(
            'fx-mentions_postFrom_users_mentions',
            'mentions_postFrom',
            'user_id',
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
        $this->dropTable('{{%mentions_postFrom}}');
    }
}
