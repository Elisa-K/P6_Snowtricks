<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\SendMailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Handlers\UserHandlers\RegistrationHandler;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, RegistrationHandler $handler): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $handler->handle($user, $form);

            $this->addFlash('success', 'Votre profil a bien été créé. Un mail a été envoyé afin d\'activer votre compte');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/activation/{token}', name: 'app_activation_user', methods: ['GET'])]
    public function verifyUser(string $token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        if ($jwt->isValid($token, 'activation', $this->getParameter('app.jwtsecret'))) {

            $payload = $jwt->getPayload($token);

            $user = $userRepository->find($payload['user_id']);

            if ($user && !$user->isActive()) {
                $user->setActive(true);
                $user->setRoles(['ROLE_USER_VERIFIED']);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre compte est activé ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            }
        }
        $this->addFlash('danger', 'Le lien est invalide ou a expiré');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/resendtokenactivation', name: 'app_resend_token_activation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function resendTokenActivation(JWTService $jwt, SendMailService $mail, UserRepository $userRepository): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if ($user->isActive()) {
            $this->addFlash('danger', 'Votre compte est déjà activé');
            return $this->redirectToRoute('app_home');
        }

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload = [
            'action' => 'activation',
            'user_id' => $user->getId()
        ];


        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        $mail->send(
            $user->getEmail(),
            'Snowtricks - Activation de votre compte',
            'register',
            compact('user', 'token')
        );

        $this->addFlash('success', 'Un mail a été envoyé afin d\'activer votre compte');
        return $this->redirectToRoute('app_home');
    }
}