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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, JWTService $jwt, SendMailService $mail): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            // On génère le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
            $user->setTokenActivation($token);


            $mail->send(
                $user->getEmail(),
                'Snowtricks - Activation de votre compte',
                'register',
                compact('user', 'token')
            );

            $this->addFlash('success', 'Votre profil a bien été créé. Un mail a été envoyé afin d\'activer votre compte');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/activation/{token}', name: 'app_activation_user', methods: ['GET'])]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {

            $payload = $jwt->getPayload($token);

            $user = $userRepository->find($payload['user_id']);

            if ($user && !$user->isActive()) {
                $user->setActive(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre compte est activé !');
                return $this->redirectToRoute('app_home');
            }
        }
        $this->addFlash('danger', 'Le lien est invalide ou a expiré');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/resendtokenactivation', name: 'app_resend_token_activation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function resendTokenActivation(JWTService $jwt, SendMailService $mail, UserRepository $userRepository): Response
    {

        $user = $this->getUser();

        // Vérifié utilisateur connecté (VOTER)
        if ($user->isActive()) {
            $this->addFlash('danger', 'Votre compte est déjà activé');
            return $this->redirectToRoute('app_home');
        }

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload = [
            'user_id' => $user->getId()
        ];

        // On génère le token
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