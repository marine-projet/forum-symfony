<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour gérer la sécurité (connexion et déconnexion).
 */
class SecurityController extends AbstractController
{
    /** 
     * Gère la connexion des utilisateurs.
     *
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils Le service pour gérer l'authentification.
     * @return Response La réponse HTTP contenant la vue de connexion ou une redirection.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils)
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('home'); 
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    
    /** 
     * Gère la vérification de la connexion (interceptée par le système de sécurité).
     * 
     * @Route("/login_check", name="app_login_check")
     * 
     * @return void
     * @throws \LogicException Cette méthode peut être vide, elle est interceptée par le système de sécurité.
     *  
     * 
     */ 
    #[Route(path: '/login_check', name: 'app_login_check')]
    public function loginCheck(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by your security system.');
    }

    /** 
     * Gère la déconnexion des utilisateurs (interceptée par le système de sécurité).
     *
     * @Route("/logout", name="app_logout", methods={"GET"})
     * @return void
     * @throws \LogicException Cette méthode peut être vide, elle est interceptée par le système de sécurité.
     */
    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut être vide, elle est interceptée par la clé firewall.logout dans security.yaml.');
    }
}
