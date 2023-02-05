<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VideoURLValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
		$rxYoutube = '/^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/i';

		$rxDailyMotion = '/^(?:https?:\/\/)?(?:www\.)?dai\.?ly(motion)?(?:\.com)?\/?.*(?:video|embed)?(?:.*v=|v\/|\/)([a-z0-9]+)?$/i';


		if (!preg_match($rxYoutube, $value) && !preg_match($rxDailyMotion, $value)) {
			$this->context->buildViolation($constraint->message)
				->addViolation();
		}
	}

}