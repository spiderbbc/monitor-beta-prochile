<?php

use yii\db\Migration;

/**
 * Class m190813_211505_Products_Series
 */
class m190813_211505_Products_Series extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%products_series}}', [
            'id'                => $this->primaryKey(),
            'name'              => $this->string(),
            'abbreviation_name' => $this->string(),
            'status'            => $this->smallInteger(1)->defaultValue(1),
            'createdAt'         => $this->integer(),
            'updatedAt'         => $this->integer(),
            'createdBy'         => $this->integer(),
            'updatedBy'         => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%products_series}}', [
            'name'                  => 'Pro Chile',
            'abbreviation_name'     => 'PCH',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%products_series}}');
    }
}
