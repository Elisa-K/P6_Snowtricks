<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use App\Form\TrickFormType;
use App\Form\CommentFormType;
use App\Service\FileUploader;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
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
    public function loadMoreTricks(Request $request, TrickRepository $trickRepository)
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

    #[Route('/loadmorecomments/{slug}', methods: 'GET')]
    public function loadMoreComments(Trick $trick, Request $request, CommentRepository $commentRepository)
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
    public function show(Trick $trick, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = ['trick' => $trick];

        if ($this->isGranted('ROLE_USER_VERIFIED')) {
            $comment = new Comment();
            $form = $this->createForm(CommentFormType::class, $comment)->handleRequest($request);
            $data['commentForm'] = $form;
            if ($form->isSubmitted() && $form->isValid()) {
                $comment->setAuthor($this->getUser());
                $comment->setTrick($trick);
                $entityManager->persist($comment);
                $entityManager->flush();
                $this->addFlash('success', 'Votre commentaire est publié !');
                return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
            }
        }
        return $this->render('trick/show.html.twig', $data);
    }

    #[Route('/tricks/add', name: 'app_trick_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_VERIFIED')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($trick->getPhotos() as $photo) {
                if ($photo->file != null) {
                    $photo->setPath($fileUploader->upload($photo->file));
                    continue;
                }
                $trick->removePhoto($photo);
            }

            $featuredImg = $form->get('featuredImage')->getData();
            $trick->setFeaturedImage($fileUploader->upload($featuredImg));

            $trick
                ->setAuthor($this->getUser())
                ->setSlug($slugger->slug($trick->getName())->lower());


            $entityManager->persist($trick);
            $entityManager->flush();
            $this->addFlash('success', 'La figure ' . $trick->getName() . ' est publiée avec succès !');
            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
        }
        return $this->render('trick/add.html.twig', ['trickForm' => $form]);
    }

    #[Route('/tricks/edit/{slug}', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_VERIFIED')]
    public function edit(Trick $trick, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $photos = $trick->getPhotos();
        $oldPhotos = clone $photos;
        $oldFeaturedImg = $trick->getFeaturedImage();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($photos as $photo) {
                if (!$photo->getId()) {
                    $photo->setPath($fileUploader->upload($photo->file));
                }
            }

            foreach ($oldPhotos as $photo) {
                if (!$trick->getPhotos()->contains($photo)) {
                    $fileUploader->delete($photo->getPath());
                }
            }

            $featuredImg = $form->get('featuredImage')->getData();
            if ($featuredImg != null) {
                $trick->setFeaturedImage($fileUploader->upload($featuredImg));
                $fileUploader->delete($oldFeaturedImg);
            }

            $trick->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($trick);
            $entityManager->flush();

            $this->addFlash('success', 'La figure ' . $trick->getName() . ' a été modifié avec succès !');
            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);

        }

        return $this->render('trick/edit.html.twig', ['trickForm' => $form, 'trick' => $trick]);
    }

    #[Route('/tricks/delete/{id}', name: 'app_trick_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER_VERIFIED')]
    public function delete(Trick $trick, Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted('TRICK_DELETE', $trick);
        if ($this->isCsrfTokenValid('delete-' . $trick->getId(), $request->get('_token'))) {

            try {
                $entityManager->beginTransaction();
                $entityManager->remove($trick);
                $entityManager->flush();
                $entityManager->commit();

                $photos = $trick->getPhotos();
                $images = array_merge([$trick->getFeaturedImage()], $photos->map(static fn($photo) => $photo->getPath())->toArray());

                foreach ($images as $image) {
                    $fileUploader->delete($image);
                }

                $this->addFlash('success', 'La figure a été supprimée avec succès !');
            } catch (\Exception $e) {
                $entityManager->rollback();
                $this->addFlash('error', 'Une erreur s\'est produite, la figure n\'a pu être supprimée !');
            }
        }

        return $this->redirectToRoute('app_home');
    }

}