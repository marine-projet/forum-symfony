<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Sujet;
use App\Entity\Comment;
use App\Form\SujetType;
use App\Form\CategoryType;
use App\Form\UserType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use App\Repository\SujetRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Contrôleur pour gérer toutes les opérations administratives.
 * 
 * Ce contrôleur donne la possibilité de gérer des entités telles que 
 * les membres, catégories, sujets et commentaires via l'interface administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    /**
     * Affiche un tableau de bord avec les statistiques globales.
     *
     * @Route("", name="dashboard")
     *
     * @param UserRepository $userRepository          Le repository de l'entité User.
     * @param CategoryRepository $categoryRepository Les catégories disponibles.
     * @param SujetRepository $sujetRepository       Les sujets disponibles.
     * @param CommentRepository $commentRepository   Les commentaires enregistrés.
     *
     * @return Response Affiche le tableau de bord avec des statistiques.
     */
    // -------------------- DASHBOARD --------------------
    #[Route('', name: 'dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        CategoryRepository $categoryRepository,
        SujetRepository $sujetRepository,
        CommentRepository $commentRepository
    ): Response {
        $stats = [
            'users'      => $userRepository->count([]),
            'categories' => $categoryRepository->count([]),
            'sujets'     => $sujetRepository->count([]),
            'comments'   => $commentRepository->count([]),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * Affiche une liste des membres (utilisateurs) enregistrés.
     *
     * @Route("/members", name="members")
     * 
     * @param UserRepository $userRepository Repository pour accéder aux utilisateurs.
     *
     * @return Response La vue affichant la liste des membres.
     */
    #[Route('/members', name: 'members')]
    public function members(UserRepository $userRepository): Response
    {
        $members = $userRepository->findBy([], ['id' => 'DESC']);
        return $this->render('admin/members.html.twig', [
            'members' => $members,
        ]);
    }
    /**
     * Crée un nouveau membre.
     *
     * @Route("/members/new", name="members_new")
     * 
     * @param Request $request Les données de la requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour effectuer les opérations en base.
     * @param UserPasswordHasherInterface $passwordHasher Pour hasher le mot de passe utilisateur.
     * 
     * @return Response La vue du formulaire ou redirige vers la liste des membres en cas de succès.
     */
    #[Route('/members/new', name: 'members_new')]
    public function membersNew(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);   // 👈 utilise ton FormType
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le MDp en clair depuis le champ non mappé
            $password = $form->get('password')->getData();

            // Sécurité : double-check si jamais non soumis
            if ($password === null || $password === '') {
                $this->addFlash('error', 'Le mot de passe est requis.');
            } else {
                // Hash + set sur la propriété mappée "password"
                $hashed = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashed);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Membre créé.');
                return $this->redirectToRoute('admin_members');
            }
        }

        return $this->render('admin/form/form_member.html.twig', [
            'form' => $form->createView(),
            'members' => 'Nouveau membre',
        ]);
    }

    /**
     * Permet de modifier un membre existant.
     *
     * @Route("/members/{id}/edit", name="members_edit")
     * 
     * @param User $user L'utilisateur à modifier.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour sauvegarder les modifications.
     * 
     * @return Response Retourne la vue du formulaire ou redirige vers la liste des membres.
     */
    #[Route('/members/{id}/edit', name: 'members_edit')]
    public function membersEdit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($user)
            ->add('username')
            ->add('email')
            ->add('password')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Membre modifié.');
            return $this->redirectToRoute('admin_members');
        }

        return $this->render('admin/form/form_member.html.twig', [
            'form' => $form->createView(),
            'title' => sprintf('Modifier membre #%d', $user->getId()),
        ]);
    }

    /**
     * Supprime un membre existant.
     *
     * @Route("/members/{id}/delete", name="members_delete", methods={"POST"})
     * 
     * @param User $user L'utilisateur à supprimer.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour effectuer la suppression.
     * 
     * @return Response Redirige vers la liste des membres après suppression.
     */
    #[Route('/members/{id}/delete', name: 'members_delete', methods: ['POST'])]
    public function membersDelete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Membre supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_members');
    }

    /**
     * Affiche une liste des catégories enregistrés.
     *
     * @Route("/categories', name="categorie")
     * 
     * @param CategoryRepository $categoryRepository Repository pour accéder aux catégories.
     *
     * @return Response La vue affichant la liste des catégories.
     */
    #[Route('/categories', name: 'categories')]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findBy([], ['id' => 'ASC']);
        return $this->render('admin/categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    /** Crée une nouvelle catégorie.
     *
     * @Route("/categories/new", name="categories_new")
     * 
     * @param Request $request Les données de la requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour effectuer les opérations en base.
     * 
     * @return Response La vue du formulaire ou redirige vers la liste des catégories en cas de succès.
     */
    #[Route('/categories/new', name: 'categories_new')]
    public function categoriesNew(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie créée.');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/form/form_category.html.twig', [
            'form' => $form->createView(),
            'name' => 'Nouvelle catégorie',
        ]);
    }

    /**
     * Permet de modifier une catégorie existante.
     *
     * @Route("/categories/{id}/edit", name="categories_edit")
     * 
     * @param Category $category La catégorie à modifier.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour sauvegarder les modifications.
     * 
     * @return Response Retourne la vue du formulaire ou redirige vers la liste des catégories.
     */
    #[Route('/categories/{id}/edit', name: 'categories_edit')]
    public function categoriesEdit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($category)
            ->add('name')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Catégorie modifiée.');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/form/form_category.html.twig', [
            'form' => $form->createView(),
            'name' => sprintf('Modifier catégorie #%d', $category->getId()),
        ]);
    }

    /**
     * Supprime une catégorie existante.
     *
     * @Route("/categories/{id}/delete", name="categories_delete", methods={"POST"})
     * 
     * @param Category $category La catégorie à supprimer.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour effectuer la suppression.
     * 
     * @return Response Redirige vers la liste des catégories après suppression.
     */
    #[Route('/categories/{id}/delete', name: 'categories_delete', methods: ['POST'])]
    public function categoriesDelete(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_category_'.$category->getId(), $request->request->get('_token'))) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie supprimée.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_categories');
    }

    /**
     * Affiche une liste des sujets.
     *
     * @Route("/sujets", name="sujets")
     * 
     * @param SujetRepository $sujetRepository Repository pour accéder aux sujets.
     *
     * @return Response La vue affichant la liste des sujets.
     */
    #[Route('/sujets', name: 'sujets')]
    public function sujets(SujetRepository $sujetRepository): Response
    {
        $sujets = $sujetRepository->findBy([], ['id' => 'DESC']);
        return $this->render('admin/sujets.html.twig', [
            'sujets' => $sujets,
        ]);
    }

    /** Crée un nouveau sujet.
     *
     * @Route("/sujets/new", name="sujets_new")
     * 
     * @param Request $request Les données de la requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour effectuer les opérations en base.
     * 
     * @return Response La vue du formulaire ou redirige vers la liste des sujets en cas de succès.
     */
    #[Route('/sujets/new', name: 'sujets_new')]
    public function sujetsNew(Request $request, EntityManagerInterface $em): Response
    {
        $sujet = new Sujet();
        $form = $this->createForm(SujetType::class, $sujet);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sujet);
            $em->flush();
            $this->addFlash('success', 'Sujet créé.');
            return $this->redirectToRoute('admin_sujets');
        }

        return $this->render('admin/form/form_sujet.html.twig', [
            'form' => $form->createView(),
            'name' => 'Nouveau sujet',
        ]);
    }

    /**
     * Permet de modifier un sujet existant.
     *
     * @Route("/sujets/{id}/edit", name="sujets_edit")
     * 
     * @param Sujet $sujet Le sujet à modifier.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour sauvegarder les modifications.
     * 
     * @return Response Retourne la vue du formulaire ou redirige vers la liste des sujets.
     */
    #[Route('/sujets/{id}/edit', name: 'sujets_edit')]
    public function sujetsEdit(Sujet $sujet, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($sujet)
        ->add('name') // champ texte du Sujet (adapte si ton champ s'appelle autrement)
        ->add('category', EntityType::class, [
            'class' => Category::class,       // ✅ pas 'name'
            'choice_label' => 'name',         // adapte si Category a 'title'/'label' etc.
            'placeholder' => '— Choisir une catégorie —',
            'required' => true,               // passe à false si relation nullable
        ])
    ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Sujet modifié.');
            return $this->redirectToRoute('admin_sujets');
        }

        return $this->render('admin/form/form_sujet.html.twig', [
            'form' => $form->createView(),
            'name' => sprintf('Modifier sujet #%d', $sujet->getId()),
        ]);
    }

    /**
     * Supprime un sujet existant.
     *
     * @Route("/sujets/{id}/delete", name="sujets_delete", methods={"POST"})
     * 
     * @param Sujet $sujet Le sujet à supprimer.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour effectuer la suppression.
     * 
     * @return Response Redirige vers la liste des sujets après suppression.
     */
    #[Route('/sujets/{id}/delete', name: 'sujets_delete', methods: ['POST'])]
    public function sujetsDelete(Sujet $sujet, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_sujet_'.$sujet->getId(), $request->request->get('_token'))) {
            $em->remove($sujet);
            $em->flush();
            $this->addFlash('success', 'Sujet supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_sujets');
    }

    /**
     * Affiche une liste des commentaires.
     *
     * @Route("/comments", name="comments")
     * 
     * @param CommentRepository $commentRepository Repository pour accéder aux commentaires.
     * @param SujetRepository $sujetRepository Repository pour accéder aux sujets.
     *
     * @return Response La vue affichant la liste des commentaires.
     */

    #[Route('/comments', name: 'comments')]
    public function comments(CommentRepository $commentRepository, SujetRepository $sujetRepository): Response
    {

        $sujets = $sujetRepository->findAll();
        $comments = $commentRepository->findBy([], ['date' => 'DESC']); // ou ['id' => 'DESC']
        return $this->render('admin/comments.html.twig', [
            'comments' => $comments,
            'sujets' => $sujets,
        ]);
    }

    /**
     * Permet de modifier un commentaire existant.
     *
     * @Route("/comments/{id}/edit", name="comments_edit")
     * 
     * @param Comment $comment Le commentaire à modifier.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour sauvegarder les modifications.
     * 
     * @return Response Retourne la vue du formulaire ou redirige vers la liste des commentaires.
     */
    #[Route('/comments/{id}/edit', name: 'comments_edit')]
    public function commentsEdit(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($comment)
        ->add('text')
        ->add('authorUser', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'username', 
            'placeholder' => '— Aucun —',
            'required' => false,
        ])
        ->add('subject', EntityType::class, [
            'class' => Sujet::class,
            'choice_label' => 'name', 
            'placeholder' => '— Choisir un sujet —',
            'required' => true,
        ])
        ->add('date', null, [
            'widget' => 'single_text',
            'required' => true,
        ])
        ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Commentaire modifié.');
            return $this->redirectToRoute('admin_comments');
        }

        return $this->render('admin/form/form_comment.html.twig', [
            'form' => $form->createView(),
            'name' => sprintf('Modifier commentaire #%d', $comment->getId()),
        ]);
    }

    /**
     * Supprime un commentaire existant.
     *
     * @Route("/comments/{id}/delete", name="comments_delete", methods={"POST"})
     * @param Comment $comment Le commentaire à supprimer.
     * @param Request $request Les données de requête HTTP.
     * @param EntityManagerInterface $em L'EntityManager pour effectuer la suppression.
     * @return Response Redirige vers la liste des commentaires après suppression.
     */
    #[Route('/comments/{id}/delete', name: 'comments_delete', methods: ['POST'])]
    public function commentsDelete(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_comment_'.$comment->getId(), $request->request->get('_token'))) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        return $this->redirectToRoute('admin_comments');
    }
}
