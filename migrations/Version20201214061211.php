<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201214061211 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE donnee_mensuelle (id INT AUTO_INCREMENT NOT NULL, stock DOUBLE PRECISION DEFAULT NULL, cost_restaurant_value DOUBLE PRECISION DEFAULT NULL, cost_restaurant_pourcent DOUBLE PRECISION DEFAULT NULL, cost_electricite_value DOUBLE PRECISION DEFAULT NULL, cost_electricite_pourcent DOUBLE PRECISION NOT NULL, cost_eau_value DOUBLE PRECISION DEFAULT NULL, cost_eau_pourcent DOUBLE PRECISION NOT NULL, cost_gasoil_value DOUBLE PRECISION DEFAULT NULL, cost_gasoil_pourcent DOUBLE PRECISION DEFAULT NULL, salaire_brute_value DOUBLE PRECISION DEFAULT NULL, salaire_brute_pourcent DOUBLE PRECISION DEFAULT NULL, sqn_interne DOUBLE PRECISION DEFAULT NULL, sqn_booking DOUBLE PRECISION DEFAULT NULL, sqn_tripadvisor DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE donnee_mensuelle');
    }
}
