<?php

$app->bind("/api/user/info",function(){
	api_headers($this);

	if(isset($_GET["token"])){
		if(!Util::isEmpty($_GET["token"])){
			$token = Token::getTokenById($_GET["token"]);

			if(!is_null($token)){
				if(!$token->isExpired()){
					if(isset($_GET["user"])){
						if(!Util::isEmpty($_GET["user"])){
							$user = User::getUserByUsername($_GET["user"]);
							if(is_null($user) && is_numeric($_GET["user"])) $user = User::getUserById($_GET["user"]);

							if(!is_null($user)){
								$followersYouKnow = [];

								foreach($user->followersYouFollow($token->getUser()) as $u){
									array_push($followersYouKnow,$u->toAPIJson($token->getUser(),false));
								}

								$a = $user->toAPIJson($token->getUser(),false);
								$a["followersYouKnow"] = $followersYouKnow;

								return json_encode($a);
							} else {
								return json_encode(["error" => "Unknown user"]);
							}
						} else {
							return json_encode(["error" => "Invalid user"]);
						}
					} else {
						return json_encode(["error" => "Invalid user"]);
					}
				} else {
					return json_encode(["error" => "Token expired"]);
				}
			} else {
				return json_encode(["error" => "Invalid token"]);
			}
		} else {
			return json_encode(["error" => "Invalid token"]);
		}
	} else {
		return json_encode(["error" => "Invalid token"]);
	}
});