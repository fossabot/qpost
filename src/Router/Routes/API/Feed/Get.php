<?php

namespace qpost\Router\API\Feed;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use qpost\Account\PrivacyLevel;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_method_check;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;
use function qpost\Router\create_route_get;

function home_feed_query(User $currentUser): QueryBuilder {
	return EntityManager::instance()->getRepository(FeedEntry::class)->createQueryBuilder("f")
		->innerJoin("f.user", "u")
		->where("u.privacyLevel != :closed")
		->setParameter("closed", PrivacyLevel::CLOSED, Type::STRING)
		->andWhere("f.post is null")
		->andWhere("f.type = :post or f.type = :share")
		->setParameter("post", FeedEntryType::POST, Type::STRING)
		->setParameter("share", FeedEntryType::SHARE, Type::STRING)
		->andWhere("exists (select 1 from qpost\Account\Follower ff where ff.to = :to) or f.user = :to")
		->setParameter("to", $currentUser)
		->orderBy("f.time", "DESC")
		->setMaxResults(30);
}

function profile_feed_query(User $user): QueryBuilder {
	return EntityManager::instance()->getRepository(FeedEntry::class)->createQueryBuilder("f")
		->where("(f.post is null and f.type = :post) or (f.post is not null and f.type = :share) or (f.type = :newFollowing)")
		->setParameter("post", FeedEntryType::POST, Type::STRING)
		->setParameter("share", FeedEntryType::SHARE, Type::STRING)
		->setParameter("newFollowing", FeedEntryType::NEW_FOLLOWING, Type::STRING)
		->andWhere("f.user = :user")
		->setParameter("user", $user)
		->orderBy("f.time", "DESC")
		->setMaxResults(30);
}

create_route_get("/api/feed", function () {
	if (api_method_check($this, "GET", false)) {
		$token = api_get_token();
		$currentUser = !is_null($token) ? $token->getUser() : null;
		$requestData = api_request_data($this);

		if (isset($requestData["max"]) && !isset($requestData["user"])) {
			// Load older posts on home feed
			if (!is_null($currentUser)) {
				if (is_numeric($requestData["max"])) {
					$results = [];

					/**
					 * @var FeedEntry[] $feedEntries
					 */
					$feedEntries = home_feed_query($currentUser)
						->andWhere("f.id < :id")
						->setParameter("id", $requestData["max"], Type::INTEGER)
						->getQuery()
						->getResult();

					foreach ($feedEntries as $feedEntry) {
						array_push($results, api_prepare_object($feedEntry));
					}

					return json_encode(["results" => $results]);
				} else {
					return json_encode(["error" => "'max' has to be an integer."]);
				}
			} else {
				// Return error if there is no user as we need it to get the home feed entries
				$this->response->status = "401";
				return json_encode(["error" => "Invalid token"]);
			}
		} else if (!isset($requestData["max"]) && !isset($requestData["user"])) {
			// Load first posts on home feed
			if (!is_null($currentUser)) {
				$results = [];

				/**
				 * @var FeedEntry[] $feedEntries
				 */
				$feedEntries = home_feed_query($currentUser)
					->getQuery()
					->getResult();

				foreach ($feedEntries as $feedEntry) {
					array_push($results, api_prepare_object($feedEntry));
				}

				return json_encode(["results" => $results]);
			} else {
				// Return error if there is no user as we need it to get the home feed entries
				$this->response->status = "401";
				return json_encode(["error" => "Invalid token"]);
			}
		} else if (isset($requestData["max"]) && isset($requestData["user"])) {
			// Load older posts on profile page
			$user = User::getUser($requestData["user"]);

			if (!is_null($user) && $user->mayView($currentUser)) {
				if (is_numeric($requestData["max"])) {
					$results = [];

					/**
					 * @var FeedEntry[] $feedEntries
					 */
					$feedEntries = profile_feed_query($user)
						->andWhere("f.id < :id")
						->setParameter("id", $requestData["max"], Type::INTEGER)
						->getQuery()
						->getResult();

					foreach ($feedEntries as $feedEntry) {
						array_push($results, api_prepare_object($feedEntry));
					}

					return json_encode(["results" => $results]);
				} else {
					return json_encode(["error" => "'max' has to be an integer."]);
				}
			} else {
				return json_encode(["error" => "The requested user could not be found."]);
			}
		} else if (!isset($requestData["max"]) && isset($requestData["user"])) {
			// Load first posts on profile page
			$user = User::getUser($requestData["user"]);

			if (!is_null($user) && $user->mayView($currentUser)) {
				$results = [];

				/**
				 * @var FeedEntry[] $feedEntries
				 */
				$feedEntries = profile_feed_query($user)
					->getQuery()
					->getResult();

				foreach ($feedEntries as $feedEntry) {
					array_push($results, api_prepare_object($feedEntry));
				}

				return json_encode(["results" => $results]);
			} else {
				return json_encode(["error" => "The requested user could not be found."]);
			}
		} else {
			$this->response->status = "400";
			return json_encode(["error" => "Bad request"]);
		}
	} else {
		return "";
	}
});