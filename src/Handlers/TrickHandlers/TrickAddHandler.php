<?php

namespace App\Handlers\TrickHandlers;

use App\Entity\Trick;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickAddHandler
{

	private EntityManagerInterface $entityManager;
	private FileUploader $fileUploader;
	private SluggerInterface $slugger;
	private Security $security;

	public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader, SluggerInterface $slugger, Security $security)
	{
		$this->entityManager = $entityManager;
		$this->fileUploader = $fileUploader;
		$this->slugger = $slugger;
		$this->security = $security;
	}

	public function handle(Trick $trick, FormInterface $form): void
	{
		foreach ($trick->getPhotos() as $photo) {
			if ($photo->file !== null) {
				$photo->setPath($this->fileUploader->upload($photo->file));
				continue;
			}
			$trick->removePhoto($photo);
		}

		$featuredImg = $form->get('featuredImage')->getData();
		$trick->setFeaturedImage($this->fileUploader->upload($featuredImg));

		$trick
			->setAuthor($this->security->getUser())
			->setSlug($this->slugger->slug($trick->getName())->lower());


		$this->entityManager->persist($trick);
		$this->entityManager->flush();
	}
}