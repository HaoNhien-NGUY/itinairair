<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204180043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE travel_item ADD trip_id INT DEFAULT NULL');

        $this->addSql('UPDATE travel_item SET trip_id = (SELECT day.trip_id FROM day WHERE day.id = travel_item.start_day_id)');

        $this->addSql('ALTER TABLE travel_item ADD CONSTRAINT FK_D443C6B3A5BC2E0E FOREIGN KEY (trip_id) REFERENCES trip (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D443C6B3A5BC2E0E ON travel_item (trip_id)');

        $this->addSql('ALTER TABLE travel_item ALTER COLUMN trip_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE travel_item DROP CONSTRAINT FK_D443C6B3A5BC2E0E');
        $this->addSql('DROP INDEX IDX_D443C6B3A5BC2E0E');
        $this->addSql('ALTER TABLE travel_item DROP trip_id');
    }
}
