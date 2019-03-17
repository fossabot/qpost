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
 * @ORM\Entity(repositoryClass="App\Repository\UserPrivacySettingsRepository")
 */
class UserPrivacySettings {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $level;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="privacy_settings", cascade={"persist", "remove"})
	 */
	private $user;

	public function getId(): ?int {
		return $this->id;
	}

	public function getLevel(): ?int {
		return $this->level;
	}

	public function setLevel(int $level): self {
		$this->level = $level;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(User $user): self {
		$this->user = $user;

		// set the owning side of the relation if necessary
		if ($this !== $user->getPrivacySettings()) {
			$user->setPrivacySettings($this);
		}

		return $this;
	}
}
