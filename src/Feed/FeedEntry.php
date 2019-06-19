<?php

/**
 * Represents a feed entry
 * 
 * @package FeedEntry
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class FeedEntry {
    /**
     * Returns a feed entry object fetched by the ID of the entry
     * 
     * @access public
     * @param int $id
     * @return FeedEntry
     */
    public static function getEntryById($id){
        $n = "feedEntry_" . $id;

        if(\CacheHandler::existsInCache($n)){
            return \CacheHandler::getFromCache($n);
        } else {
            $entry = new self($id);
            $entry->reload();

            if($entry->exists()){
                return $entry;
            } else {
                return null;
            }
        }
    }

    /**
     * Returns whether a feed entry is cached by ID
     * 
     * @access public
     * @param int $id
     * @return bool
     */
    public static function isCached($id){
        return \CacheHandler::existsInCache("feedEntry_" . $id);
    }

    /**
     * @access private
     * @var int $id
     */
    private $id;

    /**
     * @access private
     * @var int $user
     */
    private $user;

    /**
     * @access private
     * @var string $text
     */
    private $text;

    /**
     * @access private
     * @var int $following
     */
    private $following;

    /**
     * @access private
     * @var int $post
     */
    private $post;

    /**
     * @access private
     * @var string $sessionId
     */
    private $sessionId;

    /**
     * @access private
     * @var string $type
     */
    private $type;

    /**
     * @access private
     * @var bool $nsfw
     */
    private $nsfw;

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
     * @var int $replies
     */
    private $replies;

    /**
     * @access private
     * @var int[]|null
     */
    private $replyArray;

    /**
     * @access private
     * @var int $shares
     */
    private $shares;

    /**
     * @access private
     * @var ShareSample|null $shareSample
     */
    private $shareSample;

    /**
     * @access private
     * @var int $favorites
     */
    private $favorites;

    /**
     * @access private
     * @var FavoriteSample|null $favoriteSample
     */
    private $favoriteSample;

    /**
     * @access private
     * @var int[] $attachments
     */
    private $attachments;

    /**
     * Constructor
     * 
     * @access protected
     * @param int $id
     */
    protected function __construct($id){
        $this->id = $id;
    }

    /**
     * Reloads data from the database
     * 
     * @access public
     */
    public function reload(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT * FROM `feed` WHERE `id` = ?");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->id = $row["id"];
                $this->user = $row["user"];
                $this->text = $row["text"];
                $this->following = $row["following"];
                $this->post = $row["post"];
                $this->sessionId = $row["sessionId"];
                $this->type = $row["type"];
                $this->nsfw = $row["nsfw"];
                $this->replies = $row["count.replies"];
                $this->shares = $row["count.shares"];
                $this->favorites = $row["count.favorites"];
                $this->attachments = is_null($row["attachments"]) ? [] : json_decode($row["attachments"],true);
                $this->time = $row["time"];
                $this->exists = true;

                $this->saveToCache();
            }
        }
        $stmt->close();
    }

    /**
     * Returns the ID of the feed entry
     * 
     * @access public
     * @return int
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Returns the ID of the user that created the feed entry
     * 
     * @access public
     * @return int
     */
    public function getUserId(){
        return $this->user;
    }

    /**
     * Returns the user object of the user that created the feed entry
     * 
     * @access public
     * @return User
     */
    public function getUser(){
        return User::getUserById($this->user);
    }

    /**
     * Returns the text of the feed entry, null if no text is available
     * 
     * @access public
     * @return string
     */
    public function getText(){
        return $this->text;
    }

    /**
     * Returns the ID of the user that was followed (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return int
     */
    public function getFollowingId(){
        return $this->following;
    }

    /**
     * Returns the user object of the user that was followed (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return User
     */
    public function getFollowing(){
        return !is_null($this->following) ? User::getUserById($this->following) : null;
    }

    /**
     * Returns the ID of the post that was shared/replied to (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return int
     */
    public function getPostId(){
        return $this->post;
    }

    /**
     * Returns the feed entry object of the post that was shared/replied to (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return FeedEntry
     */
    public function getPost(){
        return !is_null($this->post) ? self::getEntryById($this->post) : null;
    }

    /**
     * Returns the session ID used by $user when creating this feed entry
     * 
     * @access public
     * @return string
     */
    public function getSessionId(){
        return $this->sessionId;
    }

    /**
     * Returns the type of the feed entry
     * 
     * @access public
     * @return string
     */
    public function getType(){
        return $this->type;
    }

    /**
     * Returns whether this post was marked as NSFW
     * 
     * @access public
     * @return bool
     */
    public function isNSFW(){
        return $this->nsfw;
    }

    /**
     * Returns an array of the IDs of the attachments
     * 
     * @access public
     * @return int[]
     */
    public function getAttachments(){
        return $this->attachments;
    }

    /**
     * Returns an array of the MediaFile objects of the attachments
     * 
     * @access public
     * @return MediaFile[]
     */
    public function getAttachmentObjects(){
        $array = [];

        foreach($this->attachments as $a){
            $m = MediaFile::getMediaFileFromID($a);

            if(!is_null($m))
                array_push($array,$m);
        }

        return $array;
    }

    /**
     * Returns the timestamp of when the feed entry was created
     * 
     * @access public
     * @return string
     */
    public function getTime(){
        return $this->time;
    }

    public function exists(){
        return $this->exists;
    }

    /**
     * Returns how often the feed entry was replied to
     * 
     * @access public
     * @return int
     */
    public function getReplies(){
        return $this->replies;
    }

    /**
     * Returns objects of the replies of this feed entry in chronological order, if available
     * 
     * @access public
     * @param int $limit The limit of how many replies should be loaded
     * @return FeedEntry[]|null Returns null if the replies could not be loaded
     */
    public function getReplyArray($limit = 10){
        if(is_null($this->replyArray) && $this->type == FEED_ENTRY_TYPE_POST && $this->getReplies() > 0){
            $mysqli = Database::Instance()->get();
							
			$postId = $this->id;
			$uid = $this->user;

			$stmt = $mysqli->prepare("SELECT f.id AS feedEntryId,u.id AS userId FROM `feed` AS f INNER JOIN `users` AS u ON f.user = u.id WHERE f.`post` = ? AND f.`type` = 'POST' ORDER BY u.`id` = ?,f.`time` DESC");
            $stmt->bind_param("ii",$postId,$uid);
            if($stmt->execute()){
                $this->replyArray = [];

				$result = $stmt->get_result();

				if($result->num_rows){
                    while($row = $result->fetch_assoc()){
                        $f = FeedEntry::getEntryById($row["feedEntryId"]);
                        $u = User::getUserById($row["userId"]);
                        
                        if(is_null($f) || is_null($u)) continue;

						array_push($this->replyArray,$row["feedEntryId"]);
                    }
                    
                    $this->saveToCache();
				}
			}
			$stmt->close();
        }

        if(!is_null($this->replyArray)){
            $a = [];
            foreach ($this->replyArray as $id) {
                $f = self::getEntryById($id);

                if(!is_null($f)){
                    array_push($a,$f);
                }
            }

            return $a;
        } else {
            return null;
        }
    }

    /**
     * Reloads the reply count
     * 
     * @access public
     */
    public function reloadReplies(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` WHERE `post` = ? AND `type` = 'POST'");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->replies = $row["count"];
                $this->replyArray = null;

                $this->saveToCache();
            }
        }
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE `feed` SET `count.replies` = ? WHERE `id` = ?");
        $stmt->bind_param("ii",$this->replies,$this->id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Returns how often the feed entry was shared
     * 
     * @access public
     * @return int
     */
    public function getShares(){
        return $this->shares;
    }

    /**
     * Reloads the share count
     * 
     * @access public
     */
    public function reloadShares(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` WHERE `post` = ? AND `type` = 'SHARE'");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->shares = $row["count"];
                $this->shareSample = null;

                $this->saveToCache();
            }
        }
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE `feed` SET `count.shares` = ? WHERE `id` = ?");
        $stmt->bind_param("ii",$this->shares,$this->id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Returns a sample of users that shared this post, null if the sample could not be loaded or the feed entry is not a post.
     * 
     * @access public
     * @return ShareSample|null
     */
    public function getShareSample(){
        if($this->type === FEED_ENTRY_TYPE_POST && is_null($this->shareSample)){
            $shareSample = new ShareSample($this);

            if(!is_null($shareSample->getUsers())){
                $this->shareSample = $shareSample;
                $this->saveToCache();
            }
        }

        return $this->shareSample;
    }

    /**
     * Returns how often the feed entry was favorized
     * 
     * @access public
     * @return int
     */
    public function getFavorites(){
        return $this->favorites;
    }

    /**
     * Reloads the favorite count
     * 
     * @access public
     */
    public function reloadFavorites(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `favorites` WHERE `post` = ?");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->favorites = $row["count"];
                $this->favoriteSample = null;

                $this->saveToCache();
            }
        }
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE `feed` SET `count.favorites` = ? WHERE `id` = ?");
        $stmt->bind_param("ii",$this->favorites,$this->id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Returns a sample of users that shared this post, null if the sample could not be loaded or the feed entry is not a post.
     * 
     * @access public
     * @return FavoriteSample|null
     */
    public function getFavoriteSample(){
        if($this->type === FEED_ENTRY_TYPE_POST && is_null($this->favoriteSample)){
            $favoriteSample = new FavoriteSample($this);

            if(!is_null($favoriteSample->getUsers())){
                $this->favoriteSample = $favoriteSample;
                $this->saveToCache();
            }
        }

        return $this->favoriteSample;
    }

    /**
     * Deletes the feed entry and all its references from the database
     * 
     * @access public
     */
    public function delete(){
        $mysqli = Database::Instance()->get();

        $this->removeFromCache();

        $stmt = $mysqli->prepare("DELETE FROM `feed` WHERE `id` = ? OR (`post` = ? AND `type` = 'SHARE')");
        $stmt->bind_param("ii",$this->id,$this->id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("DELETE FROM `notifications` WHERE `post` = ?");
        $stmt->bind_param("i",$this->id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("DELETE FROM `favorites` WHERE `post` = ?");
        $stmt->bind_param("i",$this->id);
        $stmt->execute();
        $stmt->close();

        $parent = $this->getPost();
        if(!is_null($parent) && $this->type == "POST"){
            $this->getPost()->reloadReplies();
        }

        if($this->type == "POST") $this->getUser()->reloadPostsCount();
	}
	
	/**
	 * Returns whether the current user may view this feed entry
	 * 
	 * @access public
	 * @return bool
	 */
	public function mayView(){
		return $this->getUser()->mayView() && (!is_null($this->getPost()) ? $this->getPost()->getUser()->mayView() : true);
	}

	/**
	 * Returns this object as json object to be used in the API
	 * 
	 * @access public
	 * @param bool $encode If true, will return a json string, else an associative array
	 * @return string|array
	 */
	public function toAPIJson($encode = true){
		$ar = [];
		foreach($this->getAttachmentObjects() as $at){
			array_push($ar,$at->toAPIJson());
		}

		$a = [
			"id" => $this->id,
			"user" => !is_null($this->getUser()) ? $this->getUser()->toAPIJson() : null,
			"text" => $this->text,
			"referencedUser" => !is_null($this->getFollowing()) ? $this->getFollowing()->toAPIJson() : null,
			"referencedPost" => !is_null($this->getPost()) ? $this->getPost()->toAPIJson() : null,
			"type" => $this->type,
			"replies" => $this->replies,
			"shares" => $this->shares,
			"favorites" => $this->favorites,
			"attachments" => $ar,
			"time" => $this->time
		];

		return $encode == true ? json_encode($a) : $a;
	}

    /**
     * Returns HTML code to use in a feed entry list (search, profile, home feed, ...)
     * 
     * @access public
     * @param bool $noBorder If false, the additional HTML for easier use in bootstrap lists won't be included
	 * @param bool $hideAttachments If true, the attachments will be hidden
     * @return string
     */
    public function toListHTML($noBorder = false, $hideAttachments = false){
		if(!$this->mayView()) return "";

		$user = $this->getUser();
		if(is_null($user)) return "";

		$s = "";

        if($this->getType() == "POST"){
            if($noBorder == false) $s .= '<li class="list-group-item px-0 py-0 feedEntry statusTrigger" data-status-render="' . $this->getId() . '" data-entry-id="' . $this->getId() . '">';
            $s .= '<div class="px-4 py-2">';
			$s .= '<div class="row">';
            $s .= '<div class="float-left">';
			$s .= '<a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick float-left">';
			$s .= '<img class="rounded mx-1 my-1" src="' . $user->getAvatarURL() . '" width="40" height="40"/>';
            $s .= '</a>';
            $s .= '<p class="float-left ml-1 mb-0">';
			$s .= '<a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<span class="font-weight-bold convertEmoji">' . $user->getDisplayName() . $user->renderCheckMark() . '</span>';
			$s .= '</a>';

			$s .= '<span class="text-muted font-weight-normal"> @' . $user->getUsername() . ' </span>';

			$s .= '<br/>';

            $s .= '<span class="small text-muted"><i class="far fa-clock"></i> ';
            $s .= Util::timeago($this->getTime());
            $s .= '</span>';

			$s .= '</p>';
            $s .= '</div>';
            
            if($this->nsfw){
                $s .= '</div>';
                $s .= '</div>';

                $s .= '<div class="nsfwInfo ignoreParentClick bg-' . (Util::isUsingNightMode() ? "dark" : "light") . ' text-' . (Util::isUsingNightMode() ? "white" : "muted") . ' text-center py-4">';
                $s .= '<div style="font-size: 26px;"><i class="fas fa-exclamation-triangle"></i></div>';
                $s .= 'This post was marked as NSFW. Click to show.';
                $s .= '</div>';

                $s .= '<div class="px-4">';
                $s .= '<div class="row">';
            }

            if(!is_null($this->getText())) $s .= '<div class="float-left ml-1 my-2" style="width: 100%">';
            
            if(!is_null($this->getText())){
                $s .= '<p class="mb-0 convertEmoji' . ($this->nsfw ? ' hiddenNSFW d-none' : '') . '" style="word-wrap: break-word;">';
                $s .= Util::convertPost($this->getText());
                $s .= '</p>';
            }

            if($hideAttachments == false && count($this->attachments) > 0){
                if(!is_null($this->getText())) $s .= '</div>';
                $s .= '</div>';
                $s .= '</div>';

                if($this->nsfw){
                    $s .= '<div class="hiddenNSFW d-none">';
                }

                $s .= Util::renderAttachmentEmbeds($this->getAttachmentObjects(),$this->id);
                
                if($this->nsfw){
                    $s .= '</div>';
                }

                $s .= '<div class="px-4">';
                $s .= '<div class="row">';
                $s .= '<div class="float-left ml-1 my-2" style="width: 100%">';
            }

			$s .= Util::getPostActionButtons($this);
			$s .= '</div>';
            $s .= '</div>';
            $s .= '</div>';
            if($noBorder == false) $s .= '</li>';

            return $s;
        } else if($this->getType() == "NEW_FOLLOWING"){
            $u2 = $this->getFollowing();
			if (is_null($u2)) return "";
				
            if($noBorder == false) $s .= '<li class="list-group-item px-2 py-2" data-entry-id="' . $this->getId() . '">';
            
			if(Util::isLoggedIn() && Util::getCurrentUser()->getId() == $this->getUserId()){
			    $s .= '<div class="float-right">';
			    $s .= '<span class="deleteButton ml-2" data-post-id="' . $this->getId() . '" data-toggle="tooltip" title="Delete">';
                $s .= '<i class="fas fa-trash-alt"></i>';
			    $s .= '</span>';
			    $s .= '</div>';
            }
            
			$s .= '<i class="fas fa-user-plus text-info"></i> <b><a href="/' . $user->getUsername() . '" class="clearUnderline convertEmoji">' . $user->getDisplayName() . $user->renderCheckMark() . '</a></b> is now following <a href="/' . $u2->getUsername() . '" class="clearUnderline convertEmoji">' . $u2->getDisplayName() . '</a> &bull; <span class="text-muted">' . Util::timeago($this->getTime()) . '</span>';
            if($noBorder == false) $s .= '</li>';
            
            return $s;
        } else if($this->getType() == "SHARE"){
            $sharedPost = $this->getPost();
			$sharedUser = $sharedPost->getUser();

			if(is_null($sharedPost) || is_null($sharedUser))
				return "";

            if($noBorder == false) $s .= '<li class="list-group-item px-0 py-0 feedEntry statusTrigger" data-status-render="' . $sharedPost->getId() . '" data-entry-id="' . $this->getId() . '">';
            $s .= '<div class="px-4 py-2">';
			$s .= '<div class="small text-muted">';
			$s .= '<i class="fas fa-share-alt text-blue"></i> Shared by <a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick">' . $user->getDisplayName() . $user->renderCheckMark() . '</a> &bull; ' . Util::timeago($this->getTime());
			$s .= '</div>';
			$s .= '<div class="row">';
			$s .= '<div class="float-left">';
			$s .= '<a href="/' . $sharedUser->getUsername() . '" class="clearUnderline ignoreParentClick float-left">';
			$s .= '<img class="rounded mx-1 my-1" src="' . $sharedUser->getAvatarURL() . '" width="40" height="40"/>';
            $s .= '</a>';
            $s .= '<p class="float-left ml-1 mb-0">';
			$s .= '<a href="/' . $sharedUser->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<span class="font-weight-bold convertEmoji">' . $sharedUser->getDisplayName() . $sharedUser->renderCheckMark() . '</span>';
			$s .= '</a>';

			$s .= '<span class="text-muted font-weight-normal"> @' . $sharedUser->getUsername() . ' </span>';

			$s .= '<br/>';

            $s .= '<span class="small text-muted"><i class="far fa-clock"></i> ';
            $s .= Util::timeago($sharedPost->getTime());
            $s .= '</span>';

            $s .= '</p>';
            $s .= '</div>';

            if($sharedPost->isNSFW()){
                $s .= '</div>';
                $s .= '</div>';

                $s .= '<div class="nsfwInfo ignoreParentClick bg-' . (Util::isUsingNightMode() ? "dark" : "light") . ' text-' . (Util::isUsingNightMode() ? "white" : "muted") . ' text-center py-4">';
                $s .= '<div style="font-size: 26px;"><i class="fas fa-exclamation-triangle"></i></div>';
                $s .= 'This post was marked as NSFW. Click to show.';
                $s .= '</div>';

                $s .= '<div class="px-4">';
                $s .= '<div class="row">';
            }

            if(!is_null($sharedPost->getText())) $s .= '<div class="float-left ml-1" style="width: 100%">';
            
            $parent = $sharedPost->getPost();
            if(!is_null($parent)){
                $parentCreator = $parent->getUser();

                if(!is_null($parentCreator)){
                    $s .= '<div class="text-muted small">';
                    $s .= 'Replying to <a href="/' . $parentCreator->getUsername() . '">@' . $parentCreator->getUsername() . '</a>';
                    $s .= '</div>';
                }
            }

            if(!is_null($sharedPost->getText())){
                $s .= '<p class="mb-0 convertEmoji' . ($sharedPost->isNSFW() ? ' hiddenNSFW d-none' : '') . '" style="word-wrap: break-word;">';
                $s .= Util::convertPost($sharedPost->getText());
                $s .= '</p>';
            }

			if($hideAttachments == false && count($sharedPost->attachments) > 0){
				if(!is_null($sharedPost->getText())) $s .= '</div>';
                $s .= '</div>';
                $s .= '</div>';

                if($sharedPost->isNSFW()){
                    $s .= '<div class="hiddenNSFW d-none">';
                }

                $s .= Util::renderAttachmentEmbeds($sharedPost->getAttachmentObjects(),$this->id);
                
                if($sharedPost->isNSFW()){
                    $s .= '</div>';
                }

                $s .= '<div class="px-4">';
                $s .= '<div class="row">';
                $s .= '<div class="float-left ml-1 my-2" style="width: 100%">';
            }

			$s .= Util::getPostActionButtons($sharedPost);
			$s .= '</div>';
            $s .= '</div>';
            $s .= '</div>';
            if($noBorder == false) $s .= '</li>';
            
            return $s;
        }

        return "";
    }

    public function saveToCache(){
        \CacheHandler::setToCache("feedEntry_" . $this->id,$this,\CacheHandler::OBJECT_CACHE_TIME);
    }

    public function removeFromCache(){
        \CacheHandler::deleteFromCache("feedEntry_" . $this->id);
    }
}