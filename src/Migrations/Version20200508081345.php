<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200508081345 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE points DROP CONSTRAINT FK_27BA8E29A76ED395');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT FK_27BA8E291E5D0459');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT FK_27BA8E29C54C8C93');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT FK_27BA8E29519161AB');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E29A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E291E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E29C54C8C93 FOREIGN KEY (type_id) REFERENCES points_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E29519161AB FOREIGN KEY (hint_id) REFERENCES test_hint (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE city DROP CONSTRAINT FK_2D5B0234F92F3E70');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_interest DROP CONSTRAINT FK_F95D1131A76ED395');
        $this->addSql('ALTER TABLE test_interest DROP CONSTRAINT FK_F95D11311E5D0459');
        $this->addSql('ALTER TABLE test_interest ADD CONSTRAINT FK_F95D1131A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_interest ADD CONSTRAINT FK_F95D11311E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_hint DROP CONSTRAINT FK_31BF299C1E5D0459');
        $this->addSql('ALTER TABLE test_hint ADD CONSTRAINT FK_31BF299C1E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0C8BAC62AF');
        $this->addSql('ALTER TABLE test ADD moderator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE test ADD created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE test ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0CD0AFA354 FOREIGN KEY (moderator_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0CB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0C8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D87F7E0CD0AFA354 ON test (moderator_id)');
        $this->addSql('CREATE INDEX IDX_D87F7E0CB03A8386 ON test (created_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE test_hint DROP CONSTRAINT fk_31bf299c1e5d0459');
        $this->addSql('ALTER TABLE test_hint ADD CONSTRAINT fk_31bf299c1e5d0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE city DROP CONSTRAINT fk_2d5b0234f92f3e70');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT fk_2d5b0234f92f3e70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0CD0AFA354');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0CB03A8386');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT fk_d87f7e0c8bac62af');
        $this->addSql('DROP INDEX IDX_D87F7E0CD0AFA354');
        $this->addSql('DROP INDEX IDX_D87F7E0CB03A8386');
        $this->addSql('ALTER TABLE test DROP moderator_id');
        $this->addSql('ALTER TABLE test DROP created_by_id');
        $this->addSql('ALTER TABLE test DROP created_at');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT fk_d87f7e0c8bac62af FOREIGN KEY (city_id) REFERENCES city (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_interest DROP CONSTRAINT fk_f95d1131a76ed395');
        $this->addSql('ALTER TABLE test_interest DROP CONSTRAINT fk_f95d11311e5d0459');
        $this->addSql('ALTER TABLE test_interest ADD CONSTRAINT fk_f95d1131a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_interest ADD CONSTRAINT fk_f95d11311e5d0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT fk_27ba8e29a76ed395');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT fk_27ba8e291e5d0459');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT fk_27ba8e29c54c8c93');
        $this->addSql('ALTER TABLE points DROP CONSTRAINT fk_27ba8e29519161ab');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT fk_27ba8e29a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT fk_27ba8e291e5d0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT fk_27ba8e29c54c8c93 FOREIGN KEY (type_id) REFERENCES points_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT fk_27ba8e29519161ab FOREIGN KEY (hint_id) REFERENCES test_hint (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
