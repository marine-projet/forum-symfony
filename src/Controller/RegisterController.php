<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Security\EmailVerifier;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Contrôleur pour gérer l'inscription des utilisateurs.
 */
class RegisterController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /** 
     * Gère l'inscription des nouveaux utilisateurs.
     *
     * @param Request $request La requête HTTP.
     * @param UserPasswordHasherInterface $userPasswordHasher Le service de hachage de mot de passe.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour la persistance des données.
     * @return Response La réponse HTTP contenant la vue d'inscription ou une redirection.
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setRoles(['ROLE_USER']);
    
            $emailVerificationToken = Uuid::v4()->toRfc4122(); 
            $user->setEmailVerificationToken($emailVerificationToken);
        
            $entityManager->persist($user);
            $entityManager->flush();

            $emailVerificationUrl = $this->generateUrl(
            'app_verify_email', // Nom de la route
            ['token' => $emailVerificationToken], // Paramètres de la route
            UrlGeneratorInterface::ABSOLUTE_URL // URL absolue
);

            $htmlEmailVerificationUrl = htmlspecialchars($emailVerificationUrl);

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('contact.marine1991@gmail.com', 'Mail Bot'))
                    ->to((string) $user->getEmail())
                    ->subject('Merci de confirmer votre mail')
                    ->html(
                        '<h1>Bienvenue sur notre application !</h1>' .
                        '<p>Merci de vous être inscrit. Veuillez confirmer votre adresse email en cliquant sur le lien suivant :</p>' .
                        '<a href="' . $htmlEmailVerificationUrl . '">Confirmer mon adresse</a>'
                    )
            );
        

            $this->addFlash('success', 'Un email de confirmation a été envoyé. Veuillez vérifier votre boîte de réception.');
        
            return $this->redirectToRoute('app_register_confirmation');
        }

        return $this->render('security/inscription.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    
    /** 
     * Vérifie l'adresse email de l'utilisateur à l'aide du token fourni.
     * 
     * 
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour la persistance des données.
     * @param UserAuthenticatorInterface $userAuthenticator Le service d'authentification des utilisateurs.
     * @param FormLoginAuthenticator $authenticator L'authentificateur de connexion par formulaire.
     * @return Response La réponse HTTP après la vérification.
     */
    #[Route('/verify-email', name: 'app_verify_email')]
public function verifyUserEmail(
    Request $request,
    EntityManagerInterface $entityManager,
    UserAuthenticatorInterface $userAuthenticator,
    ?FormLoginAuthenticator $authenticator = null // Nullable avec valeur par défaut
): Response {
    $token = $request->query->get('token');

    if (!$token) {
        throw $this->createNotFoundException('Token non fourni pour la vérification.');
    }

    $user = $entityManager->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]);

    if (!$user) {
        throw $this->createNotFoundException('Aucun utilisateur trouvé pour ce token.');
    }

    if (!$user->isVerified()) {
        $user->setIsVerified(true);
        $user->setIsActive(true);
        $user->setEmailVerificationToken(null);
        $entityManager->flush();
    }

    try {
        if ($authenticator) {
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        // Effectuer l'authentification d'une autre manière si l'authenticator est null
    } catch (\Symfony\Component\Security\Core\Exception\AuthenticationException $e) {
        $this->addFlash('error', 'Une erreur est survenue lors de l\'authentification automatique.');
        return $this->redirectToRoute('app_login');
    }

    $this->addFlash('success', 'Votre email a été confirmé avec succès et vous êtes maintenant connecté.');
    return $this->redirectToRoute('home');
}

    /** 
     * Affiche une page de confirmation après l'inscription.
     *
     * @Route("/register/confirmation", name="app_register_confirmation")
     * @return Response La réponse HTTP contenant la vue de confirmation.
     */
    #[Route('/register/confirmation', name: 'app_register_confirmation')]

    public function confirmEmailNotification(): Response
    {
        return $this->render('security/confirmation_email.html.twig');
    }
}