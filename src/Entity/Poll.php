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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\PollRepository")
 */
class Poll {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="array")
	 */
	private $options = [];

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $expiry;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\PollVote", mappedBy="poll", orphanRemoval=true)
	 */
	private $votes;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $winner;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\FeedEntry", inversedBy="poll", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $feedEntry;

	public function __construct() {
		$this->votes = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getOptions(): ?array {
		return $this->options;
	}

	public function setOptions(array $options): self {
		$this->options = $options;

		return $this;
	}

	public function getExpiry(): ?DateTimeInterface {
		return $this->expiry;
	}

	public function setExpiry(DateTimeInterface $expiry): self {
		$this->expiry = $expiry;

		return $this;
	}

	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	/**
	 * @return Collection|PollVote[]
	 */
	public function getVotes(): Collection {
		return $this->votes;
	}

	public function addVote(PollVote $vote): self {
		if (!$this->votes->contains($vote)) {
			$this->votes[] = $vote;
			$vote->setPoll($this);
		}

		return $this;
	}

	public function removeVote(PollVote $vote): self {
		if ($this->votes->contains($vote)) {
			$this->votes->removeElement($vote);
			// set the owning side to null (unless already changed)
			if ($vote->getPoll() === $this) {
				$vote->setPoll(null);
			}
		}

		return $this;
	}

	public function getWinner(): ?int {
		return $this->winner;
	}

	public function setWinner(?int $winner): self {
		$this->winner = $winner;

		return $this;
	}

	public function getFeedEntry(): ?FeedEntry {
		return $this->feedEntry;
	}

	public function setFeedEntry(FeedEntry $feedEntry): self {
		$this->feedEntry = $feedEntry;

		return $this;
	}
}
