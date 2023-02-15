<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService{

	private $mailer;

	public function __construct(MailerInterface $mailer){
		$this->mailer = $mailer;
	}

	public function send(string $to, string $subject, string $template, array $context): void{

		$email = (new TemplatedEmail())
				->from('elisa-klein@elisa-klein.fr')
				->to($to)
				->subject($subject)
				->htmlTemplate("mail/$template.html.twig")
				->context($context);

		$this->mailer->send($email);
	}
}