<?php

namespace App\Handlers\TrickHandlers;

use App\Entity\Trick;
use App\Service\FileUploader;

class TrickDeleteHandler
{
	private FileUploader $fileUploader;


	public function __construct(FileUploader $fileUploader)
	{

		$this->fileUploader = $fileUploader;
	}

	public function handle(Trick $trick): void
	{
		$photos = $trick->getPhotos();
		$images = array_merge([$trick->getFeaturedImage()], $photos->map(static fn($photo) => $photo->getPath())->toArray());

		foreach ($images as $image) {
			$this->fileUploader->delete($image);
		}

	}
}