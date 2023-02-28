<?php

namespace App\Handlers\CommentHandlers;


use App\Entity\Trick;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CommentAddHandler
{
	private EntityManagerInterface $entityManager;
	private Security $security;

	public function __construct(EntityManagerInterface $entityManager, Security $security)
	{
		$this->entityManager = $entityManager;
		$this->security = $security;
	}

	public function handle(Comment $comment, Trick $trick): void
	{
		$comment->setAuthor($this->security->getUser());
		$comment->setTrick($trick);
		$this->entityManager->persist($comment);
		$this->entityManager->flush();
	}
}