<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
	private string $targetDirectory;
	private SluggerInterface $slugger;

	public function __construct($targetDirectory, SluggerInterface $slugger)
	{
		$this->targetDirectory = $targetDirectory;
		$this->slugger = $slugger;
	}

	public function upload(UploadedFile $file)
	{
		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFilename = $this->slugger->slug($originalFilename);
		$fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
		try {
			$file->move($this->getTargetDirectory(), $fileName);
		} catch (FileException $e) {
			return null;
		}
		return $fileName;
	}

	public function delete(string $path): void
	{
		$fileSystem = new Filesystem();
		$fileSystem->remove($this->getTargetDirectory() . '/' . $path);
	}

	public function getTargetDirectory(): ?string
	{
		return $this->targetDirectory;
	}

}