<?php

namespace App\Form;

use App\Entity\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
// use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PhotoType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('path', FileType::class, [
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
	}
}