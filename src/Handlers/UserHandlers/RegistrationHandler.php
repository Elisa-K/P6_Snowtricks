<?php

namespace App\Handlers\UserHandlers;

use App\Entity\User;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SendMailService;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationHandler
{
	private EntityManagerInterface $entityManager;
	private ContainerBagInterface $params;
	private UserPasswordHasherInterface $userPasswordHasher;
	private JWTService $jwt;
	private SendMailService $mail;


	public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $params, UserPasswordHasherInterface $userPasswordHasher, JWTService $jwt, SendMailService $mail)
	{
		$this->entityManager = $entityManager;
		$this->params = $params;
		$this->userPasswordHasher = $userPasswordHasher;
		$this->jwt = $jwt;
		$this->mail = $mail;
	}

	public function handle(User $user, FormInterface $form): void
	{
		$user->setPassword(
			$this->userPasswordHasher->hashPassword(
				$user,
				$form->get('plainPassword')->getData()
			)
		);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		$header = [
			'typ' => 'JWT',
			'alg' => 'HS256'
		];

		$payload = [
			'action' => 'activation',
			'user_id' => $user->getId()
		];

		$token = $this->jwt->generate($header, $payload, $this->params->get('app.jwtsecret'));

		$this->mail->send(
			$user->getEmail(),
			'Snowtricks - Activation de votre compte',
			'register',
			compact('user', 'token')
		);
	}
}