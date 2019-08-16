<?php

use yii\db\Migration;

/**
 * Class m190813_215104_Credencials_Api
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

        $this->createTable('{{%Credencials_Api}}',[
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'resourceId'      => $this->integer()->notNull(),
            'name'            => $this->string(45),
            'api_key'         => $this->string(45),
            'api_secret_key'  => $this->string(45),
            'access_secret_token' => $this->string(45),
            'bearer_token'    => $this->string(45),
            'apiLogin'        => $this->string(45),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%Credencials_Api}}', [
            'userId'                => 1,
            'resourceId'            => 1,
            'name'                  => 'admin-twitter',
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

        $this->insert('{{%Credencials_Api}}', [
            'userId'                => 1,
            'resourceId'            => 2,
            'name'                  => 'admin-livechat',
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

        $this->insert('{{%Credencials_Api}}', [
            'userId'                => 1,
            'resourceId'            => 3,
            'name'                  => 'admin-livechatConversations',
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
            'Credencials_Api',
            'userId'
        );

        $this->addForeignKey(
            'credencial_api_userId',
            'Credencials_Api',
            'userId',
            'Users',
            'id',
            'CASCADE',
            'CASCADE'
        );


         // creates index for column `resourceId`
        $this->createIndex(
            'idx-credencial_api_resourceId',
            'Credencials_Api',
            'resourceId'
        );

        $this->addForeignKey(
            'credencial_api_resourceId',
            'Credencials_Api',
            'resourceId',
            'Resources',
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
        $this->dropTable('{{%AlertConfig_Sources}}');
    }

}
