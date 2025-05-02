<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250502100404 extends AbstractMigration
{


    public function up(Schema $schema): void
    {

        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key DROP NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {

        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key SET NOT NULL
        SQL);
    }
}
