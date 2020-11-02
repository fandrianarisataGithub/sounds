<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102072115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE client_upload_hotel (client_upload_id INT NOT NULL, hotel_id INT NOT NULL, INDEX IDX_9B04B1C727EF6A3E (client_upload_id), INDEX IDX_9B04B1C73243BB18 (hotel_id), PRIMARY KEY(client_upload_id, hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_upload_hotel ADD CONSTRAINT FK_9B04B1C727EF6A3E FOREIGN KEY (client_upload_id) REFERENCES client_upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_upload_hotel ADD CONSTRAINT FK_9B04B1C73243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE client_upload_hotel');
    }
}
