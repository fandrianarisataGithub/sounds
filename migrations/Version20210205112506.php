<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210205112506 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE changement_after_import (id INT AUTO_INCREMENT NOT NULL, liste_pfupdated_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, last_data VARCHAR(255) NOT NULL, next_data VARCHAR(255) NOT NULL, INDEX IDX_B5E7ADC13BDD64EA (liste_pfupdated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_updated (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_updated_interval_change_pf (client_updated_id INT NOT NULL, interval_change_pf_id INT NOT NULL, INDEX IDX_17523A7B658FAB2 (client_updated_id), INDEX IDX_17523A7BECBEA75C (interval_change_pf_id), PRIMARY KEY(client_updated_id, interval_change_pf_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE interval_change_pf (id INT AUTO_INCREMENT NOT NULL, date_last DATETIME DEFAULT NULL, date_next DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE liste_pfupdated (id INT AUTO_INCREMENT NOT NULL, client_updated_id INT DEFAULT NULL, id_pro VARCHAR(255) NOT NULL, INDEX IDX_6ADF197658FAB2 (client_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE changement_after_import ADD CONSTRAINT FK_B5E7ADC13BDD64EA FOREIGN KEY (liste_pfupdated_id) REFERENCES liste_pfupdated (id)');
        $this->addSql('ALTER TABLE client_updated_interval_change_pf ADD CONSTRAINT FK_17523A7B658FAB2 FOREIGN KEY (client_updated_id) REFERENCES client_updated (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_updated_interval_change_pf ADD CONSTRAINT FK_17523A7BECBEA75C FOREIGN KEY (interval_change_pf_id) REFERENCES interval_change_pf (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE liste_pfupdated ADD CONSTRAINT FK_6ADF197658FAB2 FOREIGN KEY (client_updated_id) REFERENCES client_updated (id)');
        $this->addSql('DROP TABLE changement_pf');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE client_updated_interval_change_pf DROP FOREIGN KEY FK_17523A7B658FAB2');
        $this->addSql('ALTER TABLE liste_pfupdated DROP FOREIGN KEY FK_6ADF197658FAB2');
        $this->addSql('ALTER TABLE client_updated_interval_change_pf DROP FOREIGN KEY FK_17523A7BECBEA75C');
        $this->addSql('ALTER TABLE changement_after_import DROP FOREIGN KEY FK_B5E7ADC13BDD64EA');
        $this->addSql('CREATE TABLE changement_pf (id INT AUTO_INCREMENT NOT NULL, client VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, id_pro VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_avant DATETIME DEFAULT NULL, date_maj DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE changement_after_import');
        $this->addSql('DROP TABLE client_updated');
        $this->addSql('DROP TABLE client_updated_interval_change_pf');
        $this->addSql('DROP TABLE interval_change_pf');
        $this->addSql('DROP TABLE liste_pfupdated');
    }
}
