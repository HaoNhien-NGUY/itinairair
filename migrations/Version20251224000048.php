<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224000048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD discriminator VARCHAR(5) NOT NULL DEFAULT \'12345\'');
        $this->addSql('ALTER TABLE "user" ALTER username DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER username TYPE VARCHAR(22)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_DISCRIMINATOR ON "user" (username, discriminator)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_USER_DISCRIMINATOR');
        $this->addSql('ALTER TABLE "user" DROP discriminator');
        $this->addSql('ALTER TABLE "user" ALTER username SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE "user" ALTER username TYPE VARCHAR(255)');
    }
}
