<?php

namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class AvatarFormType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('avatarPath', FileType::class, [
			'required' => true,
			'label' => false,
			'mapped' => false,
			'constraints' => [
				new Image([
					'maxSize' => '2M',
					'mimeTypes' => [
						'image/jpg',
						'image/jpeg',
						'image/png'
					]
				])
			]
		]);
	}
}