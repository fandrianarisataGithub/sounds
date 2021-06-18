<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210618072312 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contact_tw (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visit (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, hotel_id INT DEFAULT NULL, checkin DATETIME NOT NULL, checkout DATETIME NOT NULL, provenance VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, nbr_nuitee INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_437EE9399395C3F3 (customer_id), INDEX IDX_437EE9393243BB18 (hotel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE visit ADD CONSTRAINT FK_437EE9399395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE visit ADD CONSTRAINT FK_437EE9393243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('DROP TABLE tresorerie_depense');
        $this->addSql('DROP TABLE tresorerie_recette');
        $this->addSql('ALTER TABLE client RENAME INDEX fk_c74404552733eb1b TO IDX_C74404552733EB1B');
        $this->addSql('ALTER TABLE client_upload_hotel ADD CONSTRAINT FK_9B04B1C727EF6A3E FOREIGN KEY (client_upload_id) REFERENCES client_upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE data_tropical_wood CHANGE date_facture date_facture DATETIME DEFAULT NULL, CHANGE date_paiement_prevu date_paiement_prevu DATETIME DEFAULT NULL, CHANGE date_paiement_effectif date_paiement_effectif DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE donnee_du_jour ADD CONSTRAINT FK_66B234443243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('ALTER TABLE donnee_mensuelle ADD CONSTRAINT FK_96CC97863243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('ALTER TABLE remarque_entreprise_tw ADD CONSTRAINT FK_FCD154A0A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise_tw (id)');
        $this->addSql('ALTER TABLE sous_categorie_tresorerie ADD CONSTRAINT FK_B625789DBCF5E72D FOREIGN KEY (categorie_id) REFERENCES category_tresorerie (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE receptionniste receptionniste VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE visit DROP FOREIGN KEY FK_437EE9399395C3F3');
        $this->addSql('CREATE TABLE tresorerie_depense (id INT AUTO_INCREMENT NOT NULL, date DATETIME DEFAULT NULL, designation VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, num_sage VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, mode_paiement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, compte_bancaire VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, monnaie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, paiement DOUBLE PRECISION DEFAULT NULL, num_compte VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, nom_fournisseur VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tresorerie_recette (id INT AUTO_INCREMENT NOT NULL, date DATETIME DEFAULT NULL, designation VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, num_sage VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, mode_paiement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, compte_bancaire VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, monnaie VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, paiement DOUBLE PRECISION DEFAULT NULL, id_pro VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, nom_client VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE contact_tw');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE visit');
        $this->addSql('ALTER TABLE client RENAME INDEX idx_c74404552733eb1b TO FK_C74404552733EB1B');
        $this->addSql('ALTER TABLE client_upload_hotel DROP FOREIGN KEY FK_9B04B1C727EF6A3E');
        $this->addSql('ALTER TABLE data_tropical_wood CHANGE date_facture date_facture DATE DEFAULT NULL, CHANGE date_paiement_prevu date_paiement_prevu DATE DEFAULT NULL, CHANGE date_paiement_effectif date_paiement_effectif DATE DEFAULT NULL, CHANGE created_at created_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE donnee_du_jour DROP FOREIGN KEY FK_66B234443243BB18');
        $this->addSql('ALTER TABLE donnee_mensuelle DROP FOREIGN KEY FK_96CC97863243BB18');
        $this->addSql('ALTER TABLE remarque_entreprise_tw DROP FOREIGN KEY FK_FCD154A0A4AEAFEA');
        $this->addSql('ALTER TABLE sous_categorie_tresorerie DROP FOREIGN KEY FK_B625789DBCF5E72D');
        $this->addSql('ALTER TABLE user CHANGE roles roles VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE receptionniste receptionniste VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
