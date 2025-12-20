<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231217000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cinema database schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE rooms (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) UNIQUE NOT NULL,
            `rows` INT NOT NULL,
            seats_per_row INT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE seats (
            id INT AUTO_INCREMENT NOT NULL,
            room_id INT NOT NULL,
            `row_number` INT NOT NULL,
            seat_number INT NOT NULL,
            is_reserved TINYINT(1) NOT NULL,
            INDEX IDX_F5CBDF0354177093 (room_id),
            UNIQUE INDEX seat_unique (room_id, `row_number`, seat_number),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE reservations (
            id INT AUTO_INCREMENT NOT NULL,
            seat_id INT NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_4DA239C1D1E27F8 (seat_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE seats ADD CONSTRAINT FK_F5CBDF0354177093 FOREIGN KEY (room_id) REFERENCES rooms (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239C1D1E27F8 FOREIGN KEY (seat_id) REFERENCES seats (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239C1D1E27F8');
        $this->addSql('ALTER TABLE seats DROP FOREIGN KEY FK_F5CBDF0354177093');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('DROP TABLE seats');
        $this->addSql('DROP TABLE rooms');
        $this->addSql('DROP TABLE users');
    }
}
