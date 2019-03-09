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

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserProfileFeaturedBoxRepository")
 */
class UserProfileFeaturedBox {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $title;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User")
	 */
	private $users;

	public function __construct() {
		$this->users = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle(?string $title): self {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return Collection|User[]
	 */
	public function getUsers(): Collection {
		return $this->users;
	}

	public function addUser(User $user): self {
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
		}

		return $this;
	}

	public function removeUser(User $user): self {
		if ($this->users->contains($user)) {
			$this->users->removeElement($user);
		}

		return $this;
	}
}
