<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210119073026 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE entreprise_tw (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact_entreprise_tw ADD entreprise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_entreprise_tw ADD CONSTRAINT FK_FEE4C1AFA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise_tw (id)');
        $this->addSql('CREATE INDEX IDX_FEE4C1AFA4AEAFEA ON contact_entreprise_tw (entreprise_id)');
        $this->addSql('ALTER TABLE remarque_entreprise_tw ADD entreprise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE remarque_entreprise_tw ADD CONSTRAINT FK_FCD154A0A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise_tw (id)');
        $this->addSql('CREATE INDEX IDX_FCD154A0A4AEAFEA ON remarque_entreprise_tw (entreprise_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contact_entreprise_tw DROP FOREIGN KEY FK_FEE4C1AFA4AEAFEA');
        $this->addSql('ALTER TABLE remarque_entreprise_tw DROP FOREIGN KEY FK_FCD154A0A4AEAFEA');
        $this->addSql('DROP TABLE entreprise_tw');
        $this->addSql('DROP INDEX IDX_FEE4C1AFA4AEAFEA ON contact_entreprise_tw');
        $this->addSql('ALTER TABLE contact_entreprise_tw DROP entreprise_id');
        $this->addSql('DROP INDEX IDX_FCD154A0A4AEAFEA ON remarque_entreprise_tw');
        $this->addSql('ALTER TABLE remarque_entreprise_tw DROP entreprise_id');
    }
}
