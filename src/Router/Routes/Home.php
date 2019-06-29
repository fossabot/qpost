<?php

use Gigadrive\MailTemplates\MailTemplates;
use qpost\Account\User;
use qpost\Database\Database;
use qpost\Util\Util;

$app->bind("/",function(){
	if(!Util::isLoggedIn()){
		$errorMsg = null;
		$successMsg = null;

		if (isset($_POST["email"]) && isset($_POST["displayName"]) && isset($_POST["username"]) && isset($_POST["password"])) {
			$email = trim(Util::fixString($_POST["email"]));
			$displayName = trim(Util::fixString($_POST["displayName"]));
			$username = trim(Util::fixString($_POST["username"]));
			$password = trim($_POST["password"]);

			if (!Util::isEmpty($email) && !Util::isEmpty($displayName) && !Util::isEmpty($username) && !Util::isEmpty($password)) {
				if (strlen($email) >= 3) {
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						if (strlen($displayName) >= 1 && strlen($displayName) <= 25) {
							if (strlen($username) >= 3) {
								if (strlen($username) <= 16) {
									if (ctype_alnum($username)) {
										if (!Util::contains($displayName, "☑️") && !Util::contains($displayName, "✔️") && !Util::contains($displayName, "✅") && !Util::contains($displayName, "🗹") && !Util::contains($displayName, "🗸")) {
											if (Util::isEmailAvailable($email)) {
												if (Util::isUsernameAvailable($username)) {
													$displayName = Util::sanatizeString($displayName);

													$mysqli = Database::Instance()->get();

													$emailToken = Util::getRandomString(7);

													$password = password_hash($password, PASSWORD_BCRYPT);

													$stmt = $mysqli->prepare("INSERT INTO `users` (`displayName`,`username`,`email`,`password`,`emailActivationToken`) VALUES(?,?,?,?,?);");
													$stmt->bind_param("sssss", $displayName, $username, $email, $password, $emailToken);
													if ($stmt->execute()) {
														$id = $stmt->insert_id;

														$mailContent = MailTemplates::readTemplate("verifyEmail", [
															"qpost: Verify your email address",
															"Complete your qpost registration!",
															"Hello, " . $displayName . "!",
															"To complete the creation of your qpost account, please click the button below and verify your email address.",
															"https://qpost.gigadrivegroup.com/account/verify-email?account=" . $id . "&verificationtoken=" . $emailToken,
															"Verify",
															"You did not register for qpost?",
															"Don't worry! Simply ignore this email and the account registered with this email address will be deleted in 2 weeks.",
															"Contact Info",
															"Terms of Service",
															"Privacy Policy",
															"Disclaimer",
															"You don't want to receive this type of emails?",
															"Click here to change your email settings or unsubscribe."
														]);

														Util::sendMail($email, "qpost: Verify your email address", $mailContent, "Paste this link into your browser to verify your account on qpost: https://qpost.gigadrivegroup.com/account/verify-email?account=" . $id . "&verificationtoken=" . $emailToken, $displayName);

														$user = User::getUserById($id);

														$successMsg = "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)";
													} else {
														$errorMsg = "An error occurred. " . $stmt->error;
													}
													$stmt->close();
												} else {
													$errorMsg = "That username is not available anymore.";
												}
											} else {
												$errorMsg = "That email is not available anymore.";
											}
										} else {
											$errorMsg = "Invalid display name.";
										}
									} else {
										$errorMsg = "Your username may only consist of letters and numbers.";
									}
								} else {
									$errorMsg = "Your username may not be longer than 16 characters.";
								}
							} else {
								$errorMsg = "Your username must be at least 3 characters long.";
							}
						} else {
							$errorMsg = "Your name must be between 1 and 25 characters long.";
						}
					} else {
						$errorMsg = "Please enter a valid email address.";
					}
				} else {
					$errorMsg = "Please enter a valid email address.";
				}
			} else {
				$errorMsg = "Please fill all the fields.";
			}
		}

		return twig_render("pages/home/index.html.twig", [
			"forceDisableNightMode" => true,
			"errorMsg" => $errorMsg,
			"successMsg" => $successMsg
		]);
	} else {
		$user = Util::getCurrentUser();
		$mysqli = Database::Instance()->get();

		$i = 0;
		$currentUser = $user->getId();

		// query is a combination of https://stackoverflow.com/a/12915720 and https://stackoverflow.com/a/24165699
		$suggestedUsers = [];
		$stmt = $mysqli->prepare("SELECT COUNT(*)       AS mutuals, u.`id` FROM users      AS me INNER JOIN follows    AS my_friends ON my_friends.follower = me.id INNER JOIN follows    AS their_friends ON their_friends.follower = my_friends.following INNER JOIN  users 	   AS u ON u.id = their_friends.following WHERE u.emailActivated = 1 AND me.id = ? AND their_friends.following != ? AND NOT EXISTS (SELECT 1 FROM follows fu3 WHERE fu3.follower = ? AND fu3.following = their_friends.following) GROUP BY me.id, their_friends.following ORDER BY RAND() LIMIT 10");
		$stmt->bind_param("iii", $currentUser, $currentUser, $currentUser);
		if ($stmt->execute()) {
			$result = $stmt->get_result();

			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					if ($i == 5) break;

					$u = User::getUserById($row["id"]);

					if (!$u->mayView()) continue;

					$mutuals = $row["mutuals"];

					array_push($suggestedUsers, [
						"user" => $u,
						"mutuals" => $mutuals
					]);

					$i++;
				}
			}
		}
		$stmt->close();

		return twig_render("pages/homefeed.html.twig", [
			"suggestedUsers" => $suggestedUsers,
			"openRequests" => $user->getOpenFollowRequests()
		]);
	}
});