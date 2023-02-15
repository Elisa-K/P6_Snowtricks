<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class VideoType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder->add('embed', UrlType::class, [
			'label' => false,
			'help' => 'URL youtube ou dailymotion',
			'constraints' => [
				new Callback([
					'callback' => static function (?string $value, ExecutionContextInterface $context) {
						$rxYoutube = '/^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/i';

						$rxDailyMotion = '/^(?:https?:\/\/)?(?:www\.)?dai\.?ly(motion)?(?:\.com)?\/?.*(?:video|embed)?(?:.*v=|v\/|\/)([a-z0-9]+)?$/i';

						if (!preg_match($rxYoutube, $value) && !preg_match($rxDailyMotion, $value)) {
							$context
								->buildViolation('Veuillez renseigner l\'url d\'une vidéo Youtube ou Dailymotion')
								->addViolation();
						}
					}
				])
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