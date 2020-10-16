<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201016080626 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fiche_hotel ADD hotel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_hotel ADD CONSTRAINT FK_79D3B4E63243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_79D3B4E63243BB18 ON fiche_hotel (hotel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fiche_hotel DROP FOREIGN KEY FK_79D3B4E63243BB18');
        $this->addSql('DROP INDEX UNIQ_79D3B4E63243BB18 ON fiche_hotel');
        $this->addSql('ALTER TABLE fiche_hotel DROP hotel_id');
    }
}
