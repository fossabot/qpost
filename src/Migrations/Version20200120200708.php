<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200120200708 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE user_appearance_settings ADD night_mode TINYINT(1) NOT NULL, ADD autoplay_gifs TINYINT(1) NOT NULL, ADD show_trends TINYINT(1) NOT NULL, ADD show_suggested_users TINYINT(1) NOT NULL, ADD show_birthdays TINYINT(1) NOT NULL, ADD show_mature_warning TINYINT(1) NOT NULL');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE user_appearance_settings DROP night_mode, DROP autoplay_gifs, DROP show_trends, DROP show_suggested_users, DROP show_birthdays, DROP show_mature_warning');
	}
}
