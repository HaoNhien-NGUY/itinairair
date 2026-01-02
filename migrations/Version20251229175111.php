<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229175111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE travel_item ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE travel_item ADD CONSTRAINT FK_D443C6B3F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D443C6B3F675F31B ON travel_item (author_id)');
        $this->addSql('ALTER TABLE "user" ALTER discriminator DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" ALTER discriminator SET DEFAULT \'12345\'');
        $this->addSql('ALTER TABLE travel_item DROP CONSTRAINT FK_D443C6B3F675F31B');
        $this->addSql('DROP INDEX IDX_D443C6B3F675F31B');
        $this->addSql('ALTER TABLE travel_item DROP author_id');
    }
}
