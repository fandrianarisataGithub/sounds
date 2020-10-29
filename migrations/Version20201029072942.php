<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201029072942 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fournisseur (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, numero_facture VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, nom_fournisseur VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) NOT NULL, echeance VARCHAR(255) DEFAULT NULL, mode_pmt VARCHAR(255) NOT NULL, montant_paye VARCHAR(255) DEFAULT NULL, date_pmt DATETIME DEFAULT NULL, remarque VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur_hotel (fournisseur_id INT NOT NULL, hotel_id INT NOT NULL, INDEX IDX_1E05EB2C670C757F (fournisseur_id), INDEX IDX_1E05EB2C3243BB18 (hotel_id), PRIMARY KEY(fournisseur_id, hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fournisseur_hotel ADD CONSTRAINT FK_1E05EB2C670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fournisseur_hotel ADD CONSTRAINT FK_1E05EB2C3243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fournisseur_hotel DROP FOREIGN KEY FK_1E05EB2C670C757F');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE fournisseur_hotel');
    }
}
