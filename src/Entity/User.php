<?php
/**
 * Copyright (C) 2019 Gigadrive Group - All rights reserved.
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

namespace qpost\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=16, unique=true)
	 */
	private $username;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 */
	private $password;

	/**
	 * @ORM\Column(type="string", length=50, unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $register_date;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $email_activated;

	/**
	 * @ORM\Column(type="string", length=7, nullable=true)
	 */
	private $email_activation_token;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $verified;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $last_username_change;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\UserProfileData", inversedBy="user", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $profile_data;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\UserPrivacySettings", inversedBy="user", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $privacy_settings;

	public function getId(): ?int {
		return $this->id;
	}

	public function getUsername(): ?string {
		return $this->username;
	}

	public function setUsername(string $username): self {
		$this->username = $username;

		return $this;
	}

	public function getPassword(): ?string {
		return $this->password;
	}

	public function setPassword(?string $password): self {
		$this->password = $password;

		return $this;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail(string $email): self {
		$this->email = $email;

		return $this;
	}

	public function getRegisterDate(): ?\DateTimeInterface {
		return $this->register_date;
	}

	public function setRegisterDate(\DateTimeInterface $register_date): self {
		$this->register_date = $register_date;

		return $this;
	}

	public function getEmailActivated(): ?bool {
		return $this->email_activated;
	}

	public function setEmailActivated(bool $email_activated): self {
		$this->email_activated = $email_activated;

		return $this;
	}

	public function getEmailActivationToken(): ?string {
		return $this->email_activation_token;
	}

	public function setEmailActivationToken(?string $email_activation_token): self {
		$this->email_activation_token = $email_activation_token;

		return $this;
	}

	public function getVerified(): ?bool {
		return $this->verified;
	}

	public function setVerified(bool $verified): self {
		$this->verified = $verified;

		return $this;
	}

	public function getLastUsernameChange(): ?\DateTimeInterface {
		return $this->last_username_change;
	}

	public function setLastUsernameChange(?\DateTimeInterface $last_username_change): self {
		$this->last_username_change = $last_username_change;

		return $this;
	}

	public function getProfileData(): ?UserProfileData {
		return $this->profile_data;
	}

	public function setProfileData(UserProfileData $profile_data): self {
		$this->profile_data = $profile_data;

		return $this;
	}

	public function getPrivacySettings(): ?UserPrivacySettings {
		return $this->privacy_settings;
	}

	public function setPrivacySettings(UserPrivacySettings $privacy_settings): self {
		$this->privacy_settings = $privacy_settings;

		return $this;
	}
}
