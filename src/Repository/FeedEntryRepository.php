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

namespace qpost\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Type;
use qpost\Constants\FeedEntryType;
use qpost\Constants\MiscConstants;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use function in_array;
use function is_null;

/**
 * @method FeedEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedEntry[]    findAll()
 * @method FeedEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedEntryRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, FeedEntry::class);
	}

	public function getEntryById(int $id): ?FeedEntry {
		return $this->findOneBy([
			"id" => $id
		]);
	}

	public function getUserPostCount(User $user): int {
		return $this->createQueryBuilder("f")
			->select("count(f.id)")
			->where("f.user = :user")
			->setParameter("user", $user)
			->andWhere("f.type = :type")
			->setParameter("type", FeedEntryType::POST, Type::STRING)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getSingleScalarResult();
	}

	public function getUserReplyCount(User $user): int {
		return $this->createQueryBuilder("f")
			->select("count(f.id)")
			->where("f.user = :user")
			->setParameter("user", $user)
			->andWhere("f.type = :type")
			->setParameter("type", FeedEntryType::REPLY, Type::STRING)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getSingleScalarResult();
	}

	public function getUserShareCount(User $user): int {
		return $this->createQueryBuilder("f")
			->select("count(f.id)")
			->where("f.user = :user")
			->setParameter("user", $user)
			->andWhere("f.type = :type")
			->setParameter("type", FeedEntryType::SHARE, Type::STRING)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getSingleScalarResult();
	}

	public function getUserFollowingPostCount(User $user): int {
		return $this->createQueryBuilder("f")
			->select("count(f.id)")
			->where("f.user = :user")
			->setParameter("user", $user)
			->andWhere("f.type = :type")
			->setParameter("type", FeedEntryType::NEW_FOLLOWING, Type::STRING)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getSingleScalarResult();
	}

	public function getUserTotalPostCount(User $user): int {
		return $this->createQueryBuilder("f")
			->select("count(f.id)")
			->where("f.user = :user")
			->setParameter("user", $user)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getSingleScalarResult();
	}

	public function getFeed(?User $from, ?User $target = null, int $min = null, int $max = null, string $type = "posts"): array {
		$limit = 15;

		$rsm = $this->createResultSetMappingBuilder("f");
		$rsm->addScalarResult("favoriteCount", "favoriteCount", "integer");
		$rsm->addScalarResult("replyCount", "replyCount", "integer");
		$rsm->addScalarResult("shareCount", "shareCount", "integer");
		$rsm->addScalarResult("favorited", "favorited", "boolean");
		$rsm->addScalarResult("shared", "shared", "boolean");

		$ownerWhere = is_null($target) ? "(EXISTS (SELECT 1 FROM follower AS ff WHERE ff.sender_id = ? AND ff.receiver_id = u.id) OR u.id = ?)" : "f.user_id = ?";
		$typeWhere = $type === "posts" ? "((f.type = 'POST' AND f.parent_id IS NULL AND (f.text IS NULL OR f.text NOT LIKE '@%')) OR (f.type = 'SHARE' AND f.parent_id IS NOT NULL))" : "(f.type = 'REPLY' OR (f.type = 'POST' AND f.text LIKE '@%'))";

		$parameters[] = is_null($from) ? 0 : $from->getId();
		$parameters[] = is_null($from) ? 0 : $from->getId();

		$parameters[] = is_null($target) ? $from->getId() : $target->getId();

		if (is_null($target)) {
			$parameters[] = $from->getId();
		}

		if (!is_null($min)) {
			$parameters[] = $min;
		}

		if (!is_null($max)) {
			$parameters[] = $max;
		}

		$parameters[] = (int)($limit + ($limit / 2));

		$query = $this->_em->createNativeQuery("SELECT " . $rsm->generateSelectClause([
				"f" => "f",
			]) . ",COALESCE(favoriteCount, 0) AS favoriteCount,
       COALESCE(replyCount, 0)    AS replyCount,
       COALESCE(shareCount, 0)    AS shareCount,
       COALESCE(favorited, 0)     AS favorited,
       COALESCE(shared, 0)        AS shared
FROM feed_entry AS f
         LEFT JOIN (
    SELECT feed_entry_id, COUNT(favorite.id) AS favoriteCount, SUM(favorite.user_id = ?) AS favorited
    FROM favorite
    GROUP BY feed_entry_id
) favorite_counts ON favorite_counts.feed_entry_id = f.id
         LEFT JOIN (
    SELECT parent_id, SUM(feed_entry.type = 'REPLY') AS replyCount, SUM(feed_entry.type = 'SHARE') AS shareCount, SUM(feed_entry.user_id = ? AND feed_entry.type = 'SHARE') AS shared
    FROM feed_entry
    WHERE feed_entry.type = 'REPLY'
       OR feed_entry.type = 'SHARE'
    GROUP BY parent_id
) children ON children.parent_id = f.id
		INNER JOIN user AS u ON f.user_id = u.id
WHERE " . $ownerWhere . (is_null($target) ? " AND u.privacy_level != 'CLOSED'" : "") . "
" . (!is_null($min) ? " AND f.id > ?" : "") . "
" . (!is_null($max) ? " AND f.id < ?" : "") . "
AND " . $typeWhere . "
GROUP BY f.id
ORDER BY f.time DESC
LIMIT ?", $rsm);

		foreach ($parameters as $index => $parameter) {
			$query = $query->setParameter($index, $parameter);
		}

		$results = $query->getResult();

		$entries = [];
		$ids = [];

		foreach ($results as $result) {
			if (count($entries) >= $limit) {
				break;
			}

			/**
			 * @var FeedEntry $feedEntry
			 */
			$feedEntry = $result[0];

			$feedEntry->setReplyCount($result["replyCount"])
				->setShareCount($result["shareCount"])
				->setFavoriteCount($result["favoriteCount"])
				->setFavorited($result["favorited"])
				->setShared($result["shared"]);

			$id = $feedEntry->getId();

			if ($feedEntry->getType() === FeedEntryType::SHARE || $feedEntry->getType() === FeedEntryType::REPLY) {
				$parent = $feedEntry->getParent();

				if (!is_null($parent)) {
					$id = $feedEntry->getParent()->getId();
				}
			}

			if (in_array($id, $ids)) {
				continue;
			}

			$entries[] = $feedEntry;
			$ids[] = $id;
		}

		return $entries;
	}

	/**
	 * @param int $randomizer
	 * @param int $limit
	 * @return int[]
	 */
	public function getSitemapFeedEntries(int $randomizer, int $limit): array {
		$rsm = $this->createResultSetMappingBuilder("f");
		$rsm->addScalarResult("id", "id");

		$query = $this->_em->createNativeQuery("SELECT f.id AS id
FROM feed_entry AS f
         INNER JOIN user AS u ON u.id = f.user_id
		 LEFT JOIN suspension s on u.id = s.target_id
WHERE (f.type = 'POST' OR f.type = 'REPLY')
  AND u.privacy_level = 'PUBLIC'
  AND s.id IS NULL
ORDER BY f.time
LIMIT ? OFFSET ?", $rsm);

		$query->setParameter(0, $limit);
		$query->setParameter(1, ($randomizer - 1) * $limit);

		$ids = [];

		foreach ($query->getResult() as $resultSet) {
			$ids[] = $resultSet["id"];
		}

		return $ids;
	}
}
