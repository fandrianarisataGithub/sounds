<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201215065311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE donnee_mensuelle ADD hotel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE donnee_mensuelle ADD CONSTRAINT FK_96CC97863243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id)');
        $this->addSql('CREATE INDEX IDX_96CC97863243BB18 ON donnee_mensuelle (hotel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE donnee_mensuelle DROP FOREIGN KEY FK_96CC97863243BB18');
        $this->addSql('DROP INDEX IDX_96CC97863243BB18 ON donnee_mensuelle');
        $this->addSql('ALTER TABLE donnee_mensuelle DROP hotel_id');
    }
}
