<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CategoryRepository;
use App\Repository\SujetRepository;
use App\Repository\CommentRepository;
use App\Form\SujetType;
use App\Form\CommentType;
use App\Entity\Sujet;
use App\Entity\Comment;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur pour gérer les fonctionnalités du forum.
 */
class ForumController extends AbstractController
{
    /**
     * Affiche la page principale du forum avec les catégories et les sujets.
     *
     * @Route("/forum", name="forum")
     * @param Request $request La requête HTTP.
     * @param CategoryRepository $categoryRepository Le dépôt pour accéder aux catégories.
     * @param SujetRepository $sujetRepository Le dépôt pour accéder aux sujets.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour la persistance des données.
     * @return Response La réponse HTTP contenant la vue du forum.
     */
    #[Route('/forum', name: 'forum')]
    public function show(
        Request $request,
        CategoryRepository $categoryRepository,
        SujetRepository $sujetRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $selectedCategoryId = $request->query->get('category_filter');


        $categories = $categoryRepository->findAll();

        if ($selectedCategoryId) {

            $sujets = $sujetRepository->findBy(['category' => $selectedCategoryId]);
        } else {
            $sujets = $sujetRepository->findAll();
        }

        $sujet = new Sujet();
        $form = $this->createForm(SujetType::class, $sujet);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sujet);
            $entityManager->flush();

            $this->addFlash('success', 'Sujet créé avec succès !');

            return $this->redirectToRoute('forum');
        }

        return $this->render('forum/index.html.twig', [
            'categories' => $categories,
            'sujets' => $sujets,
            'form' => $form->createView(),
            'selectedCategoryId' => $selectedCategoryId, 
        ]);
    }

    /**
     * Affiche les détails d'un sujet spécifique, y compris les commentaires associés.
     *
     * @Route("/forum/subject/{id}", name="forum_sujet_detail", requirements={"id"="\d+"})
     * @param int $id L'ID du sujet à afficher.
     * @param SujetRepository $sujetRepository Le dépôt pour accéder aux sujets.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour la persistance des données.
     * @param Request $request La requête HTTP.
     * @return Response La réponse HTTP contenant la vue du sujet et ses commentaires.
     */
    #[Route('/forum/subject/{id}', name: 'forum_sujet_detail', requirements: ['id' => '\d+'])]
    public function showSubject(
        int $id,
        SujetRepository $sujetRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {

        $sujet = $sujetRepository->find($id);
        if (!$sujet) {
            throw $this->createNotFoundException('Sujet non trouvé.');
        }

        if ($request->isMethod('POST')) {
            $text = $request->request->get('text'); 

            if (!isset($text) || empty($text)) {
                $this->addFlash('error', 'Le champ texte est obligatoire.');
            } else {
                $comment = new Comment();
                $comment->setText($text);
                $comment->setAuthor($this->getUser() ? $this->getUser()->getUserIdentifier() : 'Anonyme');
                if ($this->getUser()) {$comment->setAuthorUser($this->getUser());}
                $comment->setDate(new \DateTimeImmutable());
                $comment->setSubject($sujet);

                $entityManager->persist($comment);
                $entityManager->flush();

                // ✅ Notifier tous ceux qui ont mis ce sujet en favori
foreach ($sujet->getFavoritedBy() as $favUser) {

    // Ne pas notifier celui qui vient de commenter
    if ($this->getUser() && $favUser === $this->getUser()) {
        continue;
    }

    $notif = new Notification();
    $notif->setUser($favUser);
    $notif->setMessage('Nouveau commentaire sur votre sujet favori : ' . $sujet->getName());
    $notif->setIsRead(false);

    $notif->setSubject($sujet);
    
    $entityManager->persist($notif);
}

$entityManager->flush();

                $this->addFlash('success', 'Commentaire ajouté avec succès.');

                return $this->redirectToRoute('forum_sujet_detail', ['id' => $id]);
            }
        }

        $comments = $sujet->getComments();
        $profilePictures = [];

        return $this->render('forum/sujet_detail.html.twig', [
            'subject' => $sujet,
            'comments' => $comments,
            'profilePictures' => $profilePictures,
        ]);
    }

    /**
     * Permet à un utilisateur de modifier son propre commentaire.
     *
     * @Route("/comment/{id}/edit", name="comment_edit")
     * @param Comment $comment Le commentaire à modifier.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités pour la persistance des données.
     * @return Response La réponse HTTP contenant la vue du formulaire de modification.
     */
    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur connecté est l'auteur
        if ($this->getUser()->getUserIdentifier() !== $comment->getAuthor()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce commentaire.');
        }

        // Créez un formulaire pour modifier le commentaire
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le commentaire a été modifié avec succès.');

            return $this->redirectToRoute('forum_sujet_detail', ['id' => $comment->getSubject()->getId()]); // Redirigez vers la liste des commentaires
        }

        return $this->render('forum/comment_edit.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
            'sujet' => $comment->getSubject(),
        ]);
    }
    /**
 * @Route("/comment/delete/{id}", name="comment_delete", methods={"GET"})
 */
#[Route('/comment/delete/{id}', name: 'comment_delete', methods: ['GET'])]
public function delete(int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager)
{
    $comment = $commentRepository->find($id);

    if (!$comment) {
        throw $this->createNotFoundException('Commentaire non trouvé.');
    }

    // Supprimer le commentaire
    $entityManager->remove($comment);
    $entityManager->flush();

    // Redirection après suppression
    return $this->redirectToRoute('forum_sujet_detail', ['id' => $comment->getSubject()->getId()]);
}
    #[Route('/forum/subject/{id}/favorite', name: 'forum_sujet_favorite', requirements: ['id' => '\d+'])]
public function toggleFavorite(
    int $id,
    SujetRepository $sujetRepository,
    EntityManagerInterface $entityManager
): Response {
    $sujet = $sujetRepository->find($id);

    if (!$sujet) {
        throw $this->createNotFoundException('Sujet non trouvé.');
    }

    // Vérifiez que l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un favori.');
    }

    if ($user->getFavoriteSubjects()->contains($sujet)) {
        // Retirer des favoris
        $user->removeFavoriteSubject($sujet);
        $this->addFlash('success', 'Sujet retiré des favoris.');
    } else {
        // Ajouter aux favoris
        $user->addFavoriteSubject($sujet);
        $this->addFlash('success', 'Sujet ajouté aux favoris.');
    }

    $entityManager->flush();

    return $this->redirectToRoute('forum_sujet_detail', ['id' => $id]);
}
}

