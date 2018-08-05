<?php

/**
 * Represents a user
 * 
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class User {
	/**
	 * Gets a user object by the user's ID
	 * 
	 * @access public
	 * @param int $id
	 * @return User
	 */
	public static function getUserById($id){
		$n = "user_id_" . $id;

		if(CacheHandler::existsInCache($n)){
			return CacheHandler::getFromCache($n);
		} else {
			$user = new User($id);
			$user->reload();

			if($user->exists == true){
				return $user;
			} else {
				return null;
			}
		}
	}

	/**
	 * Gets a user object by the user's username
	 * 
	 * @access public
	 * @param string $username
	 * @return User
	 */
	public static function getUserByUsername($username){
		$n = "user_name_" . strtolower($username);

		if(CacheHandler::existsInCache($n)){
			return CacheHandler::getFromCache($n);
		} else {
			$id = null;

			$mysqli = Database::Instance()->get();
			$stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `username` = ?");
			$stmt->bind_param("s",$username);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					$id = $row["id"];
				}
			}
			$stmt->close();

			if(!is_null($id)){
				$user = new User($id);
				$user->reload();
	
				if($user->exists == true){
					return $user;
				} else {
					return null;
				}
			} else {
				return null;
			}
		}
	}

	/**
	 * Returns whether a user object is cached by ID
	 * 
	 * @access public
	 * @param int $id
	 * @return bool
	 */
	public static function isCached($id){
		return \CacheHandler::existsInCache("user_id_" . $id);
	}

	public static function registerUser($id,$username,$avatar,$email,$token,$registerDate){
		$mysqli = Database::Instance()->get();
		$user = self::getUserById($id);

		if($user == null){
			$stmt = $mysqli->prepare("INSERT IGNORE INTO `users` (`id`,`displayName`,`username`,`email`,`avatar`,`token`,`gigadriveJoinDate`) VALUES(?,?,?,?,?,?,?);");
			$stmt->bind_param("issssss",$id,$username,$username,$email,$avatar,$token,$registerDate);
			$stmt->execute();
			$stmt->close();

			self::getUserById($id)->removeFromCache();

			$user = self::getUserById($id);
		} else {
			$stmt = $mysqli->prepare("UPDATE `users` SET `username` = ?, `email` = ?, `avatar` = ?, `token` = ?, `gigadriveJoinDate` = ? WHERE `id` = ?");
			$stmt->bind_param("sssssi",$username,$email,$avatar,$token,$registerDate,$id);
			$stmt->execute();
			$stmt->close();

			$user->username = $username;
			$user->email = $email;
			$user->avatar = $avatar;

			$user->saveToCache();
		}

		return $user;
	}

	/**
	 * Gets a user object by data
	 * 
	 * @access public
	 * @param int $id
	 * @param string $displayName
	 * @param string $username
	 * @param string $email
	 * @param string $avatar
	 * @param string $bio
	 * @param string $time
	 * @return User
	 */
	public static function getUserByData($id,$displayName,$username,$email,$avatar,$bio,$token,$privacyLevel,$featuredBoxTitle,$featuredBoxContent,$lastGigadriveUpdate,$gigadriveJoinDate,$time){
		$user = self::isCached($id) ? self::getUserById($id) : new User($id);

		$user->id = $id;
		$user->displayName = $displayName;
		$user->username = $username;
		$user->email = $email;
		$user->avatar = $avatar;
		$user->bio = $bio;
		$user->token = $token;
		$user->privacyLevel = $privacyLevel;
		$user->featuredBoxTitle = $featuredBoxTitle;
		$user->featuredBoxContent = is_null($featuredBoxContent) ? [] : (is_string($featuredBoxContent) ? json_decode($featuredBoxContent,true) : $featuredBoxContent);
		$user->lastGigadriveUpdate = $lastGigadriveUpdate;
		$user->gigadriveJoinDate = $gigadriveJoinDate;
		$user->time = $time;

		$user->saveToCache();

		return $user;
	}

	/**
	 * @access private
	 * @var int $id
	 */
	private $id;

	/**
	 * @access private
	 * @var string $displayName
	 */
	private $displayName;

	/**
	 * @access private
	 * @var string $username
	 */
	private $username;

	/**
	 * @access private
	 * @var string $email
	 */
	private $email;

	/**
	 * @access private
	 * @var string $avatar
	 */
	private $avatar;

	/**
	 * @access private
	 * @var string $bio
	 */
	private $bio;

	/**
	 * @access private
	 * @var string $token
	 */
	private $token;

	/**
	 * @access private
	 * @var string $privacyLevel
	 */
	private $privacyLevel;

	/**
	 * @access private
	 * @var string $featuredBoxTitle
	 */
	private $featuredBoxTitle;

	/**
	 * @access private
	 * @var int[] $featuredBoxContent
	 */
	private $featuredBoxContent;

	/**
	 * @access private
	 * @var string $lastGigadriveUpdate
	 */
	private $lastGigadriveUpdate;

	/**
	 * @access private
	 * @var string $gigadriveJoinDate
	 */
	private $gigadriveJoinDate;

	/**
	 * @access private
	 * @var string $time
	 */
	private $time;

	/**
	 * @access private
	 * @var bool $exists
	 */
	private $exists;

	/**
	 * @access private
	 * @var int $feedEntries
	 */
	private $feedEntries;

	/**
	 * @access private
	 * @var int $posts
	 */
	private $posts;

	/**
	 * @access private
	 * @var int $followers
	 */
	private $followers;

	/**
	 * @access private
	 * @var int $following
	 */
	private $following;

	/**
	 * @access public
	 * @var array $cachedFollowers
	 */
	public $cachedFollowers = [];

	/**
	 * @access public
	 * @var array $cachedBlocks
	 */
	public $cachedBlocks = [];

	/**
	 * @access private
	 * @var int $unreadMessages
	 */
	private $unreadMessages;

	/**
	 * @access private
	 * @var int $unreadNotifications
	 */
	private $unreadNotifications;

	/**
	 * @access private
	 * @var int $favorites
	 */
	private $favorites;

	/**
	 * @access private
	 * @var array $followingArray
	 */
	private $followingArray;

	/**
	 * @access private
	 * @var int $followRequests
	 */
	private $followRequests;

	/**
	 * Constructor
	 * 
	 * @access private
	 * @param int $id
	 */
	protected function __construct($id){
		$this->id = $id;
	}

	/**
	 * Reloads the user's data
	 * 
	 * @access public
	 */
	public function reload(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id` = ?");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				$this->id = $row["id"];
				$this->displayName = $row["displayName"];
				$this->username = $row["username"];
				$this->email = $row["email"];
				$this->avatar = $row["avatar"];
				$this->bio = $row["bio"];
				$this->token = $row["token"];
				$this->privacyLevel = $row["privacy.level"];
				$this->featuredBoxTitle = $row["featuredBox.title"];
				$this->featuredBoxContent = is_null($row["featuredBox.content"]) ? [] : json_decode($row["featuredBox.content"],true);
				$this->lastGigadriveUpdate = $row["lastGigadriveUpdate"];
				$this->gigadriveJoinDate = $row["gigadriveJoinDate"];
				$this->time = $row["time"];

				$this->exists = true;

				if(!is_null($this->feedEntries))
					$this->reloadFeedEntriesCount();

				if(!is_null($this->following))
					$this->reloadFollowingCount();

				if(!is_null($this->followers))
					$this->reloadFollowerCount();
			}

			$this->saveToCache();
		}
		$stmt->close();
	}

	/**
	 * Returns the user's account ID
	 * 
	 * @access public
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Returns the user's display name
	 * 
	 * @access public
	 * @return string
	 */
	public function getDisplayName(){
		return $this->displayName;
	}

	/**
	 * Returns the user's username
	 * 
	 * @access public
	 * @return string
	 */
	public function getUsername(){
		return $this->username;
	}

	/**
	 * Returns the user's email address
	 * 
	 * @access public
	 * @return string
	 */
	public function getEmail(){
		return $this->email;
	}

	/**
	 * Returns the user's avatar URL
	 * 
	 * @access public
	 * @return string
	 */
	public function getAvatarURL(){
		return is_null($this->avatar) ? sprintf(GIGADRIVE_CDN_UPLOAD_FINAL_URL,"defaultAvatar.png") : $this->avatar;
	}

	/**
	 * Returns the user's bio
	 * 
	 * @access public
	 * @return string
	 */
	public function getBio(){
		return $this->bio;
	}

	/**
	 * Returns the Gigadrive API token
	 * 
	 * @access public
	 * @return string
	 */
	public function getToken(){
		return $this->token;
	}

	/**
	 * Returns the user's privacy level
	 * 
	 * @access public
	 * @return string
	 */
	public function getPrivacyLevel(){
		return $this->privacyLevel;
	}

	/**
	 * Returns the timestamp of the last time the user's data was updated with the Gigadrive API
	 * 
	 * @access public
	 * @return string
	 */
	public function getLastGigadriveUpdate(){
		return $this->lastGigadriveUpdate;
	}

	/**
	 * Returns the timestamp of when the Gigadrive account was created
	 * 
	 * @access public
	 * @return string
	 */
	public function getGigadriveRegistrationDate(){
		return $this->gigadriveJoinDate;
	}

	/**
	 * Updates the last gigadrive update date to right now
	 * 
	 * @access public
	 */
	public function updateLastGigadriveUpdate(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("UPDATE `users` SET `lastGigadriveUpdate` = CURRENT_TIMESTAMP WHERE `id` = ?");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$s = $mysqli->prepare("SELECT `lastGigadriveUpdate` FROM `users` WHERE `id` = ?");
			$s->bind_param("i",$this->id);
			if($s->execute()){
				$result = $s->get_result();
				
				if($result->num_rows){
					$this->lastGigadriveUpdate = $result->fetch_assoc()["lastGigadriveUpdate"];
					$this->saveToCache();
				}
			}
			$s->close();
		}
		$stmt->close();
	}

	/**
	 * Returns the registration time for the user
	 * 
	 * @access public
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * Gets the user's feed entries count
	 * 
	 * @access public
	 * @return int
	 */
	public function getFeedEntries(){
		if(is_null($this->feedEntries)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `feed` WHERE `user` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->feedEntries = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->feedEntries;
	}

	/**
	 * Gets the user's posts count
	 * 
	 * @access public
	 * @return int
	 */
	public function getPosts(){
		if(is_null($this->posts)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `feed` WHERE `user` = ? AND `type` = 'POST' AND `post` IS NULL");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->posts = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->posts;
	}

	/**
	 * Gets the user's followers count
	 * 
	 * @access public
	 * @return int
	 */
	public function getFollowers(){
		if(is_null($this->followers)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `following` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->followers = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->followers;
	}

	/**
	 * Gets the user's following count
	 * 
	 * @access public
	 * @return int
	 */
	public function getFollowing(){
		if(is_null($this->following)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `follower` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->following = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->following;
	}

	/**
	 * Returns the title of the user's Featured box
	 * 
	 * @access public
	 * @return string
	 */
	public function getFeaturedBoxTitle(){
		return $this->featuredBoxTitle;
	}

	/**
	 * Returns an array of user IDs that are featured in the user's Featured box
	 * 
	 * @access public
	 * @return int[]
	 */
	public function getFeaturedBoxContent(){
		return $this->featuredBoxContent;
	}

	/**
	 * Gets the user's favorites count
	 * 
	 * @access public
	 * @return int
	 */
	public function getFavorites(){
		if(is_null($this->favorites)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `favorites` WHERE `user` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					$this->favorites = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->favorites;
	}

	/**
	 * Reloads the favorites count
	 * 
	 * @access public
	 */
	public function reloadFavoritesCount(){
		$this->favorites = null;
		$this->getFavorites();
	}

	/**
	 * Adds a specific post to the user's favorites
	 * 
	 * @access public
	 * @param int $postId
	 */
	public function favorite($postId){
		if(!$this->hasFavorited($postId)){
			$post = FeedEntry::getEntryById($postId);

			if(!is_null($post) && $post->getType() == FEED_ENTRY_TYPE_POST){
				if(!$this->isBlocked($post->getUserId())){
					if(($this->id != $post->getUserId()) && (($post->getUser()->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE && !$this->isFollowing($post->getUserId())) || ($post->getUser()->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED))){
						return;
					}

					$mysqli = Database::Instance()->get();

					$stmt = $mysqli->prepare("INSERT INTO `favorites` (`user`,`post`) VALUES(?,?);");
					$stmt->bind_param("ii",$this->id,$postId);
					if($stmt->execute()){
						\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,true,5*60);

						if(!is_null($post))
							$post->reloadFavorites();

						if($post->getUser()->getId() != $this->id && $post->getUser()->canPostNotification(NOTIFICATION_TYPE_FAVORITE,$this->id,$postId)){
							$puid = $post->getUser()->getId();
							$pid = $post->getId();

							$s = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`follower`,`post`) VALUES(?,'FAVORITE',?,?);");
							$s->bind_param("iii",$puid,$this->id,$pid);
							$s->execute();
							$s->close();

							$post->getUser()->reloadUnreadNotifications();
						}
					}
					$stmt->close();
				}
			}
		}
	}

	/**
	 * Removes a specific post from the user's favorites
	 * 
	 * @access public
	 * @param int $postId
	 */
	public function unfavorite($postId){
		if($this->hasFavorited($postId)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("DELETE FROM `favorites` WHERE `user` = ? AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,false,5*60);

				$feedEntry = FeedEntry::getEntryById($postId);
				if(!is_null($feedEntry))
					$feedEntry->reloadFavorites();
			}
			$stmt->close();
		}
	}

	/**
	 * Returns whether the user has marked a specific post as their favorite
	 * 
	 * @access public
	 * @param int $postId
	 * @return bool
	 */
	public function hasFavorited($postId){
		if(\CacheHandler::existsInCache("favoriteStatus_" . $this->id . "_" . $postId)){
			return \CacheHandler::getFromCache("favoriteStatus_" . $this->id . "_" . $postId);
		} else {
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `favorites` WHERE `user` = ? AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					if($row["count"] > 0){
						\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,true,5*60);
					} else {
						\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,false,5*60);
					}
				}
			}
			$stmt->close();

			return \CacheHandler::getFromCache("favoriteStatus_" . $this->id . "_" . $postId);
		}
	}

	/**
	 * Shares a post with the own profile and news feed
	 * 
	 * @access public
	 * @param int $postId
	 */
	public function share($postId){
		if(!$this->hasShared($postId)){
			$mysqli = Database::Instance()->get();
			$sessionId = session_id();

			$stmt = $mysqli->prepare("INSERT INTO `feed` (`user`,`post`,`sessionId`,`type`) VALUES(?,?,?,'SHARE');");
			$stmt->bind_param("iis",$this->id,$postId,$sessionId);
			if($stmt->execute()){
				\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,true,5*60);

				$feedEntry = FeedEntry::getEntryById($postId);
				if(!is_null($feedEntry))
					$feedEntry->reloadShares();

				if($feedEntry->getUser()->getId() != $this->id && $feedEntry->getUser()->canPostNotification(NOTIFICATION_TYPE_SHARE,$this->id,$postId)){
					$puid = $feedEntry->getUser()->getId();
					$pid = $feedEntry->getId();

					$s = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`follower`,`post`) VALUES(?,'SHARE',?,?);");
					$s->bind_param("iii",$puid,$this->id,$pid);
					$s->execute();
					$s->close();

					$feedEntry->getUser()->reloadUnreadNotifications();
				}
			}
			$stmt->close();
		}
	}

	/**
	 * Removes a share made on a post
	 * 
	 * @access public
	 * @param int $postId
	 */
	public function unshare($postId){
		if($this->hasShared($postId)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("DELETE FROM `feed` WHERE `user` = ? AND `type` = 'SHARE' AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,false,5*60);

				$feedEntry = FeedEntry::getEntryById($postId);
				if(!is_null($feedEntry))
					$feedEntry->reloadShares();
			}
			$stmt->close();
		}
	}

	/**
	 * Returns whether the user has shared a post
	 * 
	 * @access public
	 * @param int $postId
	 */
	public function hasShared($postId){
		if(\CacheHandler::existsInCache("shareStatus_" . $this->id . "_" . $postId)){
			return \CacheHandler::getFromCache("shareStatus_" . $this->id . "_" . $postId);
		} else {
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` WHERE `user` = ? AND `type` = 'SHARE' AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					if($row["count"] > 0){
						\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,true,5*60);
					} else {
						\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,false,5*60);
					}
				}
			}
			$stmt->close();

			return \CacheHandler::getFromCache("shareStatus_" . $this->id . "_" . $postId);
		}
	}

	/**
	 * Reloads the feed entries count
	 * 
	 * @access public
	 */
	public function reloadFeedEntriesCount(){
		$this->feedEntries = null;
		$this->getFeedEntries();
	}

	/**
	 * Reloads the posts count
	 * 
	 * @access public
	 */
	public function reloadPostsCount(){
		$this->posts = null;
		$this->getPosts();
	}

	/**
	 * Reloads the following count
	 * 
	 * @access public
	 */
	public function reloadFollowingCount(){
		$this->following = null;
		$this->getFollowing();
	}

	/**
	 * Reloads the follower count
	 * 
	 * @access public
	 */
	public function reloadFollowerCount(){
		$this->followers = null;
		$this->getFollowers();
	}

	/**
	 * Returns whether or not the user followers $user
	 * 
	 * @access public
	 * @param int|User $user The user object or ID
	 * @return bool
	 */
	public function isFollowing($user){
		if(is_object($user))
			$user = $user->getId();

		if($user == $this->id) return false;

		if(in_array($this->id,User::getUserById($user)->cachedFollowers)){
			return true;
		} else {
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `follower` = ? AND `following` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					if($row["count"] > 0){
						User::getUserById($user)->cacheFollower($this->id);
						$this->saveToCache();
					}
				}
			}
			$stmt->close();

			return in_array($this->id,User::getUserById($user)->cachedFollowers);
		}
	}

	/**
	 * Returns whether or not the user has sent a follow request to $user
	 * 
	 * @access public
	 * @param int|User $user
	 * @return bool
	 */
	public function hasSentFollowRequest($user){
		$userId = is_object($user) ? $user->getId() : $user;

		$b = false;

		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follow_requests` WHERE `follower` = ? AND `following` = ?");
		$stmt->bind_param("ii",$this->id,$userId);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				if($row["count"] > 0)
					$b = true;
			}
		}
		$stmt->close();

		return $b;
	}

	/**
	 * Returns the amount of open follow requests the user has
	 */
	public function getOpenFollowRequests(){
		if($this->getPrivacyLevel() == "PRIVATE"){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follow_requests` WHERE `following` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					$this->followRequests = $row["count"];
					$this->saveToCache();
				}
			}
			$stmt->close();

			return $this->followRequests;
		} else {
			return 0;
		}
	}

	/**
	 * Reloads the amount of open follow requests the user has
	 * 
	 * @access public
	 */
	public function reloadOpenFollowRequests(){
		if($this->getPrivacyLevel() == "PRIVATE"){
			$this->followRequests = null;
			$this->getOpenFollowRequests();
		}
	}

	/**
	 * Returns whether or not the user has received a follow request from $user
	 * 
	 * @access public
	 * @param int|User $user
	 * @return bool
	 */
	public function hasReceivedFollowRequest($user){
		if(is_object($user)){
			return $user->hasSentFollowRequest($this);
		} else {
			return self::getUserById($user)->hasSentFollowRequest($this);
		}
	}

	/**
	 * Returns whether or not the user has blocked $user
	 * 
	 * @access public
	 * @param int|User $user The user object or ID
	 * @return bool
	 */
	public function hasBlocked($user){
		if(is_object($user))
			$user = $user->getId();

		if($user == $this->id) return false;

		if(in_array($user,$this->cachedBlocks)){
			return true;
		} else {
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `blocks` WHERE `user` = ? AND `target` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					if($row["count"] > 0){
						array_push($this->cachedBlocks,$user);
						$this->saveToCache();
					}
				}
			}
			$stmt->close();

			return in_array($user,$this->cachedBlocks);
		}
	}

	/**
	 * Returns whether the user was blocked by $user
	 * 
	 * @access public
	 * @param int|User The user object or ID
	 * @return bool
	 */
	public function isBlocked($user){
		if(!is_object($user))
			$user = self::getUserById($user);

		return !Is_null($user) ? $user->hasBlocked($this) : false;
	}

	/**
	 * Returns whether or not the user is followed by $user
	 * 
	 * @access public
	 * @param int|User $user The user object or ID
	 * @return bool
	 */
	public function isFollower($user){
		return is_object($user) ? $user->isFollowing($this) : self::getUserById($user)->isFollowing($this);
		/*if(!is_object($user))
			$user = self::getUserById($user);

		if(in_array($this->id,$user->cachedFollowers)){
			return true;
		} else {
			$mysqli = Database::Instance()->get();

			$uID = $user->getId();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `follower` = ? AND `following` = ?");
			$stmt->bind_param("ii",$uID,$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					if($row["count"] > 0){
						array_push($user->cachedFollowers,$this->getId());
						$user->saveToCache();
					}
				}
			}
			$stmt->close();

			return in_array($this->id,$user->cachedFollowers);
		}*/
	}

	/**
	 * Adds a user ID to the follower cache
	 * 
	 * @access public
	 * @param int $user
	 */
	public function cacheFollower($user){
		if(!in_array($user,$this->cachedFollowers))
			array_push($this->cachedFollowers,$user);
		
		$this->saveToCache();
	}

	/**
	 * Removes a user ID to the follower cache
	 * 
	 * @access public
	 * @param int $user
	 */
	public function uncacheFollower($user){
		if(in_array($user,$this->cachedFollowers))
			$this->cachedFollowers = Util::removeFromArray($this->cachedFollowers,$user);
		
		$this->saveToCache();
	}

	/**
	 * Follows a user
	 * 
	 * @access public
	 * @param int|User $user
	 */
	public function follow($user){
		if($this->getFollowers() >= FOLLOW_LIMIT) return;

		if(is_object($user))
			$user = $user->getId();

		if(!$this->isFollowing($user)){
			$mysqli = Database::Instance()->get();

			$u = self::getUserById($user);
			if(!is_null($u)){
				if($u->getPrivacyLevel() == "CLOSED"){
					return;
				} else if($u->getPrivacyLevel() == "PRIVATE"){
					if(!$this->hasSentFollowRequest($user)){
						$stmt = $mysqli->prepare("INSERT INTO `follow_requests` (`follower`,`following`) VALUES(?,?);");
						$stmt->bind_param("ii",$this->id,$user);
						$stmt->execute();
						$stmt->close();

						self::getUserById($user)->reloadOpenFollowRequests();

						return;
					} else {
						$stmt = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `follower` = ? AND `following` = ?");
						$stmt->bind_param("ii",$this->id,$user);
						$stmt->execute();
						$stmt->close();

						self::getUserById($user)->reloadOpenFollowRequests();
					}
				}
			}

			$b = false;

			$stmt = $mysqli->prepare("INSERT INTO `follows` (`follower`,`following`) VALUES(?,?);");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				if(!is_null($u)){
					$u->cacheFollower($this->id);
					$u->reloadFollowerCount();
					$this->reloadFollowingCount();
					
					$b = true;
				}
			}
			$stmt->close();

			if($b){
				$sID = session_id();

				if($this->canPostFeedEntry(FEED_ENTRY_TYPE_NEW_FOLLOWING,$user)){
					$stmt = $mysqli->prepare("INSERT INTO `feed` (`user`,`following`,`type`,`sessionId`) VALUES(?,?,'NEW_FOLLOWING',?);");
					$stmt->bind_param("iis",$this->id,$user,$sID);
					$stmt->execute();
					$stmt->close();
				}

				if(self::getUserById($user)->canPostNotification(NOTIFICATION_TYPE_NEW_FOLLOWER,$this->id,null)){
					$stmt = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`follower`) VALUES(?,'NEW_FOLLOWER',?);");
					$stmt->bind_param("ii",$user,$this->id);
					$stmt->execute();
					$stmt->close();

					self::getUserById($user)->reloadUnreadNotifications();
				}
			}

			$this->followingArray = null;
			$this->saveToCache();
		}
	}

	/**
	 * Unfollows a user
	 * 
	 * @access public
	 * @param int|User $user
	 */
	public function unfollow($user){
		if(is_object($user))
			$user = $user->getId();

		if($this->isFollowing($user)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("DELETE FROM `follows` WHERE `follower` = ? AND `following` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$u = self::getUserById($user);
				if(!is_null($u)){
					$u->uncacheFollower($this->id);
					$u->reloadFollowerCount();
					$this->reloadFollowingCount();
				}
			}
			$stmt->close();
		}
	}

	/**
	 * Blocks a user
	 * 
	 * @access public
	 * @param int|User $user
	 */
	public function block($user){
		if(is_object($user))
			$user = $user->getId();

		if($user == $this->id) return;

		if(!$this->hasBlocked($user)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("INSERT INTO `blocks` (`user`,`target`) VALUES(?,?);");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				array_push($this->cachedBlocks,$user);
				$this->saveToCache();
			}
			$stmt->close();

			if($this->isFollowing($user))
				$this->unfollow($user);

			if(self::getUserById($user)->isFollowing($this))
				self::getUserById($user)->unfollow($this);
		}
	}

	/**
	 * Unblocks a user
	 * 
	 * @access public
	 * @param int|User $user
	 */
	public function unblock($user){
		if(is_object($user))
			$user = $user->getId();

		if($user == $this->id) return;

		if($this->hasBlocked($user)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("DELETE FROM `blocks` WHERE `user` = ? AND `target` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$this->cachedBlocks = Util::removeFromArray($this->cachedBlocks,$user);
				$this->saveToCache();
			}
			$stmt->close();
		}
	}

	/**
	 * Returns an array with the IDs of all users that are followed by this one
	 * 
	 * @access public
	 * @return array
	 */
	public function getFollowingAsArray(){
		if(is_null($this->followingArray)){
			$this->followingArray = [];

			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT `following` FROM `follows` WHERE `follower` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					while($row = $result->fetch_assoc()){
						array_push($this->followingArray,$row["following"]);
					}
				}
			}
			$stmt->close();

			$this->saveToCache();
		}

		return $this->followingArray;
	}

	/**
	 * Returns true if a feed entry may be posted with the given parameters
	 * 
	 * @access public
	 * @param string $type
	 * @param int $following
	 * @return bool
	 */
	public function canPostFeedEntry($type,$following){
		$b = true;

		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `feed` WHERE `user` = ? AND `type` = ? AND `following` = ? AND `time` > (NOW() - INTERVAL 4 DAY)");
		$stmt->bind_param("isi",$this->id,$type,$following);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				if($row["count"] > 0){
					$b = false;
				} else {
					$s = $mysqli->prepare("SELECT `type`,`following` FROM `feed` WHERE `user` = ? ORDER BY `time` DESC LIMIT 10");
					$s->bind_param("i",$this->id);
					if($s->execute()){
						$result = $s->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								if($row["type"] == $type && $row["following"] == $following){
									$b = false;
									break;
								}
							}
						}
					}
					$s->close();
				}
			}
		}
		$stmt->close();

		return $b;
	}

	/**
	 * Returns true if a notification may be posted with the given parameters
	 * 
	 * @access public
	 * @param string $type
	 * @param int $follower
	 * @param int $post
	 * @return bool
	 */
	public function canPostNotification($type,$follower,$post){
		$b = true;

		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `notifications` WHERE `user` = ? AND `type` = ? AND `follower` = ? AND `post` " . (is_null($post) ? " IS NULL" : "= " . $post) . " AND `time` > (NOW() - INTERVAL 4 DAY)");
		$stmt->bind_param("isi",$this->id,$type,$follower);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				if($row["count"] > 0){
					$b = false;
				} else {
					$s = $mysqli->prepare("SELECT `type`,`follower`,`post` FROM `notifications` WHERE `user` = ? ORDER BY `time` DESC LIMIT 10");
					$s->bind_param("i",$this->id);
					if($s->execute()){
						$result = $s->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								if($row["type"] == $type && $row["follower"] == $follower && $row["post"] == $post){
									$b = false;
									break;
								}
							}
						}
					}
					$s->close();
				}
			}
		}
		$stmt->close();

		return $b;
	}

	/**
	 * Returns the number of unread messages the user has
	 * 
	 * @access public
	 * @return int
	 */
	public function getUnreadMessages(){
		if(is_null($this->unreadMessages)){
			$this->reloadUnreadMessages();
		}

		return $this->unreadMessages;
	}

	/**
	 * Updates the number of unread messages the user has
	 * 
	 * @access public
	 */
	public function reloadUnreadMessages(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `messages` WHERE `receiver` = ? AND `is_read` = 0");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				$this->unreadMessages = $row["count"];

				$this->saveToCache();
			}
		}
		$stmt->close();
	}

	/**
	 * Returns the number of unread notifications the user has
	 * 
	 * @access public
	 * @return int
	 */
	public function getUnreadNotifications(){
		if(is_null($this->unreadNotifications)){
			$this->reloadUnreadNotifications();
		}

		return $this->unreadNotifications;
	}

	/**
	 * Updates the number of unread messages the user has
	 * 
	 * @access public
	 */
	public function reloadUnreadNotifications(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `notifications` WHERE `user` = ? AND `seen` = 0");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				$this->unreadNotifications = $row["count"];

				$this->saveToCache();
			}
		}
		$stmt->close();
	}

	/**
	 * Marks all unread notifications as read
	 * 
	 * @access public
	 */
	public function markNotificationsAsRead(){
		$this->unreadNotifications = 0;

		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("UPDATE `notifications` SET `seen` = 1 WHERE `user` = ? AND `seen` = 0");
		$stmt->bind_param("i",$this->id);
		$stmt->execute();
		$stmt->close();

		$this->saveToCache();
	}

	/**
	 * Saves the user object to the cache
	 * 
	 * @access public
	 */
	public function saveToCache(){
		\CacheHandler::setToCache("user_id_" . $this->id,$this,20*60);
		\CacheHandler::setToCache("user_name_" . strtolower($this->username),$this,20*60);
	}

	/**
	 * Removes the user object from the cache
	 * 
	 * @access public
	 */
	public function removeFromCache(){
		\CacheHandler::deleteFromCache("user_id_" . $this->id);
		\CacheHandler::deleteFromCache("user_name_" . strtolower($this->username));
	}
}