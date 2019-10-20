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

namespace qpost\Controller\API;

use qpost\Entity\Notification;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

class DesktopNotificationsController extends AbstractController {
	/**
	 * @Route("/api/desktopNotifications", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function notifications(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$entityManager = $apiService->getEntityManager();

		$notificationRepository = $entityManager->getRepository(Notification::class);

		/**
		 * @var Notification[] $notifications
		 */
		$notifications = $notificationRepository->findBy([
			"user" => $user,
			"notified" => false
		], [
			"time" => "DESC"
		]);

		$results = [];

		foreach ($notifications as $notification) {
			$results[] = $apiService->serialize($notification);

			$notification->setNotified(true);
			$entityManager->persist($notification);
		}

		$entityManager->flush();

		return $apiService->json([
			"results" => $results,
			"notifications" => $notificationRepository->count([
				"user" => $user,
				"seen" => false
			]),
			"messages" => 0 // TODO
		]);
	}
}