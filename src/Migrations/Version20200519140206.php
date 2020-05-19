<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200519140206 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE test_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE test_action_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE test_action (id INT NOT NULL, user_id INT NOT NULL, test_id INT NOT NULL, hint_id INT DEFAULT NULL, action_type_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D0158BBCA76ED395 ON test_action (user_id)');
        $this->addSql('CREATE INDEX IDX_D0158BBC1E5D0459 ON test_action (test_id)');
        $this->addSql('CREATE INDEX IDX_D0158BBC519161AB ON test_action (hint_id)');
        $this->addSql('CREATE INDEX IDX_D0158BBC1FEE0472 ON test_action (action_type_id)');
        $this->addSql('CREATE TABLE test_action_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A54157995E237E06 ON test_action_type (name)');
        $this->addSql('ALTER TABLE test_action ADD CONSTRAINT FK_D0158BBCA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_action ADD CONSTRAINT FK_D0158BBC1E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_action ADD CONSTRAINT FK_D0158BBC519161AB FOREIGN KEY (hint_id) REFERENCES test_hint (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_action ADD CONSTRAINT FK_D0158BBC1FEE0472 FOREIGN KEY (action_type_id) REFERENCES test_action_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE test_action DROP CONSTRAINT FK_D0158BBC1FEE0472');
        $this->addSql('DROP SEQUENCE test_action_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE test_action_type_id_seq CASCADE');
        $this->addSql('DROP TABLE test_action');
        $this->addSql('DROP TABLE test_action_type');
    }
}
