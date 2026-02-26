<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223173253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD message VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL, CHANGE is_read is_read TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user DROP updated_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP message, DROP created_at, CHANGE is_read is_read TINYINT NOT NULL');
        $this->addSql('ALTER TABLE `user` ADD updated_at DATETIME DEFAULT NULL');
    }
}
