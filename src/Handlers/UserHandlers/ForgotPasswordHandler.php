<?php

namespace App\Handlers\UserHandlers;

use App\Entity\User;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SendMailService;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ForgotPasswordHandler
{
	private EntityManagerInterface $entityManager;
	private ContainerBagInterface $params;
	private JWTService $jwt;
	private SendMailService $mail;


	public function __construct(EntityManagerInterface $entityManager, ContainerBagInterface $params, JWTService $jwt, SendMailService $mail)
	{
		$this->entityManager = $entityManager;
		$this->params = $params;
		$this->jwt = $jwt;
		$this->mail = $mail;
	}

	public function handle(User $user): void
	{
		$header = [
			'typ' => 'JWT',
			'alg' => 'HS256'
		];

		$payload = [
			'action' => 'reset',
			'user_id' => $user->getId()
		];

		$token = $this->jwt->generate($header, $payload, $this->params->get('app.jwtsecret'));
		$user->setTokenReset($token);
		$this->entityManager->flush();

		$this->mail->send(
			$user->getEmail(),
			'Snowtricks - Réinitialisation de votre mot de passe',
			'password_reset',
			compact('user', 'token')
		);
	}
}