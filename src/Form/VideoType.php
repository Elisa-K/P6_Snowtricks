<?php

namespace App\Form;

use App\Entity\Video;
use App\Validator\VideoURL;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Hostname;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class VideoType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('embed', UrlType::class, [
			'label' => false,
			'help' => 'URL youtube ou dailymotion',
			'constraints' => [
				new VideoURL()
			]
		]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Video::class
		]);
	}
}