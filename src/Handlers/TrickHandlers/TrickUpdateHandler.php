<?php

namespace App\Handlers\TrickHandlers;

use App\Entity\Trick;
use App\Service\FileUploader;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class TrickUpdateHandler
{

	private EntityManagerInterface $entityManager;
	private FileUploader $fileUploader;


	public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
	{
		$this->entityManager = $entityManager;
		$this->fileUploader = $fileUploader;
	}

	public function handle(Trick $trick, FormInterface $form, Collection $oldPhotos, string $oldFeaturedImg): void
	{
		foreach ($trick->getPhotos() as $photo) {
			if (!$photo->getId()) {
				$photo->setPath($this->fileUploader->upload($photo->file));
			}
		}

		foreach ($oldPhotos as $photo) {
			if (!$trick->getPhotos()->contains($photo)) {
				$this->fileUploader->delete($photo->getPath());
			}
		}

		$featuredImg = $form->get('featuredImage')->getData();
		if ($featuredImg !== null) {
			$trick->setFeaturedImage($this->fileUploader->upload($featuredImg));
			$this->fileUploader->delete($oldFeaturedImg);
		}

		$trick->setUpdatedAt(new \DateTimeImmutable());
		$this->entityManager->persist($trick);
		$this->entityManager->flush();

	}
}