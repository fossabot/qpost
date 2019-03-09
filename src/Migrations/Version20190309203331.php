<?php
/**
 * Copyright (C) 2019 Gigadrive Group - All rights reserved.
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
final class Version20190309203331 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, profile_data_id INT NOT NULL, privacy_settings_id INT NOT NULL, username VARCHAR(16) NOT NULL, password VARCHAR(60) DEFAULT NULL, email VARCHAR(50) NOT NULL, register_date DATETIME NOT NULL, email_activated TINYINT(1) NOT NULL, email_activation_token VARCHAR(7) DEFAULT NULL, verified TINYINT(1) NOT NULL, last_username_change DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649E44B7FA0 (profile_data_id), UNIQUE INDEX UNIQ_8D93D649BA3A3030 (privacy_settings_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE user_privacy_settings (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE user_profile_data (id INT AUTO_INCREMENT NOT NULL, featured_box_id INT NOT NULL, display_name VARCHAR(25) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, birthday DATE DEFAULT NULL, UNIQUE INDEX UNIQ_9BF700F5C894489E (featured_box_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE user_profile_featured_box (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE user_profile_featured_box_user (user_profile_featured_box_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_219487741C034FE5 (user_profile_featured_box_id), INDEX IDX_21948774A76ED395 (user_id), PRIMARY KEY(user_profile_featured_box_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E44B7FA0 FOREIGN KEY (profile_data_id) REFERENCES user_profile_data (id)');
		$this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649BA3A3030 FOREIGN KEY (privacy_settings_id) REFERENCES user_privacy_settings (id)');
		$this->addSql('ALTER TABLE user_profile_data ADD CONSTRAINT FK_9BF700F5C894489E FOREIGN KEY (featured_box_id) REFERENCES user_profile_featured_box (id)');
		$this->addSql('ALTER TABLE user_profile_featured_box_user ADD CONSTRAINT FK_219487741C034FE5 FOREIGN KEY (user_profile_featured_box_id) REFERENCES user_profile_featured_box (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE user_profile_featured_box_user ADD CONSTRAINT FK_21948774A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE user_profile_featured_box_user DROP FOREIGN KEY FK_21948774A76ED395');
		$this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649BA3A3030');
		$this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E44B7FA0');
		$this->addSql('ALTER TABLE user_profile_data DROP FOREIGN KEY FK_9BF700F5C894489E');
		$this->addSql('ALTER TABLE user_profile_featured_box_user DROP FOREIGN KEY FK_219487741C034FE5');
		$this->addSql('DROP TABLE user');
		$this->addSql('DROP TABLE user_privacy_settings');
		$this->addSql('DROP TABLE user_profile_data');
		$this->addSql('DROP TABLE user_profile_featured_box');
		$this->addSql('DROP TABLE user_profile_featured_box_user');
	}
}
