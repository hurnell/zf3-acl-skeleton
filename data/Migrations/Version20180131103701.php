<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180131103701 extends AbstractMigration
{

    /**
     * Returns the description of this migration.
     */
    public function getDescription()
    {

        $description = 'This is the initial migration which creates the user table.';
        return $description;
    }

    /**
     * Upgrades the schema to its newer state.
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->createRoleTable($schema);
        $this->createUserTable($schema);
        $this->createUserRoleMapTable($schema);
        /*
          drop table if exists `user_role_map`;
          drop table if exists `user`;
          drop table if exists `role`;
          DELETE FROM `migrations`;
         */
    }

    public function createRoleTable(Schema $schema)
    {
        $table = $schema->createTable('role');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('active', 'boolean', ['notnull' => false, 'default' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addIndex(['parent_id'], 'parent_id_idx');
        $table->addForeignKeyConstraint('role', ['parent_id'], ['id'], [], 'role_recursive_key_fk');
        $table->addOption('engine', 'InnoDB');
    }

    private function createUserTable(Schema $schema)
    {
        $table = $schema->createTable('user');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('email', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('full_name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('password', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('photo', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('status', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->addColumn('pwd_reset_token', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('pwd_reset_token_date', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'email_idx');
        $table->addOption('engine', 'InnoDB');
    }

    private function createUserRoleMapTable(Schema $schema)
    {
        $table = $schema->createTable('user_role_map');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('role_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id'], 'user_id_idx');
        $table->addIndex(['role_id'], 'role_id_idx');
        $table->addForeignKeyConstraint('role', ['role_id'], ['id'], ["onDelete" => "CASCADE"], 'user_role_map_role_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], ["onDelete" => "CASCADE"], 'user_role_map_user_id_fk');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('user_role_map');
        $schema->dropTable('role');
        $schema->dropTable('user');
    }

}
