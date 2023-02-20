<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/signin', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/forgottenpassword', name: 'app_forgot_password')]
    public function forgottenPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, JWTService $jwt, SendMailService $mail): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);
            if ($user) {
                $header = [
                    'typ' => 'JWT',
                    'alg' => 'HS256'
                ];

                $payload = [
                    'action' => 'reset',
                    'user_id' => $user->getId()
                ];

                $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
                $user->setTokenReset($token);
                $entityManager->persist($user);
                $entityManager->flush();

                $mail->send(
                    $user->getEmail(),
                    'Snowtricks - Réinitialisation de votre mot de passe',
                    'password_reset',
                    compact('user', 'token')
                );

                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe.');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('danger', 'Email inexistant !');
        }

        return $this->render('security/forgot_password.html.twig', ['form' => $form]);
    }

    #[Route(path: '/resetpassword/{tokenReset}', name: 'app_reset_password')]
    public function resetPassword($tokenReset, UserRepository $userRepository, Request $request, JWTService $jwt, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['tokenReset' => $tokenReset]);
        if ($user && $jwt->isValid($tokenReset) && !$jwt->isExpired($tokenReset) && $jwt->checkAction($tokenReset, 'reset') && $jwt->check($tokenReset, $this->getParameter('app.jwtsecret'))) {
            $form = $this->createForm(ResetPasswordFormType::class)->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setTokenReset('');
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe modifié avec succès.');
                return $this->redirectToRoute('app_login');
            }
            return $this->render('security/reset_password.html.twig', ['form' => $form]);
        }
        $this->addFlash('danger', 'Le lien est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

}