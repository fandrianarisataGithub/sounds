<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210127074023 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, hotel_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, date_arrivee DATETIME NOT NULL, date_depart DATETIME NOT NULL, duree_sejour INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_C74404553243BB18 (hotel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_upload (id INT AUTO_INCREMENT NOT NULL, annee VARCHAR(255) DEFAULT NULL, type_client VARCHAR(255) DEFAULT NULL, numero_facture VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, personne_hebergee VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, date DATETIME DEFAULT NULL, montant_payer VARCHAR(255) DEFAULT NULL, date_pmt DATETIME DEFAULT NULL, mode_pmt VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_upload_hotel (client_upload_id INT NOT NULL, hotel_id INT NOT NULL, INDEX IDX_9B04B1C727EF6A3E (client_upload_id), INDEX IDX_9B04B1C73243BB18 (hotel_id), PRIMARY KEY(client_upload_id, hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_entreprise_tw (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT DEFAULT NULL, nom_en_contact VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, INDEX IDX_FEE4C1AFA4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_tw (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data_tropical_wood (id INT AUTO_INCREMENT NOT NULL, entreprise VARCHAR(255) DEFAULT NULL, contact VARCHAR(255) DEFAULT NULL, detail VARCHAR(255) DEFAULT NULL, type_transaction VARCHAR(255) DEFAULT NULL, etat_production VARCHAR(255) DEFAULT NULL, paiement VARCHAR(255) DEFAULT NULL, montant_total NUMERIC(10, 0) DEFAULT NULL, date_confirmation DATETIME DEFAULT NULL, devis LONGTEXT DEFAULT NULL, montant_paye NUMERIC(10, 0) DEFAULT NULL, id_pro VARCHAR(255) DEFAULT NULL, total_reglement NUMERIC(10, 0) DEFAULT NULL, reste DOUBLE PRECISION DEFAULT NULL, date_facture DATETIME NOT NULL, etape_production DOUBLE PRECISION DEFAULT NULL, date_paiement_prevu DATETIME DEFAULT NULL, date_paiement_effectif DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE donnee_du_jour (id INT AUTO_INCREMENT NOT NULL, hotel_id INT NOT NULL, heb_to VARCHAR(255) DEFAULT NULL, heb_ca VARCHAR(255) DEFAULT NULL, res_n_couvert VARCHAR(255) DEFAULT NULL, res_ca VARCHAR(255) DEFAULT NULL, res_p_dej VARCHAR(255) DEFAULT NULL, res_dej VARCHAR(255) DEFAULT NULL, res_dinner VARCHAR(255) DEFAULT NULL, spa_ca VARCHAR(255) DEFAULT NULL, spa_n_abonne VARCHAR(255) DEFAULT NULL, spa_c_unique VARCHAR(255) DEFAULT NULL, crj_direction LONGTEXT DEFAULT NULL, crj_service_rh LONGTEXT DEFAULT NULL, crj_commercial LONGTEXT DEFAULT NULL, crj_comptable LONGTEXT DEFAULT NULL, crj_reception LONGTEXT DEFAULT NULL, crj_restaurant LONGTEXT DEFAULT NULL, crj_spa LONGTEXT DEFAULT NULL, crj_s_technique LONGTEXT DEFAULT NULL, crj_litiges LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_66B234443243BB18 (hotel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise_tw (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fiche_hotel (id INT AUTO_INCREMENT NOT NULL, hotel_id INT DEFAULT NULL, c_prestige INT NOT NULL, s_familliale INT NOT NULL, c_deluxe INT NOT NULL, s_vip INT NOT NULL, le_nautile INT NOT NULL, sunset_view INT NOT NULL, UNIQUE INDEX UNIQ_79D3B4E63243BB18 (hotel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL, numero_facture VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, nom_fournisseur VARCHAR(255) DEFAULT NULL, montant VARCHAR(255) DEFAULT NULL, echeance DATETIME DEFAULT NULL, mode_pmt VARCHAR(255) DEFAULT NULL, montant_paye VARCHAR(255) DEFAULT NULL, date_pmt DATETIME DEFAULT NULL, remarque VARCHAR(255) DEFAULT NULL, reste VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur_hotel (fournisseur_id INT NOT NULL, hotel_id INT NOT NULL, INDEX IDX_1E05EB2C670C757F (fournisseur_id), INDEX IDX_1E05EB2C3243BB18 (hotel_id), PRIMARY KEY(fournisseur_id, hotel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hotel (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, pseudo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hotel_user (hotel_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3A5600C13243BB18 (hotel_id), INDEX IDX_3A5600C1A76ED395 (user_id), PRIMARY KEY(hotel_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE remarque_entreprise_tw (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT DEFAULT NULL, date_remarque DATETIME NOT NULL, concerne VARCHAR(255) NOT NULL, observation VARCHAR(255) NOT NULL, etat_resultat VARCHAR(255) NOT NULL, INDEX IDX_FCD154A0A4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, username VARCHAR(255) NOT NULL, hotel VARCHAR(255) NOT NULL, profile VARCHAR(255) DEFAULT NULL, pass_clair VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404553243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('ALTER TABLE client_upload_hotel ADD CONSTRAINT FK_9B04B1C727EF6A3E FOREIGN KEY (client_upload_id) REFERENCES client_upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_upload_hotel ADD CONSTRAINT FK_9B04B1C73243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_entreprise_tw ADD CONSTRAINT FK_FEE4C1AFA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise_tw (id)');
        $this->addSql('ALTER TABLE donnee_du_jour ADD CONSTRAINT FK_66B234443243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('ALTER TABLE fiche_hotel ADD CONSTRAINT FK_79D3B4E63243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('ALTER TABLE fournisseur_hotel ADD CONSTRAINT FK_1E05EB2C670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fournisseur_hotel ADD CONSTRAINT FK_1E05EB2C3243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hotel_user ADD CONSTRAINT FK_3A5600C13243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hotel_user ADD CONSTRAINT FK_3A5600C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE remarque_entreprise_tw ADD CONSTRAINT FK_FCD154A0A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise_tw (id)');
        $this->addSql('ALTER TABLE donnee_mensuelle CHANGE stock stock VARCHAR(255) DEFAULT NULL, CHANGE cost_restaurant_value cost_restaurant_value VARCHAR(255) DEFAULT NULL, CHANGE cost_restaurant_pourcent cost_restaurant_pourcent VARCHAR(255) DEFAULT NULL, CHANGE cost_electricite_value cost_electricite_value VARCHAR(255) DEFAULT NULL, CHANGE cost_electricite_pourcent cost_electricite_pourcent VARCHAR(255) DEFAULT NULL, CHANGE cost_eau_value cost_eau_value VARCHAR(255) DEFAULT NULL, CHANGE cost_eau_pourcent cost_eau_pourcent VARCHAR(255) DEFAULT NULL, CHANGE cost_gasoil_value cost_gasoil_value VARCHAR(255) DEFAULT NULL, CHANGE cost_gasoil_pourcent cost_gasoil_pourcent VARCHAR(255) DEFAULT NULL, CHANGE salaire_brute_value salaire_brute_value VARCHAR(255) DEFAULT NULL, CHANGE salaire_brute_pourcent salaire_brute_pourcent VARCHAR(255) DEFAULT NULL, CHANGE sqn_interne sqn_interne VARCHAR(255) DEFAULT NULL, CHANGE sqn_booking sqn_booking VARCHAR(255) DEFAULT NULL, CHANGE sqn_tripadvisor sqn_tripadvisor VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE donnee_mensuelle ADD CONSTRAINT FK_96CC97863243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('CREATE INDEX IDX_96CC97863243BB18 ON donnee_mensuelle (hotel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE client_upload_hotel DROP FOREIGN KEY FK_9B04B1C727EF6A3E');
        $this->addSql('ALTER TABLE contact_entreprise_tw DROP FOREIGN KEY FK_FEE4C1AFA4AEAFEA');
        $this->addSql('ALTER TABLE remarque_entreprise_tw DROP FOREIGN KEY FK_FCD154A0A4AEAFEA');
        $this->addSql('ALTER TABLE fournisseur_hotel DROP FOREIGN KEY FK_1E05EB2C670C757F');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404553243BB18');
        $this->addSql('ALTER TABLE client_upload_hotel DROP FOREIGN KEY FK_9B04B1C73243BB18');
        $this->addSql('ALTER TABLE donnee_du_jour DROP FOREIGN KEY FK_66B234443243BB18');
        $this->addSql('ALTER TABLE donnee_mensuelle DROP FOREIGN KEY FK_96CC97863243BB18');
        $this->addSql('ALTER TABLE fiche_hotel DROP FOREIGN KEY FK_79D3B4E63243BB18');
        $this->addSql('ALTER TABLE fournisseur_hotel DROP FOREIGN KEY FK_1E05EB2C3243BB18');
        $this->addSql('ALTER TABLE hotel_user DROP FOREIGN KEY FK_3A5600C13243BB18');
        $this->addSql('ALTER TABLE hotel_user DROP FOREIGN KEY FK_3A5600C1A76ED395');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_upload');
        $this->addSql('DROP TABLE client_upload_hotel');
        $this->addSql('DROP TABLE contact_entreprise_tw');
        $this->addSql('DROP TABLE contact_tw');
        $this->addSql('DROP TABLE data_tropical_wood');
        $this->addSql('DROP TABLE donnee_du_jour');
        $this->addSql('DROP TABLE entreprise_tw');
        $this->addSql('DROP TABLE fiche_hotel');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE fournisseur_hotel');
        $this->addSql('DROP TABLE hotel');
        $this->addSql('DROP TABLE hotel_user');
        $this->addSql('DROP TABLE remarque_entreprise_tw');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_96CC97863243BB18 ON donnee_mensuelle');
        $this->addSql('ALTER TABLE donnee_mensuelle CHANGE stock stock DOUBLE PRECISION DEFAULT NULL, CHANGE cost_restaurant_value cost_restaurant_value DOUBLE PRECISION DEFAULT NULL, CHANGE cost_restaurant_pourcent cost_restaurant_pourcent DOUBLE PRECISION DEFAULT NULL, CHANGE cost_electricite_value cost_electricite_value DOUBLE PRECISION DEFAULT NULL, CHANGE cost_electricite_pourcent cost_electricite_pourcent VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE cost_eau_value cost_eau_value DOUBLE PRECISION DEFAULT NULL, CHANGE cost_eau_pourcent cost_eau_pourcent VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE cost_gasoil_value cost_gasoil_value DOUBLE PRECISION DEFAULT NULL, CHANGE cost_gasoil_pourcent cost_gasoil_pourcent DOUBLE PRECISION DEFAULT NULL, CHANGE salaire_brute_value salaire_brute_value DOUBLE PRECISION DEFAULT NULL, CHANGE salaire_brute_pourcent salaire_brute_pourcent DOUBLE PRECISION DEFAULT NULL, CHANGE sqn_interne sqn_interne DOUBLE PRECISION DEFAULT NULL, CHANGE sqn_booking sqn_booking DOUBLE PRECISION DEFAULT NULL, CHANGE sqn_tripadvisor sqn_tripadvisor DOUBLE PRECISION DEFAULT NULL');
    }
}
