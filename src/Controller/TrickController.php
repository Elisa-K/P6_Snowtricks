<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: 'GET')]
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('home/index.html.twig', compact('tricks'));
    }

    #[Route('/tricks/details/{slug}', name: 'app_trick_show', methods: ['GET', 'POST'])]
    public function show(string $slug, TrickRepository $trickRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $trick = $trickRepository->findOneBy(['slug' => $slug]);

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser());
            $comment->setTrick($trick);
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire est publié !');
            return $this->redirectToRoute('app_trick_show', ['slug' => $slug]);
        }
        return $this->render('trick/show.html.twig', ['trick' => $trick, 'commentForm' => $form]);
    }
}