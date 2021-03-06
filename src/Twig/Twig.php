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

namespace qpost\Twig;

use qpost\Constants\LinkedAccountService;
use Symfony\Component\Intl\Intl;
use function array_merge;
use function basename;
use function count;
use function explode;
use function filemtime;
use function glob;
use function strlen;
use function substr;
use function usort;

class Twig {
	public static function param($parameters = []): array {
		$availableLocales = [];

		$results = glob(__DIR__ . "/../../translations/messages.*.json");
		if ($results) {
			foreach ($results as $path) {
				$name = basename($path);
				$code = explode(".", substr($name, strlen("messages.")))[0];

				if ($code) {
					$name = Intl::getLocaleBundle()->getLocaleName($code, $code);
					$flag = $code;

					switch ($code) {
						case "en":
							$flag = "gb";
							break;
					}

					$availableLocales[] = [
						"name" => $name,
						"code" => $code,
						"flag" => $flag
					];
				}
			}
		}

		// load js
		$bundleName = self::loadBundleFile(__DIR__ . "/../../public/build/bundle*.js");

		// load css
		$styleBundleName = self::loadBundleFile(__DIR__ . "/../../public/build/main*.css");

		$twigGlobals = [
			"siteName" => "qpost",
			"defaultDescription" => isset($_ENV["DEFAULT_DESCRIPTION"]) ? $_ENV["DEFAULT_DESCRIPTION"] : "",
			"defaultTwitterImage" => isset($_ENV["DEFAULT_TWITTER_IMAGE"]) ? $_ENV["DEFAULT_TWITTER_IMAGE"] : "",
			"postCharacterLimit" => $_ENV["POST_CHARACTER_LIMIT"],
			"increasedPostCharacterLimit" => $_ENV["INCREASED_POST_CHARACTER_LIMIT"],
			"availableLocales" => $availableLocales,
			"bundleName" => $bundleName,
			"styleBundleName" => $styleBundleName,
			"linkedAccountServices" => LinkedAccountService::all(),
			"_POST" => isset($_POST) ? $_POST : [],
			"_GET" => isset($_GET) ? $_GET : [],
			"_COOKIE" => isset($_COOKIE) ? $_COOKIE : [],
			"_SERVER" => isset($_SERVER) ? $_SERVER : [],
			"_SESSION" => isset($_SESSION) ? $_SESSION : [],
			"_ENV" => isset($_ENV) ? $_ENV : []
		];

		return array_merge($twigGlobals, $parameters);
	}

	private static function loadBundleFile(string $pattern): ?string {
		$results = glob($pattern);
		if ($results && count($results) > 0) {
			if (count($results) > 1) {
				usort($results, function ($a, $b) {
					$aTime = filemtime($a);
					$bTime = filemtime($b);

					return $aTime === $bTime ? 0 : $aTime > $bTime ? -1 : 1;
				});
			}

			return basename($results[0]);
		}

		return null;
	}
}