<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Handlers\UserHandlers\ResetPasswordHandler;
use App\Handlers\UserHandlers\ForgotPasswordHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/signin', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/forgottenpassword', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgottenPassword(Request $request, UserRepository $userRepository, ForgotPasswordHandler $handler): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);
            if ($user) {

                $handler->handle($user);

                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe.');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('danger', 'Email inexistant !');
        }

        return $this->render('security/forgot_password.html.twig', ['form' => $form]);
    }

    #[Route(path: '/resetpassword/{tokenReset}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(User $user, Request $request, JWTService $jwt, ResetPasswordHandler $handler): Response
    {
        $tokenReset = $user->getTokenReset();
        if ($jwt->isValid($tokenReset, 'reset', $this->getParameter('app.jwtsecret'))) {

            $form = $this->createForm(ResetPasswordFormType::class)->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $handler->handle($user, $form);

                $this->addFlash('success', 'Mot de passe modifié avec succès.');
                return $this->redirectToRoute('app_login');
            }
            return $this->render('security/reset_password.html.twig', ['form' => $form]);
        }
        $this->addFlash('danger', 'Le lien est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

}