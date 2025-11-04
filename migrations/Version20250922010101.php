<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922010101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE travel_item ADD place_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE travel_item ADD CONSTRAINT FK_D443C6B3DA6A219 FOREIGN KEY (place_id) REFERENCES place (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D443C6B3DA6A219 ON travel_item (place_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE travel_item DROP CONSTRAINT FK_D443C6B3DA6A219');
        $this->addSql('DROP INDEX IDX_D443C6B3DA6A219');
        $this->addSql('ALTER TABLE travel_item DROP place_id');
    }
}
