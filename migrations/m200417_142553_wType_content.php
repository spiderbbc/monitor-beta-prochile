<?php

use yii\db\Migration;

/**
 * Class m200417_142553_wType_content
 */
class m200417_142553_wType_content extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_type_content}}',[
            'id'              => $this->primaryKey(),
            'name'            => $this->string(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%w_type_content}}', [
            'name'        => 'Page',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);

        $this->insert('{{%w_type_content}}', [
            'name'        => 'Post',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);

        $this->insert('{{%w_type_content}}', [
            'name'        => 'Story',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);

    }

    public function down()
    {
        $this->dropTable('{{%w_type_content}}');
    }
}
