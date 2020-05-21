<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200520072612 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE test_action DROP CONSTRAINT fk_d0158bbc1fee0472');
        $this->addSql('DROP INDEX idx_d0158bbc1fee0472');
        $this->addSql('ALTER TABLE test_action RENAME COLUMN action_type_id TO type_id');
        $this->addSql('ALTER TABLE test_action ADD CONSTRAINT FK_D0158BBCC54C8C93 FOREIGN KEY (type_id) REFERENCES test_action_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D0158BBCC54C8C93 ON test_action (type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE test_action DROP CONSTRAINT FK_D0158BBCC54C8C93');
        $this->addSql('DROP INDEX IDX_D0158BBCC54C8C93');
        $this->addSql('ALTER TABLE test_action RENAME COLUMN type_id TO action_type_id');
        $this->addSql('ALTER TABLE test_action ADD CONSTRAINT fk_d0158bbc1fee0472 FOREIGN KEY (action_type_id) REFERENCES test_action_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d0158bbc1fee0472 ON test_action (action_type_id)');
    }
}
