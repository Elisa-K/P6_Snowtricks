<?php

namespace App\Form;

use App\Entity\Trick;
use App\Form\PhotoType;
use App\Form\VideoType;
use App\Entity\GroupTrick;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class TrickFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$object = $options['data'] ?? null;
		$isEdit = $object && $object->getId();

		$builder->add('name', TextType::class, [
			'label' => 'Nom *',
			'required' => true,
			'attr' => [
				'readonly' => $isEdit
			],
			'constraints' => [
				new NotBlank([
					'message' => 'Veuillez renseigner le nom de la figure.'
				]),
				new Length([
					'min' => 2,
					'minMessage' => 'Le nom de la figure doit être composé de {{ limit }} caractères minimum.',
					'max' => 255,
				])
			]
		]);

		$builder->add('description', TextareaType::class, [
			'label' => 'Description *',
			'constraints' => [
				new NotBlank([
					'message' => 'Veuillez renseigner la description de la figure.'
				])
			]
		]);
		$builder->add('groupTrick', EntityType::class, [
			'label' => 'Groupe *',
			'required' => true,
			'class' => GroupTrick::class,
			'query_builder' => function (EntityRepository $er) {
				return $er->createQueryBuilder('gr')->orderBy('gr.name', 'ASC');
			},
			'choice_label' => 'name',
			'placeholder' => 'Choisir un groupe'
		]);
		$builder->add('featuredImage', FileType::class, [
			'required' => !$isEdit,
			'mapped' => false,
			'label' => 'Image à la une *',
			'constraints' => [
				new File([
					'maxSize' => '2M',
					'mimeTypes' => [
						'image/jpg',
						'image/jpeg',
						'image/png'
					]
				])
			]
		]);
		$builder->add('photos', CollectionType::class, [
			'entry_type' => PhotoType::class,
			'label' => 'Photos',
			'allow_add' => true,
			'allow_delete' => true,
			'prototype' => true,
			'by_reference' => false,
			'error_bubbling' => false
		]);

		$builder->add('videos', CollectionType::class, [
			'entry_type' => VideoType::class,
			'label' => 'Vidéos',
			'allow_add' => true,
			'allow_delete' => true,
			'prototype' => true,
			'by_reference' => false,
			'error_bubbling' => false
		]);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data-class' => Trick::class
		]);
	}

}