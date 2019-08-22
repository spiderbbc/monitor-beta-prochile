<?php

use yii\db\Migration;

/**
 * Class m190813_215104_credencials_api
 */
class m190813_215104_Credencials_Api extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%credencials_api}}',[
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'resourceId'      => $this->integer()->notNull(),
            'name_app'            => $this->string(45),
            'api_key'         => $this->string(60),
            'api_secret_key'  => $this->string(60),
            'access_secret_token' => $this->string(60),
            'bearer_token'    => $this->string(60),
            'apiLogin'        => $this->string(60),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 1,
            'name_app'            => 'monitor-alfa',
            'api_key'             => '$2y$13$fGJtSSVFKKUlM69f8RqYDOUsCOvz8KcP26dSfdmwdjTJUmbXCdcKi',
            'api_secret_key'      => '$2y$13$eg5dtqee0N5r/YLEf3VKOehgMPRGVEMxcXw/3InmB9/arCY.w2QDe',
            'access_secret_token' => '$2y$13$/xkRzfgFqxTl2fjmdc8gj.0AVOVH84PFm7uOxHotlUo.uNoZQkXvW',
            'bearer_token'        => '$2y$13$FKKo81BwjKqOY4kXA8I0DegzmbkZnU0J8zfcw82NonIvlUAsWV4rC',
            'api_secret_key'      => '$2y$13$TVqEY/ZUD/rDf9hIer9S1uIeQof41uBFROOqJlQvThMYpgpWLrdi6',
            'apiLogin'            => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

        $this->insert('{{%credencials_api}}', [
            'userId'                => 1,
            'resourceId'            => 2,
            'name_app'                  => 'admin-twitter',
            'api_key'               => 'encrycpt here',
            'api_secret_key'        => 'encrycpt here',
            'access_secret_token'   => 'encrycpt here',
            'bearer_token'          => 'encrycpt here',
            'api_secret_key'        => 'encrycpt here',
            'apiLogin'              => 'encrycpt here',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        

        $this->insert('{{%credencials_api}}', [
            'userId'                => 1,
            'resourceId'            => 3,
            'name_app'                  => 'admin-livechatConversations',
            'api_key'               => 'encrycpt here',
            'api_secret_key'        => 'encrycpt here',
            'access_secret_token'   => 'encrycpt here',
            'bearer_token'          => 'encrycpt here',
            'api_secret_key'        => 'encrycpt here',
            'apiLogin'              => 'encrycpt here',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

         // creates index for column `userId`
        $this->createIndex(
            'idx-credencial_api_userId',
            'credencials_api',
            'userId'
        );

        $this->addForeignKey(
            'credencial_api_userId',
            'credencials_api',
            'userId',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );


         // creates index for column `resourceId`
        $this->createIndex(
            'idx-credencial_api_resourceId',
            'credencials_api',
            'resourceId'
        );

        $this->addForeignKey(
            'credencial_api_resourceId',
            'credencials_api',
            'resourceId',
            'resources',
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
        $this->dropTable('{{%credencials_api}}');
    }

}
