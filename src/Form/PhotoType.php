<?php

namespace App\Form;

use App\Entity\Photo;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PhotoType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('file', FileType::class, [
			'required' => true,
			'label' => false,
			'attr' => [
				'class' => 'input-photo'
			],
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

		$builder->add('path', HiddenType::class, [
			'required' => false,
			'label' => false
		]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Photo::class
		]);
	}

}