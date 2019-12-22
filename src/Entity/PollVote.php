<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\PollVoteRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"selected_option"})}, uniqueConstraints={@ORM\UniqueConstraint(columns={"poll_id","user_id"})})
 */
class PollVote {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\Poll", inversedBy="votes")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $poll;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $selectedOption;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	public function getId(): ?int {
		return $this->id;
	}

	public function getPoll(): ?Poll {
		return $this->poll;
	}

	public function setPoll(?Poll $poll): self {
		$this->poll = $poll;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	public function getSelectedOption(): ?int {
		return $this->selectedOption;
	}

	public function setSelectedOption(int $selectedOption): self {
		$this->selectedOption = $selectedOption;

		return $this;
	}

	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}
