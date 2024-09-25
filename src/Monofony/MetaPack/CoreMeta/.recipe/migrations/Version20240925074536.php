<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240925074536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove salt';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE sylius_admin_user DROP salt');
        $this->addSql('ALTER TABLE sylius_admin_user DROP encoder_name');
        $this->addSql('ALTER TABLE sylius_app_user DROP salt');
        $this->addSql('ALTER TABLE sylius_app_user DROP encoder_name');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE sylius_admin_user ADD salt VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_admin_user ADD encoder_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_app_user ADD salt VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE sylius_app_user ADD encoder_name VARCHAR(255) NULL');
    }
}
