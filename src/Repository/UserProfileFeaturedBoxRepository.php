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

namespace App\Repository;

use App\Entity\UserProfileFeaturedBox;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserProfileFeaturedBox|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProfileFeaturedBox|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProfileFeaturedBox[]    findAll()
 * @method UserProfileFeaturedBox[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProfileFeaturedBoxRepository extends ServiceEntityRepository {
	public function __construct(RegistryInterface $registry) {
		parent::__construct($registry, UserProfileFeaturedBox::class);
	}

	// /**
	//  * @return UserProfileFeaturedBox[] Returns an array of UserProfileFeaturedBox objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('u.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?UserProfileFeaturedBox
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
