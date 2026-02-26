<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Repository\CommentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class NotificationSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $commentRepository;
    private $security;
    private $notificationRepository;

    public function __construct(Environment $twig, CommentRepository $commentRepository, Security $security, \App\Repository\NotificationRepository $notificationRepository)
    {
        $this->twig = $twig;
        $this->commentRepository = $commentRepository;
        $this->security = $security;
        $this->notificationRepository = $notificationRepository;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $author = $this->security->getUser(); // Utilise la nouvelle classe Security

        $hasNewComments = false;

        if ($author) {
        // Vérifie s'il reste des notifications non lues dans la base
        $unreadComments = $this->commentRepository->findBy([
            'author' => $author,
            'isRead' => false,
        ]);
        $hasNewComments = count($unreadComments) > 0;;
        }

        $this->twig->addGlobal('hasNewComments', $hasNewComments);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}