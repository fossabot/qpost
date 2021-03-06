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

namespace qpost\Service\OAuth;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use qpost\Entity\LinkedAccount;
use qpost\Entity\User;
use qpost\Factory\HttpClientFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function implode;
use function is_null;
use function json_decode;
use function sprintf;
use function strtolower;
use function urlencode;

class ThirdPartyIntegration {
	/**
	 * @var Client $httpClient
	 */
	protected $httpClient;

	/**
	 * @var EntityManagerInterface $entityManager
	 */
	protected $entityManager;

	/**
	 * @var UrlGeneratorInterface $urlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var LoggerInterface $logger
	 */
	protected $logger;

	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, LoggerInterface $logger) {
		$this->httpClient = HttpClientFactory::create();
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
	}

	/**
	 * Exchanges the OAuth code with access and refresh tokens.
	 * @param string $code
	 * @return ThirdPartyIntegrationExchangeCodeResult|null
	 */
	public function exchangeCode(string $code): ?ThirdPartyIntegrationExchangeCodeResult {
		$baseURL = $this->getBaseURL();
		if (is_null($baseURL)) return null;

		$clientId = $this->getClientId();
		if (is_null($clientId)) return null;

		$clientSecret = $this->getClientSecret();
		if (is_null($clientSecret)) return null;

		$scopes = $this->getScopes();
		if (is_null($scopes)) return null;

		$response = $this->httpClient->post($baseURL . "/token", [
			"form_params" => [
				"client_id" => $clientId,
				"client_secret" => $clientSecret,
				"grant_type" => "authorization_code",
				"code" => $code,
				"redirect_uri" => $this->getRedirectURL(),
				"scope" => implode(" ", $scopes)
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();

		if (is_null($content)) return null;

		$this->logger->info("Code response", [
			"response" => $content
		]);

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["access_token"]) || !isset($data["token_type"])) return null;

		return new ThirdPartyIntegrationExchangeCodeResult(
			$data["access_token"],
			isset($data["refresh_token"]) ? $data["refresh_token"] : null,
			isset($data["expires_in"]) ? $data["expires_in"] : null,
			$clientId,
			$clientSecret
		);
	}

	/**
	 * The service's OAuth base URL.
	 * @return string|null
	 */
	public function getBaseURL(): ?string {
		return null;
	}

	/**
	 * The client ID to use with this service's OAuth API.
	 * @return string|null
	 */
	public function getClientId(): ?string {
		return $_ENV[$this->getServiceIdentifier() . "_CLIENT_ID"];
	}

	/**
	 * The string used to identify this service.
	 * @return string|null
	 */
	public function getServiceIdentifier(): ?string {
		return null;
	}

	/**
	 * The client secret to use with this service's OAuth API.
	 * @return string|null
	 */
	public function getClientSecret(): ?string {
		return $_ENV[$this->getServiceIdentifier() . "_CLIENT_SECRET"];
	}

	/**
	 * The scopes to request from this service's OAuth API.
	 * @return string[]|null
	 */
	public function getScopes(): ?array {
		return null;
	}

	/**
	 * The OAuth redirect URL to use for this service.
	 * @return string|null
	 */
	public function getRedirectURL(): ?string {
		return $this->urlGenerator->generate("qpost_thirdpartyauth_callback", ["service" => strtolower($this->getServiceIdentifier())], UrlGeneratorInterface::ABSOLUTE_URL);
	}

	/**
	 * The URL at which the user authenticates with this service.
	 * @param User $user
	 * @return string
	 */
	public function getAuthenticationURL(User $user): string {
		$baseURL = $this->getBaseURL();
		$clientId = $this->getClientId();
		$scopes = $this->getScopes();
		$redirectURL = $this->getRedirectURL();

		if (is_null($baseURL) || is_null($clientId) || is_null($redirectURL) || is_null($scopes)) return null;

		return sprintf(
			$baseURL . "/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=%s",
			$clientId,
			urlencode($redirectURL),
			urlencode(implode(" ", $scopes))
		);
	}

	/**
	 * Identifies the user of this service with the passed credentials.
	 * @param ThirdPartyIntegrationExchangeCodeResult|LinkedAccount $credentials
	 * @return ThirdPartyIntegrationIdentificationResult|null
	 */
	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		// extend in sub-classes
		return null;
	}

	/**
	 * Updates the user's identification details and saves them to the database.
	 * @param LinkedAccount $account
	 * @param ThirdPartyIntegrationIdentificationResult|null $identificationResult
	 * @throws Exception
	 */
	public function updateIdentification(LinkedAccount $account, ?ThirdPartyIntegrationIdentificationResult $identificationResult = null): void {
		if (is_null($identificationResult)) {
			$newAccount = $this->refreshToken($account);

			if (is_null($newAccount)) {
				$this->entityManager->remove($account);
				$this->entityManager->flush();
				return;
			} else {
				$account = $newAccount;
			}

			$identificationResult = $this->identify($account);
		}

		if (is_null($identificationResult)) {
			$this->entityManager->remove($account);
			$this->entityManager->flush();
			return;
		}

		$account->setLinkedUserId($identificationResult->getId())
			->setLinkedUserName($identificationResult->getUsername())
			->setLinkedUserAvatar($identificationResult->getAvatar())
			->setLastUpdate(new DateTime("now"));

		$this->entityManager->persist($account);
		$this->entityManager->flush();
	}

	/**
	 * Refreshes the access token of a {@link LinkedAccount} that is using this service.
	 * @param LinkedAccount $account
	 * @return LinkedAccount|null
	 * @throws Exception
	 */
	public function refreshToken(LinkedAccount $account): ?LinkedAccount {
		if (!$account->isExpired()) return $account;

		if ($account->getService() !== $this->getServiceIdentifier()) return $account;

		$refreshToken = $account->getRefreshToken();
		if (is_null($refreshToken)) return null;

		$baseURL = $this->getBaseURL();
		if (is_null($baseURL)) return null;

		$clientId = $account->getClientId();
		if (is_null($clientId)) return null;

		$clientSecret = $account->getClientSecret();
		if (is_null($clientSecret)) return null;

		$response = $this->httpClient->post($baseURL . "/token", [
			"form_params" => [
				"client_id" => $clientId,
				"client_secret" => $clientSecret,
				"grant_type" => "refresh_token",
				"refresh_token" => $refreshToken
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["access_token"]) || !isset($data["refresh_token"]) || !isset($data["token_type"]) || !isset($data["expires_in"])) return null;

		$expiry = new DateTime("now");
		$expiry->add(new DateInterval("PT" . $data["expires_in"] . "S"));

		$this->entityManager->persist(
			$account->setAccessToken($data["access_token"])
				->setRefreshToken($data["refresh_token"])
				->setExpiry($expiry)
		);

		$this->entityManager->flush();

		return $account;
	}
}