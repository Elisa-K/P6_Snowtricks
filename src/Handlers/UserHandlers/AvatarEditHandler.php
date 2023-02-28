<?php

namespace App\Handlers\UserHandlers;

use App\Entity\User;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class AvatarEditHandler
{
	private EntityManagerInterface $entityManager;
	private FileUploader $fileUploader;

	public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
	{
		$this->entityManager = $entityManager;
		$this->fileUploader = $fileUploader;
	}

	public function handle(User $user, FormInterface $form): void
	{
		$avatarOld = $user->getAvatarPath();
		$avatarNew = $form->get('avatarPath')->getData();
		$user->setAvatarPath($this->fileUploader->upload($avatarNew, "avatar"));
		if ($avatarOld !== null)
			$this->fileUploader->delete($avatarOld, "avatar");

		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}
}