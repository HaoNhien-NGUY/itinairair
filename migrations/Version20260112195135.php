<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112195135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE day ADD date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        $this->addSql('
            UPDATE day
            SET date = trip.start_date + ((day.position - 1) * INTERVAL \'1 day\')
            FROM trip
            WHERE day.trip_id = trip.id
        ');

        // 3. Make column not null
        $this->addSql('ALTER TABLE day ALTER COLUMN date SET NOT NULL');
        $this->addSql('ALTER TABLE day ALTER COLUMN date DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE day DROP date');
    }
}
