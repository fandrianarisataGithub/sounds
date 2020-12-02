<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201130113856 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE client_upload (id INT AUTO_INCREMENT NOT NULL, annee VARCHAR(255) DEFAULT NULL, type_client VARCHAR(255) DEFAULT NULL, numero_facture VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, personne_hebergee VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, date DATETIME DEFAULT NULL, montant_payer VARCHAR(255) DEFAULT NULL, date_pmt DATETIME DEFAULT NULL, mode_pmt VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_upload_hotel (client_upload_id INT NOT NULL, hotel_id INT NOT NULL, INDEX IDX_9B04B1C727EF6A3E (client_upload_id), INDEX IDX_9B04B1C73243BB18 (hotel_id), PRIMARY KEY(client_upload_id, hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data_tropical_wood (id INT AUTO_INCREMENT NOT NULL, entreprise VARCHAR(255) DEFAULT NULL, contact VARCHAR(255) DEFAULT NULL, detail VARCHAR(255) DEFAULT NULL, type_transaction VARCHAR(255) DEFAULT NULL, etat_production VARCHAR(255) DEFAULT NULL, paiement VARCHAR(255) DEFAULT NULL, montant_total NUMERIC(10, 0) DEFAULT NULL, date_confirmation DATETIME DEFAULT NULL, devis LONGTEXT DEFAULT NULL, montant_paye NUMERIC(10, 0) DEFAULT NULL, id_pro VARCHAR(255) DEFAULT NULL, total_reglement NUMERIC(10, 0) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fiche_hotel (id INT AUTO_INCREMENT NOT NULL, hotel_id INT DEFAULT NULL, c_prestige INT NOT NULL, s_familliale INT NOT NULL, c_deluxe INT NOT NULL, s_vip INT NOT NULL, le_nautile INT NOT NULL, sunset_view INT NOT NULL, UNIQUE INDEX UNIQ_79D3B4E63243BB18 (hotel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, numero_facture VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, nom_fournisseur VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, echeance DATETIME DEFAULT NULL, mode_pmt VARCHAR(255) DEFAULT NULL, montant_paye VARCHAR(255) DEFAULT NULL, date_pmt DATETIME DEFAULT NULL, remarque VARCHAR(255) DEFAULT NULL, reste VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur_hotel (fournisseur_id INT NOT NULL, hotel_id INT NOT NULL, INDEX IDX_1E05EB2C670C757F (fournisseur_id), INDEX IDX_1E05EB2C3243BB18 (hotel_id), PRIMARY KEY(fournisseur_id, hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hotel (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, pseudo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hotel_user (hotel_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3A5600C13243BB18 (hotel_id), INDEX IDX_3A5600C1A76ED395 (user_id), PRIMARY KEY(hotel_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_upload_hotel ADD CONSTRAINT FK_9B04B1C727EF6A3E FOREIGN KEY (client_upload_id) REFERENCES client_upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_upload_hotel ADD CONSTRAINT FK_9B04B1C73243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fiche_hotel ADD CONSTRAINT FK_79D3B4E63243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('ALTER TABLE fournisseur_hotel ADD CONSTRAINT FK_1E05EB2C670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fournisseur_hotel ADD CONSTRAINT FK_1E05EB2C3243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hotel_user ADD CONSTRAINT FK_3A5600C13243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hotel_user ADD CONSTRAINT FK_3A5600C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client ADD hotel_id INT NOT NULL, ADD created_at DATETIME NOT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE duree_sejour duree_sejour INT NOT NULL, CHANGE date_arrive date_arrivee DATETIME NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404553243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('CREATE INDEX IDX_C74404553243BB18 ON client (hotel_id)');
        $this->addSql('ALTER TABLE donnee_du_jour ADD hotel_id INT NOT NULL, CHANGE heb_to heb_to VARCHAR(255) DEFAULT NULL, CHANGE heb_ca heb_ca VARCHAR(255) DEFAULT NULL, CHANGE res_n_couvert res_n_couvert VARCHAR(255) DEFAULT NULL, CHANGE res_ca res_ca VARCHAR(255) DEFAULT NULL, CHANGE res_p_dej res_p_dej VARCHAR(255) DEFAULT NULL, CHANGE res_dej res_dej VARCHAR(255) DEFAULT NULL, CHANGE res_dinner res_dinner VARCHAR(255) DEFAULT NULL, CHANGE spa_ca spa_ca VARCHAR(255) DEFAULT NULL, CHANGE spa_n_abonne spa_n_abonne VARCHAR(255) DEFAULT NULL, CHANGE spa_c_unique spa_c_unique VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE donnee_du_jour ADD CONSTRAINT FK_66B234443243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('CREATE INDEX IDX_66B234443243BB18 ON donnee_du_jour (hotel_id)');
        $this->addSql('ALTER TABLE user ADD pass_clair VARCHAR(255) DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE client_upload_hotel DROP FOREIGN KEY FK_9B04B1C727EF6A3E');
        $this->addSql('ALTER TABLE fournisseur_hotel DROP FOREIGN KEY FK_1E05EB2C670C757F');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404553243BB18');
        $this->addSql('ALTER TABLE client_upload_hotel DROP FOREIGN KEY FK_9B04B1C73243BB18');
        $this->addSql('ALTER TABLE donnee_du_jour DROP FOREIGN KEY FK_66B234443243BB18');
        $this->addSql('ALTER TABLE fiche_hotel DROP FOREIGN KEY FK_79D3B4E63243BB18');
        $this->addSql('ALTER TABLE fournisseur_hotel DROP FOREIGN KEY FK_1E05EB2C3243BB18');
        $this->addSql('ALTER TABLE hotel_user DROP FOREIGN KEY FK_3A5600C13243BB18');
        $this->addSql('DROP TABLE client_upload');
        $this->addSql('DROP TABLE client_upload_hotel');
        $this->addSql('DROP TABLE data_tropical_wood');
        $this->addSql('DROP TABLE fiche_hotel');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE fournisseur_hotel');
        $this->addSql('DROP TABLE hotel');
        $this->addSql('DROP TABLE hotel_user');
        $this->addSql('DROP INDEX IDX_C74404553243BB18 ON client');
        $this->addSql('ALTER TABLE client ADD date_arrive DATETIME NOT NULL, DROP hotel_id, DROP date_arrivee, DROP created_at, CHANGE prenom prenom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE duree_sejour duree_sejour VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:dateinterval)\'');
        $this->addSql('DROP INDEX IDX_66B234443243BB18 ON donnee_du_jour');
        $this->addSql('ALTER TABLE donnee_du_jour DROP hotel_id, CHANGE heb_to heb_to INT NOT NULL, CHANGE heb_ca heb_ca DOUBLE PRECISION NOT NULL, CHANGE res_n_couvert res_n_couvert INT NOT NULL, CHANGE res_ca res_ca INT NOT NULL, CHANGE res_p_dej res_p_dej INT NOT NULL, CHANGE res_dej res_dej INT NOT NULL, CHANGE res_dinner res_dinner INT NOT NULL, CHANGE spa_ca spa_ca DOUBLE PRECISION NOT NULL, CHANGE spa_n_abonne spa_n_abonne INT NOT NULL, CHANGE spa_c_unique spa_c_unique INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP pass_clair, DROP image, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE prenom prenom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
