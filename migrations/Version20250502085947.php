<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250502085947 extends AbstractMigration
{

    public function up(Schema $schema): void
    {

        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER last_login TYPE DATE
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".secret_key IS '(DC2Type:secret_key)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".last_login IS '(DC2Type:date_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {

        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER secret_key DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER last_login TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".secret_key IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".last_login IS NULL
        SQL);
    }
}
