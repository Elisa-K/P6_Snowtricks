<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class VideoURL extends Constraint
{
	public $message = 'Veuillez renseigner l\'url d\'une vidéo Youtube ou Dailymotion';
}