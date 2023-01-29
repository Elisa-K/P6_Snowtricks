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
        $tricks = $trickRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('home/index.html.twig', compact('tricks'));
    }

    #[Route('/tricks/details/{slug}', name: 'app_trick_show', methods: ['GET', 'POST'])]
    public function show(Trick $trick, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = ['trick' => $trick];

        if ($this->isGranted('ROLE_USER')) {
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
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photos = $form->get('photos')->getData();

            foreach ($photos as $photo) {
                $fileName = $fileUploader->upload($photo['path']);
                if (null !== $fileName) {
                    $photoEntity = new Photo();
                    $photoEntity->setPath($fileName);
                    $photoEntity->setTrick($trick);
                    $entityManager->persist($photoEntity);
                    $trick->addPhoto($photoEntity);
                }
            }

            $videos = $form->get('videos')->getData();

            foreach ($videos as $video) {
                $video->setTrick($trick);
                $entityManager->persist($video);
            }

            $featuredImg = $form->get('featuredImage')->getData();

            if ($featuredImg) {
                $fileName = $fileUploader->upload($featuredImg);
                if (null !== $fileName) {
                    $trick->setFeaturedImage($fileName);
                }
            } else {
                $trick->setFeaturedImage($trick->getPhotos()->first()->getPath());
            }

            $trick
                ->setAuthor($this->getUser())
                ->setSlug($slugger->slug($trick->getName())->lower());


            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()]);
        }
        return $this->render('trick/add.html.twig', ['trickForm' => $form]);
    }
}