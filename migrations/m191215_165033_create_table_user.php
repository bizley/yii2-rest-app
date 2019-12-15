<?php

use yii\db\Migration;

class m191215_165033_create_table_user extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(191)->notNull()->unique(),
            'password' => $this->string(191)->notNull(),
            'auth_key' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
