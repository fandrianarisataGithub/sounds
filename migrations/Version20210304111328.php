<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210304111328 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tresorerie (id INT AUTO_INCREMENT NOT NULL, date DATETIME DEFAULT NULL, designation VARCHAR(255) DEFAULT NULL, num_sage VARCHAR(255) DEFAULT NULL, mode_paiement VARCHAR(255) DEFAULT NULL, compte_bancaire VARCHAR(255) DEFAULT NULL, monnaie VARCHAR(255) DEFAULT NULL, paiement DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tresorerie_depense (id INT AUTO_INCREMENT NOT NULL, date DATETIME DEFAULT NULL, designation VARCHAR(255) DEFAULT NULL, num_sage VARCHAR(255) DEFAULT NULL, mode_paiement VARCHAR(255) DEFAULT NULL, compte_bancaire VARCHAR(255) DEFAULT NULL, monnaie VARCHAR(255) DEFAULT NULL, paiement DOUBLE PRECISION DEFAULT NULL, num_compte VARCHAR(255) DEFAULT NULL, nom_fournisseur VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tresorerie_recette (id INT AUTO_INCREMENT NOT NULL, date DATETIME DEFAULT NULL, designation VARCHAR(255) DEFAULT NULL, num_sage VARCHAR(255) DEFAULT NULL, mode_paiement VARCHAR(255) DEFAULT NULL, compte_bancaire VARCHAR(255) DEFAULT NULL, monnaie VARCHAR(255) DEFAULT NULL, paiement DOUBLE PRECISION DEFAULT NULL, id_pro VARCHAR(255) DEFAULT NULL, nom_client VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tresorerie');
        $this->addSql('DROP TABLE tresorerie_depense');
        $this->addSql('DROP TABLE tresorerie_recette');
    }
}
