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
 * @ORM\Entity(repositoryClass="App\Repository\UserProfileDataRepository")
 */
class UserProfileData {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	private $display_name;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $avatar;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $bio;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	private $birthday;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="profile_data", cascade={"persist", "remove"})
	 */
	private $user;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\UserProfileFeaturedBox", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $featured_box;

	public function getId(): ?int {
		return $this->id;
	}

	public function getDisplayName(): ?string {
		return $this->display_name;
	}

	public function setDisplayName(string $display_name): self {
		$this->display_name = $display_name;

		return $this;
	}

	public function getAvatar(): ?string {
		return $this->avatar;
	}

	public function setAvatar(?string $avatar): self {
		$this->avatar = $avatar;

		return $this;
	}

	public function getBio(): ?string {
		return $this->bio;
	}

	public function setBio(?string $bio): self {
		$this->bio = $bio;

		return $this;
	}

	public function getBirthday(): ?\DateTimeInterface {
		return $this->birthday;
	}

	public function setBirthday(?\DateTimeInterface $birthday): self {
		$this->birthday = $birthday;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(User $user): self {
		$this->user = $user;

		// set the owning side of the relation if necessary
		if ($this !== $user->getProfileData()) {
			$user->setProfileData($this);
		}

		return $this;
	}

	public function getFeaturedBox(): ?UserProfileFeaturedBox {
		return $this->featured_box;
	}

	public function setFeaturedBox(UserProfileFeaturedBox $featured_box): self {
		$this->featured_box = $featured_box;

		return $this;
	}
}
