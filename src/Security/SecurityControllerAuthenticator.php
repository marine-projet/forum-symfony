<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Authenticator personnalisé pour gérer l'authentification des utilisateurs.
 */
class SecurityControllerAuthenticator extends AbstractLoginFormAuthenticator
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Cette méthode gère l’authentification (vérifie le login et mot de passe)
     */
    public function authenticate(Request $request): Passport
    {

        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');

        if (empty($username)) {
            throw new CustomUserMessageAuthenticationException('Le nom d’utilisateur ne peut pas être vide.');
        }

        return new Passport(
            new UserBadge($username),                
            new PasswordCredentials($password)      
        );
    }

    /**
     * Définit l'URL de la page de connexion (appelée en cas de problème d'authentification ou pour afficher le formulaire)
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }

    /**
     * En cas de succès d'authentification, redirige l'utilisateur en fonction de son rôle
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }elseif (in_array('ROLE_USER', $roles, true)) {
        return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
}
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }
}