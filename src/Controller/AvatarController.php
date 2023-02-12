<?php

namespace App\Controller;

use App\Form\AvatarFormType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AvatarController extends AbstractController
{

	#[Route('/editavatar', name: 'app_edit_avatar')]
	public function editAvatar(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader)
	{
		$user = $this->getUser();
		$form = $this->createForm(AvatarFormType::class, $user)->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			try {
				$avatarOld = $user->getAvatarPath();
				$avatarNew = $form->get('avatarPath')->getData();
				$user->setAvatarPath($fileUploader->upload($avatarNew, "avatar"));
				if ($avatarOld != null)
					$fileUploader->delete($avatarOld, "avatar");
				$entityManager->beginTransaction();
				$entityManager->persist($user);
				$entityManager->flush();
				$entityManager->commit();
				$this->addFlash('success', 'Votre avatar a été modifié !');
			} catch (\Exception $e) {
				$entityManager->rollback();
				$this->addFlash('error', 'Une erreur s\'est produite, Votre avatar n\'a pas pu être modifié.');
			}

			$referer = $request->headers->get('referer');
			return $this->redirect($referer);
		}



		return $this->render('modal/_avatarForm.html.twig', ['form' => $form, 'avatar' => $user->getAvatarPath()]);
	}
}