<?php

use yii\db\Migration;

/**
 * Class m190813_195502_Alerts
 */
class m190813_195502_Alerts extends Migration
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

        $this->createTable('{{%alerts}}',[
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'name'            => $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%alerts}}', [
            'userId'          => 1,
            'name'            => 'X Boom Lg',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

         // creates index for column `userId`
        $this->createIndex(
            'idx-useralert_userId_alerts',
            'alerts',
            'userId'
        );

        $this->addForeignKey(
            'useralert_userId_alerts',
            'alerts',
            'userId',
            'users',
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
        $this->dropTable('{{%alerts}}');
    }
}
