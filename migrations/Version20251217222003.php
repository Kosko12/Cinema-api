<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217222003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CASCADE delete to reservations.seat_id foreign key to allow room/seat deletion';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239C1D1E27F8');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239C1DAFE35 FOREIGN KEY (seat_id) REFERENCES seats (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservations RENAME INDEX idx_4da239c1d1e27f8 TO IDX_4DA239C1DAFE35');
        $this->addSql('ALTER TABLE seats RENAME INDEX idx_f5cbdf0354177093 TO IDX_BFE2575054177093');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239C1DAFE35');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239C1D1E27F8 FOREIGN KEY (seat_id) REFERENCES seats (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reservations RENAME INDEX idx_4da239c1dafe35 TO IDX_4DA239C1D1E27F8');
        $this->addSql('ALTER TABLE seats RENAME INDEX idx_bfe2575054177093 TO IDX_F5CBDF0354177093');
    }
}
