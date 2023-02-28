<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ResetPasswordFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('password', PasswordType::class, [
			'label' => 'Entrez votre nouveau mot de passe',
			'constraints' => [
				new NotBlank([
					'message' => 'Veuillez renseigner un mot de passe.',
				]),
				new Length([
					'min' => 6,
					'minMessage' => 'Votre mot de passe doit être composé de {{ limit }} caractères minimum.',
					// max length allowed by Symfony for security reasons
					'max' => 4096,
				]),
			]
		]);

	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}