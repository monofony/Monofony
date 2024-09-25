<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240925102441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'PostgresSQL Init database';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            return;
        }

        $this->addSql('CREATE SEQUENCE app_admin_avatar_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sylius_admin_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sylius_app_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sylius_customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sylius_customer_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sylius_user_oauth_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE app_admin_avatar (id INT NOT NULL, path VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE sylius_admin_user (id INT NOT NULL, avatar_id INT DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, username_canonical VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, password VARCHAR(255) DEFAULT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locked BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, roles JSONB NOT NULL, email VARCHAR(255) DEFAULT NULL, email_canonical VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88D5CC4D6B7BA4B6 ON sylius_admin_user (password_reset_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88D5CC4DC4995C67 ON sylius_admin_user (email_verification_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88D5CC4D86383B10 ON sylius_admin_user (avatar_id)');
        $this->addSql('CREATE TABLE sylius_app_user (id INT NOT NULL, customer_id INT NOT NULL, username VARCHAR(255) DEFAULT NULL, username_canonical VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, password VARCHAR(255) DEFAULT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, email_verification_token VARCHAR(255) DEFAULT NULL, verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locked BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, roles JSONB NOT NULL, email VARCHAR(255) DEFAULT NULL, email_canonical VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7706A8066B7BA4B6 ON sylius_app_user (password_reset_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7706A806C4995C67 ON sylius_app_user (email_verification_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7706A8069395C3F3 ON sylius_app_user (customer_id)');
        $this->addSql('CREATE TABLE sylius_customer (id INT NOT NULL, customer_group_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, birthday TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, gender VARCHAR(1) DEFAULT \'u\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, subscribed_to_newsletter BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E82D5E6E7927C74 ON sylius_customer (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E82D5E6A0D96FBF ON sylius_customer (email_canonical)');
        $this->addSql('CREATE INDEX IDX_7E82D5E6D2919A68 ON sylius_customer (customer_group_id)');
        $this->addSql('CREATE TABLE sylius_customer_group (id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7FCF9B0577153098 ON sylius_customer_group (code)');
        $this->addSql('CREATE TABLE sylius_user_oauth (id INT NOT NULL, user_id INT DEFAULT NULL, provider VARCHAR(255) NOT NULL, identifier VARCHAR(255) NOT NULL, access_token TEXT DEFAULT NULL, refresh_token TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C3471B78A76ED395 ON sylius_user_oauth (user_id)');
        $this->addSql('CREATE UNIQUE INDEX user_provider ON sylius_user_oauth (user_id, provider)');
        $this->addSql('ALTER TABLE sylius_admin_user ADD CONSTRAINT FK_88D5CC4D86383B10 FOREIGN KEY (avatar_id) REFERENCES app_admin_avatar (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_app_user ADD CONSTRAINT FK_7706A8069395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_customer ADD CONSTRAINT FK_7E82D5E6D2919A68 FOREIGN KEY (customer_group_id) REFERENCES sylius_customer_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_user_oauth ADD CONSTRAINT FK_C3471B78A76ED395 FOREIGN KEY (user_id) REFERENCES sylius_admin_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            return;
        }

        $this->addSql('DROP SEQUENCE app_admin_avatar_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sylius_admin_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sylius_app_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sylius_customer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sylius_customer_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sylius_user_oauth_id_seq CASCADE');
        $this->addSql('ALTER TABLE sylius_admin_user DROP CONSTRAINT FK_88D5CC4D86383B10');
        $this->addSql('ALTER TABLE sylius_app_user DROP CONSTRAINT FK_7706A8069395C3F3');
        $this->addSql('ALTER TABLE sylius_customer DROP CONSTRAINT FK_7E82D5E6D2919A68');
        $this->addSql('ALTER TABLE sylius_user_oauth DROP CONSTRAINT FK_C3471B78A76ED395');
        $this->addSql('DROP TABLE app_admin_avatar');
        $this->addSql('DROP TABLE sylius_admin_user');
        $this->addSql('DROP TABLE sylius_app_user');
        $this->addSql('DROP TABLE sylius_customer');
        $this->addSql('DROP TABLE sylius_customer_group');
        $this->addSql('DROP TABLE sylius_user_oauth');
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('DROP TABLE refresh_tokens');
    }
}
