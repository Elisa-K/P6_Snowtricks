<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
	private string $targetDirectory;
	private string $targetDirectoryAvatar;
	private SluggerInterface $slugger;

	public function __construct($targetDirectory, $targetDirectoryAvatar, SluggerInterface $slugger)
	{
		$this->targetDirectory = $targetDirectory;
		$this->targetDirectoryAvatar = $targetDirectoryAvatar;
		$this->slugger = $slugger;
	}

	public function upload(UploadedFile $file, ?string $type = null)
	{
		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFilename = $this->slugger->slug($originalFilename);
		$fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
		try {
			$file->move($this->getTargetDirectory($type), $fileName);
		} catch (FileException $e) {
			return null;
		}
		return $fileName;
	}

	public function delete(string $path, ?string $type = null): void
	{
		$fileSystem = new Filesystem();
		$fileSystem->remove($this->getTargetDirectory($type) . '/' . $path);
	}

	public function getTargetDirectory(?string $type = null): ?string
	{
		if ($type === 'avatar') {
			return $this->targetDirectoryAvatar;
		}
		return $this->targetDirectory;
	}

}