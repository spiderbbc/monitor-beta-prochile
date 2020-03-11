<?php

use yii\db\Migration;

/**
 * Class m200311_145945_terms_search
 */
class m200311_145945_terms_search extends Migration
{
    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%terms_search}}',[
            'id'              => $this->primaryKey(),
            'alertId'         => $this->integer()->notNull(),
            'name'            => $this->text(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);
        // creates index for column `userId`
        $this->createIndex(
            'idx-alert_term_search',
            'terms_search',
            'alertId'
        );

        $this->addForeignKey(
            'alert_term_search',
            'terms_search',
            'alertId',
            'alerts',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%terms_search}}');
    }
    
}
