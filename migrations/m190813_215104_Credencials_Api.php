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

        $data = Yii::$app->params;
        $secretKey = Yii::$app->params['key'];

        $this->createTable('{{%credencials_api}}',[
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'resourceId'      => $this->integer()->notNull(),
            'name_app'        => $this->string(45),
            'api_key'         => $this->string(134),
            'api_secret_key'  => $this->string(206),
            'access_secret_token' => $this->string(206),
            'bearer_token'    => $this->string(305),
            'apiLogin'        => $this->string(206),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 1,
            'name_app'            => 'monitor-alfa',
            'api_key'             => utf8_encode(Yii::$app->getSecurity()->encryptByPassword($data['twitter']['api_key'], $secretKey)),
            'api_secret_key'      => utf8_encode(Yii::$app->getSecurity()->encryptByPassword($data['twitter']['api_secret_key'], $secretKey)),
            'access_secret_token' => utf8_encode(Yii::$app->getSecurity()->encryptByPassword($data['twitter']['access_secret_token'], $secretKey)),
            'bearer_token'        => '',
            'apiLogin'            => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 2,
            'name_app'            => 'admin-twitter',
            'api_key'             => 'encrycpt here',
            'api_secret_key'      => 'encrycpt here',
            'access_secret_token' => 'encrycpt here',
            'bearer_token'        => 'encrycpt here',
            'apiLogin'            => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

        

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 3,
            'name_app'            => 'admin-livechatConversations',
            'api_key'             => 'encrycpt here',
            'api_secret_key'      => 'encrycpt here',
            'access_secret_token' => 'encrycpt here',
            'bearer_token'        => 'encrycpt here',
            'apiLogin'            => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
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
