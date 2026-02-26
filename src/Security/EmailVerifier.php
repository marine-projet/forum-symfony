<?php

namespace App\Security;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Service pour la vérification des emails des utilisateurs.
 */
class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;

    public function __construct(VerifyEmailHelperInterface $verifyEmailHelper, MailerInterface $mailer)
    {
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->mailer = $mailer;
    }

    /** 
     * Envoie un email de confirmation d'adresse email à l'utilisateur.
     *
     * @param string $verifyEmailRouteName Le nom de la route pour la vérification de l'email.
     * @param mixed $user L'utilisateur à qui envoyer l'email.
     * @param TemplatedEmail $email L'email templatisé à envoyer.
     * @return void
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getUserIdentifier(),
            ['id' => $user->getId()]
        );

        $email->context([
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAt' => $signatureComponents->getExpiresAt(),
            'username' => $user->getUsername(),
        ]);

    try {
        $this->mailer->send($email);
        error_log('Email envoyé avec succès.');
    } catch (\Exception $e) {
        // Log des erreurs
        error_log('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
    }
    }
}