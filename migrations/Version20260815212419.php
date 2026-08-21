<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260815212419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP CONSTRAINT fk_fe38f844a76ed395');
        $this->addSql('DROP INDEX idx_fe38f844a76ed395');
        $this->addSql('ALTER TABLE appointment ADD client_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE appointment ADD client_email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE appointment ADD client_phone TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD confirmed_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD admin_notes TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment DROP time_slot');
        $this->addSql('ALTER TABLE appointment DROP user_id');
        $this->addSql('ALTER TABLE appointment RENAME COLUMN date TO requested_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD time_slot VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE appointment ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE appointment DROP client_name');
        $this->addSql('ALTER TABLE appointment DROP client_email');
        $this->addSql('ALTER TABLE appointment DROP client_phone');
        $this->addSql('ALTER TABLE appointment DROP confirmed_date');
        $this->addSql('ALTER TABLE appointment DROP admin_notes');
        $this->addSql('ALTER TABLE appointment DROP updated_at');
        $this->addSql('ALTER TABLE appointment RENAME COLUMN requested_date TO date');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT fk_fe38f844a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_fe38f844a76ed395 ON appointment (user_id)');
    }
}
