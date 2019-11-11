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
            'name'              => $this->string()->notNull(),
            'user_data'         => $this->json(),
            'subject'           => $this->text(),
            'message'           => $this->string(400),
            'screen_name'       => $this->string()->notNull(),
            'profile_image_url' => $this->string(),
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
