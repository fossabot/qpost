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

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\Hashtag;
use qpost\Entity\TrendingHashtagData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function strtotime;

class CompileTrendsCommand extends Command {
	protected static $defaultName = "qpost:compile-trends";

	private $logger;
	private $entityManager;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		/**
		 * @var string $trendingTags
		 */
		$trendingTags = [];

		$weekStart = date("Y-m-d", strtotime("monday this week"));
		$weekEnd = date("Y-m-d", strtotime("sunday this week"));

		$query = $this->entityManager->getRepository(Hashtag::class)->createQueryBuilder("t")
			->addSelect("count(t.id) as amount")
			->innerJoin("t.feedEntries", "f")
			->where("t.blacklisted = false")
			->andWhere("f.time >= :start")
			->andWhere("f.time <= :end")
			->setParameter("start", $weekStart, Type::STRING)
			->setParameter("end", $weekEnd, Type::STRING)
			->groupBy("t.id")
			->orderBy("amount", "DESC")
			->setMaxResults(10)
			->getQuery();

		foreach ($query->getResult() as $result) {
			/**
			 * @var Hashtag $hashtag
			 */
			$hashtag = $result[0];

			/**
			 * @var int $amount
			 */
			$amount = $result["amount"];

			$output->writeln("#" . $hashtag->getId() . " - " . $amount);

			$trendingData = $hashtag->getTrendingData();
			if (!$trendingData) {
				$trendingData = (new TrendingHashtagData())
					->setTime(new DateTime("now"))
					->setHashtag($hashtag);
			}

			$trendingData->setPostsThisWeek($amount);

			$this->entityManager->persist($trendingData);
			$this->entityManager->persist($hashtag);

			$trendingTags[] = $hashtag->getId();
		}

		// remove former trending tags
		foreach ($this->entityManager->getRepository(Hashtag::class)->createQueryBuilder("h")
					 ->innerJoin("h.trendingData", "t")
					 ->where("h.trendingData IS NOT NULL")
					 ->andWhere("h.id NOT IN (:tags)")
					 ->setParameter("tags", $trendingTags)
					 ->getQuery()
					 ->getResult() as $hashtag) {
			$this->entityManager->remove($hashtag->getTrendingData());
		}

		$this->entityManager->flush();
	}
}