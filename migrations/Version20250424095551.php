<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250424095551 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_identifier_email
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD picture_path VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP picture_path
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_identifier_email ON "user" (email)
        SQL);
    }
}
