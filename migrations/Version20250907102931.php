<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250907102931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE day (id SERIAL NOT NULL, trip_id INT NOT NULL, position INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E5A02990A5BC2E0E ON day (trip_id)');
        $this->addSql('CREATE TABLE travel_item (id SERIAL NOT NULL, start_day_id INT NOT NULL, end_day_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, notes TEXT DEFAULT NULL, start_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, end_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(255) NOT NULL, discriminator VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, booking_reference VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, flight_number VARCHAR(10) DEFAULT NULL, departure_airport JSON DEFAULT NULL, arrival_airport JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D443C6B3C30DD6B6 ON travel_item (start_day_id)');
        $this->addSql('CREATE INDEX IDX_D443C6B379D92AEE ON travel_item (end_day_id)');
        $this->addSql('CREATE TABLE trip (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE trip_membership (id SERIAL NOT NULL, trip_id INT NOT NULL, member_id INT NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73ED0993A5BC2E0E ON trip_membership (trip_id)');
        $this->addSql('CREATE INDEX IDX_73ED09937597D3FE ON trip_membership (member_id)');
        $this->addSql('ALTER TABLE day ADD CONSTRAINT FK_E5A02990A5BC2E0E FOREIGN KEY (trip_id) REFERENCES trip (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE travel_item ADD CONSTRAINT FK_D443C6B3C30DD6B6 FOREIGN KEY (start_day_id) REFERENCES day (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE travel_item ADD CONSTRAINT FK_D443C6B379D92AEE FOREIGN KEY (end_day_id) REFERENCES day (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trip_membership ADD CONSTRAINT FK_73ED0993A5BC2E0E FOREIGN KEY (trip_id) REFERENCES trip (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trip_membership ADD CONSTRAINT FK_73ED09937597D3FE FOREIGN KEY (member_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE day DROP CONSTRAINT FK_E5A02990A5BC2E0E');
        $this->addSql('ALTER TABLE travel_item DROP CONSTRAINT FK_D443C6B3C30DD6B6');
        $this->addSql('ALTER TABLE travel_item DROP CONSTRAINT FK_D443C6B379D92AEE');
        $this->addSql('ALTER TABLE trip_membership DROP CONSTRAINT FK_73ED0993A5BC2E0E');
        $this->addSql('ALTER TABLE trip_membership DROP CONSTRAINT FK_73ED09937597D3FE');
        $this->addSql('DROP TABLE day');
        $this->addSql('DROP TABLE travel_item');
        $this->addSql('DROP TABLE trip');
        $this->addSql('DROP TABLE trip_membership');
    }
}
