<?php

use yii\db\Migration;

/**
 * Class m190813_203507_Alerts_Mencions
 */
class m190813_203507_Alerts_Mencions extends Migration
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

        $this->createTable('{{%alerts_mencions}}',[
            'id'             => $this->primaryKey(),
            'alertId'        => $this->integer()->notNull(),
            'resourcesId'    => $this->integer()->notNull(),
            'condition'      => $this->string()->notNull()->defaultValue('ACTIVE'),
            'type'           => $this->string(),
            'term_searched'  => $this->string(),
            'date_searched'  => $this->integer(),
            'since_id'       => $this->bigInteger(64)->defaultValue(0),
            'max_id'         => $this->bigInteger(64)->defaultValue(0),
            'publication_id' => $this->string(),
            'next'           => $this->string(),
            'createdAt'      => $this->integer(),
            'updatedAt'      => $this->integer(),
            'createdBy'      => $this->integer(),
            'updatedBy'      => $this->integer(),

        ],$tableOptions);

        /*$this->insert('{{%alerts_mencions}}', [
            'alertId'       => 1,
            'resourcesId'   => 1,
            //'condition'   => 'ACTIVE',
            'type'          => 'tweet',
            'term_searched' => "FullVision",
            'date_searched' => 0,
           // 'max_id'        => 1163482843902705665,
            'createdAt'     => 1559312912,
            'updatedAt'     => 1559312912,
            'createdBy'     => 1,
            'updatedBy'     => 1,
        ]);*/

        // creates index for column `alertId`
        $this->createIndex(
            'idx-alerts_mencions-alertId',
            'alerts_mencions',
            'alertId'
        );

        // add foreign key for table `alerts`
        $this->addForeignKey(
            'fk-alerts_mencions-alertId',
            'alerts_mencions',
            'alertId',
            'alerts',
            'id',
            'CASCADE',
            'CASCADE'
        );

         // creates index for column `resourcesId`
        $this->createIndex(
            'idx-alerts_mencions_resources',
            'alerts_mencions',
            'resourcesId'
        );

        // add foreign key for table `alerts_mencions`
        $this->addForeignKey(
            'fk-alerts_mencions_resources',
            'alerts_mencions',
            'resourcesId',
            'resources',
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
        $this->dropTable('{{%alerts_mencions}}');
    }

}
