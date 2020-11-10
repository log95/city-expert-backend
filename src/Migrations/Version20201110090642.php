<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201110090642 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE chat_message DROP CONSTRAINT fk_fab3fc161a9a7125');
        $this->addSql('DROP SEQUENCE chat_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE chat_message_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE test_comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE test_comment (id INT NOT NULL, test_id INT NOT NULL, created_by_id INT NOT NULL, message VARCHAR(1000) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_483586A41E5D0459 ON test_comment (test_id)');
        $this->addSql('CREATE INDEX IDX_483586A4B03A8386 ON test_comment (created_by_id)');
        $this->addSql('ALTER TABLE test_comment ADD CONSTRAINT FK_483586A41E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_comment ADD CONSTRAINT FK_483586A4B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE chat_message');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE test_comment_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE chat_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE chat_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chat (id INT NOT NULL, test_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_659df2aa1e5d0459 ON chat (test_id)');
        $this->addSql('CREATE TABLE chat_message (id INT NOT NULL, chat_id INT NOT NULL, created_by_id INT NOT NULL, message VARCHAR(1000) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_fab3fc161a9a7125 ON chat_message (chat_id)');
        $this->addSql('CREATE INDEX idx_fab3fc16b03a8386 ON chat_message (created_by_id)');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT fk_659df2aa1e5d0459 FOREIGN KEY (test_id) REFERENCES test (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT fk_fab3fc161a9a7125 FOREIGN KEY (chat_id) REFERENCES chat (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT fk_fab3fc16b03a8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE test_comment');
    }
}
