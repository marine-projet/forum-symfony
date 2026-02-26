<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AppGlobalsSubscriber implements EventSubscriberInterface
{
    private Environment $twig;
    private UserRepository $userRepository;

    public function __construct(Environment $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function onControllerEvent(ControllerEvent $event): void
    {
        // Charger et injecter les données globales
        $nbMembers = $this->userRepository->count([]);

        // Ajouter la variable Twig globale
        $this->twig->addGlobal('nbMembers', $nbMembers);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onControllerEvent',
        ];
    }
}