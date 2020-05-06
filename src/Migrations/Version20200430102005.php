<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200430102005 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE points_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE points_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE points (id INT NOT NULL, user_id INT NOT NULL, test_id INT DEFAULT NULL, type_id INT NOT NULL, points INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_27BA8E29A76ED395 ON points (user_id)');
        $this->addSql('CREATE INDEX IDX_27BA8E291E5D0459 ON points (test_id)');
        $this->addSql('CREATE INDEX IDX_27BA8E29C54C8C93 ON points (type_id)');
        $this->addSql('CREATE TABLE points_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A062EC5E237E06 ON points_type (name)');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E29A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E291E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E29C54C8C93 FOREIGN KEY (type_id) REFERENCES points_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT FK_27BA8E29C54C8C93');
        $this->addSql('DROP SEQUENCE points_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE points_type_id_seq CASCADE');
        $this->addSql('DROP TABLE points');
        $this->addSql('DROP TABLE points_type');
    }
}
