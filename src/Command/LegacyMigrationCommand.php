<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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

namespace qpost\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use mysqli;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function strtolower;

class LegacyMigrationCommand extends Command {
	protected static $defaultName = "qpost:legacy-migration";

	private $logger;
	private $entityManager;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
	}

	protected function configure() {
		$this
			->setDescription("Moves all the data from the legacy database structure to the new doctrine structure.")
			->setHelp("Moves all the data from the legacy database structure to the new doctrine structure.")
			->addArgument("type", InputArgument::REQUIRED, "What type of data to migrate.");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$type = strtolower($input->getArgument("type"));

		switch ($type) {
			case "users":
				$db = $this->db();

				break;
			case "feed":
				$db = $this->db();

				break;
			case "follows":
				$db = $this->db();

				break;
			case "followrequests":
				$db = $this->db();

				break;
			case "media":
				$db = $this->db();

				break;
			case "notifications":
				$db = $this->db();

				break;
			case "suspensions":
				$db = $this->db();

				break;
			default:
				throw new Exception("Invalid type.");
		}
	}

	private function db(): mysqli {
		$host = $_ENV["LEGACYDB_HOST"];
		$user = $_ENV["LEGACYDB_USER"];
		$password = $_ENV["LEGACYDB_PASSWORD"];
		$database = $_ENV["LEGACYDB_DATABASE"];

		return new mysqli($host, $user, $password, $database);
	}
}