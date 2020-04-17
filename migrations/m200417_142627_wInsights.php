<?php

use yii\db\Migration;

/**
 * Class m200417_142627_wInsights
 */
class m200417_142627_wInsights extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_insights}}',[
            'id'              => $this->primaryKey(),
            'content_id'      => $this->integer()->notNull(),
            'name'            => $this->string(),
            'title'           => $this->string(),
            'description'     => $this->text(),
            'insights_id'     => $this->string(),
            'period'          => $this->string(),
            'value'           => $this->integer(),
            '_like'           => $this->integer()->defaultValue(0),
            '_love'           => $this->integer()->defaultValue(0),
            '_wow'           => $this->integer()->defaultValue(0),
            '_haha'           => $this->integer()->defaultValue(0),
            '_sorry'           => $this->integer()->defaultValue(0),
            '_anger'           => $this->integer()->defaultValue(0),
            'end_time'        => $this->integer(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);


         // creates index for column `content_id`
        $this->createIndex(
            'idx-w_insights-content_id',
            'w_insights',
            'content_id'
        );

        // relation
        // add foreign key for table `seriesId`
        $this->addForeignKey(
            'fk-w_insights-w_insights',
            'w_insights',
            'content_id',
            'w_content',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%w_insights}}');
    }
}
