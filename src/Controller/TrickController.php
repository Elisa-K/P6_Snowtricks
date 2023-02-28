<?php

namespace App\Controller;


use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickFormType;
use App\Form\CommentFormType;
use App\Service\FileUploader;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Handlers\TrickHandlers\TrickAddHandler;
use Symfony\Component\Routing\Annotation\Route;
use App\Handlers\TrickHandlers\TrickDeleteHandler;
use App\Handlers\TrickHandlers\TrickUpdateHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Handlers\CommentHandlers\CommentAddHandler;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: 'GET')]
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->loadTricks(0, 4);
        return $this->render('home/index.html.twig', compact('tricks'));
    }

    #[Route('/loadmoretricks', methods: 'GET')]
    public function loadMoreTricks(Request $request, TrickRepository $trickRepository): JsonResponse
    {
        $start = $request->query->getInt('start');
        $limit = $request->query->getInt('limit');
        $tricks = $trickRepository->loadTricks($start, $limit);
        $lastResult = $start + $limit >= $trickRepository->countTricks();

        return $this->json([
            'html' => array_map(fn(Trick $trick): string => $this->renderView('/trick/_trick_card.html.twig', ['trick' => $trick]), $tricks),
            'lastResult' => $lastResult
        ]);
    }

    #[Route('/tricks/details/{slug}/loadmorecomments', methods: 'GET')]
    public function loadMoreComments(Trick $trick, Request $request, CommentRepository $commentRepository): JsonResponse
    {
        $start = $request->query->getInt('start');
        $limit = $request->query->getInt('limit');
        $comments = $commentRepository->loadComments($trick, $start, $limit);
        $lastResult = $start + $limit >= $commentRepository->countComments($trick);

        return $this->json([
            'html' => array_map(fn(Comment $comment): string => $this->renderView('/trick/_comment_card.html.twig', ['comment' => $comment]), $comments),
            'lastResult' => $lastResult
        ]);
    }

    #[Route('/tricks/details/{slug}', name: 'app_trick_show', methods: ['GET', 'POST'])]
    public function show(Trick $trick, Request $request, CommentAddHandler $handler): Response
    {
        $data = ['trick' => $trick];

        if ($this->isGranted('ROLE_USER_VERIFIED')) {
            $comment = new Comment();
            $form = $this->createForm(CommentFormType::class, $comment)->handleRequest($request);
            $data['commentForm'] = $form;

            if ($form->isSubmitted() && $form->isValid()) {

                $handler->handle($comment, $trick);

                $this->addFlash('success', 'Votre commentaire est publié !');
                return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
            }
        }

        return $this->render('trick/show.html.twig', $data);
    }

    #[Route('/tricks/add', name: 'app_trick_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_VERIFIED')]
    public function add(Request $request, TrickAddHandler $handler): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $handler->handle($trick, $form);

            $this->addFlash('success', 'La figure ' . $trick->getName() . ' est publiée avec succès !');
            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trick/add.html.twig', ['trickForm' => $form]);
    }

    #[Route('/tricks/edit/{slug}', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_VERIFIED')]
    public function edit(Trick $trick, Request $request, TrickUpdateHandler $handler): Response
    {
        $photos = $trick->getPhotos();
        $oldPhotos = clone $photos;
        $oldFeaturedImg = $trick->getFeaturedImage();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $handler->handle($trick, $form, $oldPhotos, $oldFeaturedImg);

            $this->addFlash('success', 'La figure ' . $trick->getName() . ' a été modifié avec succès !');
            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trick/edit.html.twig', ['trickForm' => $form, 'trick' => $trick]);
    }

    #[Route('/tricks/delete/{id}', name: 'app_trick_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER_VERIFIED')]
    public function delete(Trick $trick, Request $request, EntityManagerInterface $entityManager, TrickDeleteHandler $handler): Response
    {
        $this->denyAccessUnlessGranted('TRICK_DELETE', $trick);
        if ($this->isCsrfTokenValid('delete-' . $trick->getId(), $request->get('_token'))) {

            try {
                $entityManager->beginTransaction();
                $entityManager->remove($trick);
                $entityManager->flush();
                $entityManager->commit();

                $handler->handle($trick);

                $this->addFlash('success', 'La figure a été supprimée avec succès !');
            } catch (\Exception $e) {
                $entityManager->rollback();
                $this->addFlash('error', 'Une erreur s\'est produite, la figure n\'a pu être supprimée !');
            }
        }

        return $this->redirectToRoute('app_home');
    }

}