<?php

use yii\db\Migration;

/**
 * Class m190916_222510_cache
 */
class m190916_222510_cache extends Migration
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

        $this->createTable('{{%cache}}',[
            'id'                => $this->char(128),
            'expire'         => $this->integer(11),
            'data'         => $this->binary(128),

        ],$tableOptions);

    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%cache}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190916_222510_cache cannot be reverted.\n";

        return false;
    }
    */
}
