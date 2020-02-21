<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200221183847 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bike (id INT AUTO_INCREMENT NOT NULL, responsible_id INT DEFAULT NULL, license_number VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, owner_full_name VARCHAR(255) NOT NULL, stealing_date DATETIME NOT NULL, stealing_description LONGTEXT NOT NULL, is_resolved TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_4CBC3780EC7E7152 (license_number), INDEX IDX_4CBC3780602AD315 (responsible_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE police (id INT AUTO_INCREMENT NOT NULL, personal_code VARCHAR(30) NOT NULL, full_name VARCHAR(255) NOT NULL, is_available TINYINT(1) DEFAULT \'1\' NOT NULL, UNIQUE INDEX UNIQ_E47C5959461F37A5 (personal_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bike ADD CONSTRAINT FK_4CBC3780602AD315 FOREIGN KEY (responsible_id) REFERENCES police (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bike DROP FOREIGN KEY FK_4CBC3780602AD315');
        $this->addSql('DROP TABLE bike');
        $this->addSql('DROP TABLE police');
    }
}
