<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200630145153 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
START TRANSACTION;
SET time_zone = \"+00:00\";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `battleship`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `game`
--

CREATE TABLE `game` (
  `id` int(7) NOT NULL,
  `shooting_dir` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ships`
--

CREATE TABLE `ships` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `ship_type` int(1) NOT NULL,
  `coord` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `shots`
--

CREATE TABLE `shots` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player` int(1) NOT NULL,
  `attack` varchar(3) NOT NULL,
  `response` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ships`
--
ALTER TABLE `ships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `game_id_coord` (`game_id`,`coord`) USING BTREE;

--
-- Indizes für die Tabelle `shots`
--
ALTER TABLE `shots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `game`
--
ALTER TABLE `game`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ships`
--
ALTER TABLE `ships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `shots`
--
ALTER TABLE `shots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `ships`
--
ALTER TABLE `ships`
  ADD CONSTRAINT `Ship-Game-FK` FOREIGN KEY (`game_id`) REFERENCES `game` (`id`);

--
-- Constraints der Tabelle `shots`
--
ALTER TABLE `shots`
  ADD CONSTRAINT `game_id` FOREIGN KEY (`game_id`) REFERENCES `game` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;"
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
