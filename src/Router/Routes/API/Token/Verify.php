<?php

$app->bind("/api/token/verify",function(){
	api_headers($this);

	if(isset($_POST["token"])){
		if(!Util::isEmpty($_POST["token"])){
			$tokenString = $_POST["token"];

			if(isset($_POST["user"])){
				if(!Util::isEmpty($_POST["user"])){
					$userID = $_POST["user"];

					$token = Token::getTokenById($tokenString);

					if(!is_null($token)){
						$user = User::getUserById($userID);

						if(!is_null($user)){
							if($token->getUserId() == $userID){
								if(!$user->isSuspended()){
									if(!$token->isExpired()){
										$token->renew();

										return json_encode(["status" => "Token valid","user" => $user->toAPIJson($token->getUser(),false)]);
									} else {
										return json_encode(["status" => "Token expired"]);
									}
								} else {
									return json_encode(["error" => "User suspended"]);
								}
							} else {
								return json_encode(["error" => "Invalid user"]);
							}
						} else {
							return json_encode(["error" => "Invalid user"]);
						}
					} else {
						return json_encode(["error" => "Invalid token"]);
					}
				} else {
					return json_encode(["error" => "Invalid user"]);
				}
			} else {
				return json_encode(["error" => "Invalid user"]);
			}
		} else {
			return json_encode(["error" => "Invalid token"]);
		}
	} else {
		return json_encode(["error" => "Invalid token"]);
	}
});