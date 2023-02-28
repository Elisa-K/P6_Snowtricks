<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AvatarFormType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Handlers\UserHandlers\AvatarEditHandler;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AvatarController extends AbstractController
{

	#[Route('/editavatar', name: 'app_edit_avatar', methods: ['GET', 'POST'])]
	#[isGranted('ROLE_USER')]
	public function editAvatar(Request $request, EntityManagerInterface $entityManager, AvatarEditHandler $handler): Response
	{
		/**
		 * @var User $user
		 */
		$user = $this->getUser();
		$form = $this->createForm(AvatarFormType::class, $user)->handleRequest($request);

		if ($form->isSubmitted()) {

			$entityManager->beginTransaction();
			try {
				if ($form->isValid()) {
					$handler->handle($user, $form);
					$entityManager->commit();
					$this->addFlash('success', 'Votre avatar a été modifié !');
				} else {
					$this->addFlash('danger', 'Une erreur s\'est produite, Votre avatar n\'a pas pu être modifié.');
				}

			} catch (\Exception $e) {
				$entityManager->rollback();
				$this->addFlash('danger', 'Une erreur s\'est produite, Votre avatar n\'a pas pu être modifié.');
			}

			$referer = $request->headers->get('referer');
			return $this->redirect($referer);
		}



		return $this->render('modal/_avatar_form.html.twig', ['form' => $form, 'avatar' => $user->getAvatarPath()]);
	}
}