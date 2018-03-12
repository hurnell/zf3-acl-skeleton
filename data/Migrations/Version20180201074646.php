<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180201074646 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addRoleTableData();
        $this->addUserTableData();
        $this->addUserRoleMapTableData();
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    private function addRoleTableData()
    {
        $data = [
            "1, NULL, 'basic', 'The role that corresponds to a guest', 1",
            "2, 1, 'base-translate', 'base permissions for all users that have ANY access to translate module', 1",
            "3, 2, 'site-language-admin', 'role that is permitted to change the languages that are enabled throughout the site', 1",
            "4, 2, 'uber-translator', 'role that can translate all site languages', 1",
            "5, 2, 'user-manager', 'role that can update users', 1",
            "6, 2, 'dutch-translator', 'role that can translate Dutch for site ', 1"
        ];
        foreach ($data as $values) {
            $this->addSql('INSERT INTO role (`id`, `parent_id`, `name`, `description`, `active`) VALUES (' . $values . ')');
        }
    }

    private function addUserTableData()
    {
        $datetime = date('Y-m-d H:i:s');
        $data = [
            '1, "admin@application.com", "Admin", "$2y$10$nuG0x78kdCEDBNt.AT5iPuR.uXJl8tu.j955rwxA4VK9t4HKSevvW", 0, 1, "' . $datetime . '", NULL, NULL',
            '2, "test@application.com", "Test User", "$2y$10$ecAR1mHgLkJEF9jcF.qyTe2YlWn1kDNWI38b1KzIegBD.D2N5udae", 0, 1, "' . $datetime . '", NULL, NULL'
        ];
        foreach ($data as $values) {
            $this->addSql('INSERT INTO user (`id`, `email`, `full_name`, `password`, `photo`, `status`, `date_created`, `pwd_reset_token`, `pwd_reset_token_date`) VALUES (' . $values . ')');
        }
    }

    private function addUserRoleMapTableData()
    {
        $index = 0;
        for ($i = 1; $i <= 6; $i++) {
            $index++;
            $this->addSql("INSERT INTO `user_role_map` (`id`, `user_id`, `role_id`) VALUES ({$index}, 1,{$i});");
        }

        for ($j = 1; $j <= 2; $j++) {
            $index++;
            $this->addSql("INSERT INTO `user_role_map` (`id`, `user_id`, `role_id`) VALUES ({$index}, 2,{$j});");
        }
    }

}
