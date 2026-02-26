<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    /**
     * Route pour la page des mentions légales.
     */
    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions_legales.html.twig', [
            'pageTitle' => 'Mentions Légales',
        ]);
    }

    /**
     * Route pour la politique de confidentialité.
     */
    #[Route('/politique-confidentialite', name: 'app_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('legal/politique_confidentialite.html.twig', [
            'pageTitle' => 'Politique de Confidentialité',
        ]);
    }

    /**
     * Route pour les CGU.
     */
    #[Route('/cgu', name: 'app_cgu')]
    public function cgu(): Response
    {
        return $this->render('legal/cgu.html.twig', [
            'pageTitle' => 'Conditions Générales d\'Utilisation',
        ]);
    }

    /**
     * Route pour la page de contact.
     */
    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('legal/contact.html.twig', [
            'pageTitle' => 'Contact',
        ]);
    }
}