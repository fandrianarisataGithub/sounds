<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200901100352 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE donnee_du_jour (id INT AUTO_INCREMENT NOT NULL, heb_to INT NOT NULL, heb_ca DOUBLE PRECISION NOT NULL, res_n_couvert INT NOT NULL, res_ca INT NOT NULL, res_p_dej INT NOT NULL, res_dej INT NOT NULL, res_dinner INT NOT NULL, spa_ca DOUBLE PRECISION NOT NULL, spa_n_abonne INT NOT NULL, spa_c_unique INT NOT NULL, crj_direction LONGTEXT DEFAULT NULL, crj_service_rh LONGTEXT DEFAULT NULL, crj_commercial LONGTEXT DEFAULT NULL, crj_comptable LONGTEXT DEFAULT NULL, crj_reception LONGTEXT DEFAULT NULL, crj_restaurant LONGTEXT DEFAULT NULL, crj_spa LONGTEXT DEFAULT NULL, crj_s_technique LONGTEXT DEFAULT NULL, crj_litiges LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE donnee_du_jour');
    }
}
