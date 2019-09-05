<?php

use yii\db\Migration;

/**
 * Class m190905_202346_Users_Mentions
 */
class m190905_202346_Users_Mentions extends Migration
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

        $this->createTable('{{%users_mentions}}',[
            'id'                => $this->primaryKey(),
            'user_uuid'         => $this->bigInteger(20)->defaultValue(0),
            'screen_name'       => $this->string()->notNull(),
            'name'              => $this->string()->notNull(),
            'profile_image_url' => $this->string(),
            'location'          => $this->string()->notNull(),
            'url'               => $this->string(),
            'description'       => $this->string(),
            'created_at'        => $this->datetime(),
            'followers_count'   => $this->integer(10),
            'friends_count'     => $this->integer(10),
            'statuses_count'    => $this->integer(10),
            'createdAt'         => $this->integer(),
            'updatedAt'         => $this->integer(),

        ],$tableOptions);

       

    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%users_mentions}}');
    }

}
