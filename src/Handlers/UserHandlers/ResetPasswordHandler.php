<?php

namespace App\Handlers\UserHandlers;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ResetPasswordHandler
{
	private EntityManagerInterface $entityManager;
	private UserPasswordHasherInterface $userPasswordHasher;


	public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
	{
		$this->entityManager = $entityManager;
		$this->userPasswordHasher = $userPasswordHasher;

	}

	public function handle(User $user, FormInterface $form): void
	{
		$user->setTokenReset(null);
		$user->setPassword(
			$this->userPasswordHasher->hashPassword(
				$user,
				$form->get('password')->getData()
			)
		);
		$this->entityManager->flush();
	}
}