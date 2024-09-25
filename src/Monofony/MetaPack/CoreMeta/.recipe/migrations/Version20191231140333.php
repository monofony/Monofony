<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191231140333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            return;
        }

        $this->addSql('CREATE TABLE app_admin_avatar (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_admin_user ADD avatar_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_admin_user ADD CONSTRAINT FK_88D5CC4D86383B10 FOREIGN KEY (avatar_id) REFERENCES app_admin_avatar (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88D5CC4D86383B10 ON sylius_admin_user (avatar_id)');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE sylius_admin_user DROP FOREIGN KEY FK_88D5CC4D86383B10');
        $this->addSql('DROP TABLE app_admin_avatar');
        $this->addSql('DROP INDEX UNIQ_88D5CC4D86383B10 ON sylius_admin_user');
        $this->addSql('ALTER TABLE sylius_admin_user DROP avatar_id');
    }
}
