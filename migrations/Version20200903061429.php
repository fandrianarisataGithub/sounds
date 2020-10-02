<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200903061429 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE hotel (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD hotel_id INT NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404553243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('CREATE INDEX IDX_C74404553243BB18 ON client (hotel_id)');
        $this->addSql('ALTER TABLE donnee_du_jour ADD hotel_id INT NOT NULL, CHANGE heb_to heb_to INT NOT NULL, CHANGE heb_ca heb_ca DOUBLE PRECISION NOT NULL, CHANGE res_n_couvert res_n_couvert INT NOT NULL, CHANGE res_ca res_ca INT NOT NULL, CHANGE res_p_dej res_p_dej INT NOT NULL, CHANGE res_dej res_dej INT NOT NULL, CHANGE res_dinner res_dinner INT NOT NULL, CHANGE spa_ca spa_ca DOUBLE PRECISION NOT NULL, CHANGE spa_n_abonne spa_n_abonne INT NOT NULL, CHANGE spa_c_unique spa_c_unique INT NOT NULL');
        $this->addSql('ALTER TABLE donnee_du_jour ADD CONSTRAINT FK_66B234443243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('CREATE INDEX IDX_66B234443243BB18 ON donnee_du_jour (hotel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404553243BB18');
        $this->addSql('ALTER TABLE donnee_du_jour DROP FOREIGN KEY FK_66B234443243BB18');
        $this->addSql('DROP TABLE hotel');
        $this->addSql('DROP INDEX IDX_C74404553243BB18 ON client');
        $this->addSql('ALTER TABLE client DROP hotel_id');
        $this->addSql('DROP INDEX IDX_66B234443243BB18 ON donnee_du_jour');
        $this->addSql('ALTER TABLE donnee_du_jour DROP hotel_id, CHANGE heb_to heb_to INT DEFAULT NULL, CHANGE heb_ca heb_ca DOUBLE PRECISION DEFAULT NULL, CHANGE res_n_couvert res_n_couvert INT DEFAULT NULL, CHANGE res_ca res_ca INT DEFAULT NULL, CHANGE res_p_dej res_p_dej INT DEFAULT NULL, CHANGE res_dej res_dej INT DEFAULT NULL, CHANGE res_dinner res_dinner INT DEFAULT NULL, CHANGE spa_ca spa_ca DOUBLE PRECISION DEFAULT NULL, CHANGE spa_n_abonne spa_n_abonne INT DEFAULT NULL, CHANGE spa_c_unique spa_c_unique INT DEFAULT NULL');
    }
}
