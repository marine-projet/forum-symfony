<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications', methods: ['GET'])]
    public function notifications(NotificationRepository $notificationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $notifications = $notificationRepository->findBy(
            ['user' => $user],
            ['id' => 'DESC']
        );

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/notifications/{id}/mark-as-read', name: 'notification_mark_read', methods: ['POST'])]
    public function markAsRead(Notification $notification, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        // Ownership check
        if ($notification->getUser() !== $user) {
            return $this->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $notification->setIsRead(true);
        $em->flush();

        return $this->json(['success' => true]);
    }
    #[Route('/notifications/{id}/open', name: 'notification_open', methods: ['GET'])]
public function open(Notification $notification, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');

    if ($notification->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
    }

    $notification->setIsRead(true);
    $em->flush();

    $subject = $notification->getSubject();
    if (!$subject) {
        return $this->redirectToRoute('notifications');
    }

    return $this->redirectToRoute('forum_sujet_detail', ['id' => $subject->getId()]);
}
}