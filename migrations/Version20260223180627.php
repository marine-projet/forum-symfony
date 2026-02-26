<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223180627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD subject_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA23EDC87 FOREIGN KEY (subject_id) REFERENCES sujet (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA23EDC87 ON notification (subject_id)');
        $this->addSql('ALTER TABLE user DROP profile_picture');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA23EDC87');
        $this->addSql('DROP INDEX IDX_BF5476CA23EDC87 ON notification');
        $this->addSql('ALTER TABLE notification DROP subject_id');
        $this->addSql('ALTER TABLE `user` ADD profile_picture VARCHAR(255) DEFAULT NULL');
    }
}
