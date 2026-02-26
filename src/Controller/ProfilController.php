<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProfilePictureType;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Contrôleur pour gérer le profil utilisateur.
 */
class ProfilController extends AbstractController
{
    /**
     * Affiche la page de profil de l'utilisateur avec ses commentaires.
     *
     * @Route("/profil", name="profil")
     * @param CommentRepository $commentRepository Le dépôt pour accéder aux commentaires.
     * @return Response La réponse HTTP contenant la vue du profil.
     */
    #[Route('/profil', name: 'profil')]
    public function index(CommentRepository $commentRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $comments = $commentRepository->findBy(
            ['authorUser' => $user],
            ['date' => 'DESC']
        );

        // Accède aux sujets favoris via la relation `favoriteSubjects`
        $favoriteSubjects = $user->getFavoriteSubjects();

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'comments' => $comments,
            'favoriteSubjects' => $favoriteSubjects,
        ]);
    }
    /**
     * Permet à un utilisateur de modifier son profil.
     *
     * @Route("/profil/edit", name="profil_edit")
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour la persistance des données.
     * @return Response La réponse HTTP contenant la vue du formulaire de modification du profil.
     */
    #[Route('/profil/edit', name: 'profil_edit')]
    public function editProfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Créez un formulaire pour modifier le profil utilisateur
        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('profil');
        }

        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/profile/upload', name: 'profile_upload')]
    public function uploadProfilePicture(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $user = $this->getUser(); // Récupération de l'utilisateur connecté

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $form = $this->createForm(ProfilePictureType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('profilePicture')->getData();

            if ($uploadedFile) {
                // Générer un nom unique pour l'image
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                // Déplacer le fichier dans le répertoire public/uploads
                $uploadedFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                // Mettre à jour l'utilisateur avec le chemin de la photo
                $user->setProfilePicture($newFilename);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre photo de profil a été mise à jour.');

                return $this->redirectToRoute('profil'); // Rediriger vers une page de profil
            }
        }

        return $this->render('profil/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete-picture', name: 'profile_picture_delete', methods: ['POST'])]
public function deleteProfilePicture(EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser(); // Récupération de l'utilisateur connecté

    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour effectuer cette action.');
        return $this->redirectToRoute('profile');
    }

    // Supprime l'ancienne photo du serveur
    if ($user->getProfilePicture()) {
        $picturePath = $this->getParameter('uploads_directory') . '/' . $user->getProfilePicture();
        if (file_exists($picturePath)) {
            unlink($picturePath); // Supprime le fichier du serveur
        }
    }

    // Remplace la photo par la valeur par défaut
    $user->setProfilePicture(null); // Null signifie "aucune image"

    $entityManager->persist($user);
    $entityManager->flush();

    $this->addFlash('success', 'Votre photo de profil a été supprimée.');
    return $this->redirectToRoute('profil');
}
}
