<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sujet;
use App\Entity\Comment;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Vérifie si l'utilisateur est authentifié
        $isUserLoggedIn = $this->isGranted('IS_AUTHENTICATED_FULLY');

        $dernieresNouveautes = $entityManager->getRepository(Sujet::class)
            ->findBy([], ['createdAt' => 'DESC'], 2); // Les 2 derniers sujets

        $topDiscussed = $entityManager->getRepository(Sujet::class)->findTopDiscussed(5); // 5 les plus discutés


        // Passer les données à la vue
        return $this->render('home/index.html.twig', [
            'user' => $user,
            'isUserLoggedIn' => $isUserLoggedIn,
            'dernieresNouveautes' => $dernieresNouveautes,
            'topDiscussed' => $topDiscussed,

        ]);
    }
}