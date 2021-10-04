<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Storage;

use Jupi\DropzoneJsUploaderBundle\Mapping\EntityAbstraction;
use Jupi\DropzoneJsUploaderBundle\Mapping\MappingReader;
use Jupi\DropzoneJsUploaderBundle\Mapping\EntityAnnotationReader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StorageManager
{
    private MappingReader $mappingReader;
    private Filesystem $filesystem;

    private string $tempDir;

    public function __construct(MappingReader $mappingReader)
    {
        $this->mappingReader = $mappingReader;
        $this->filesystem = new Filesystem();

        $this->tempDir = sys_get_temp_dir();
    }

    public function createTempFile(UploadedFile $uploadedFile, string $tempFileName): void
    {
        $uploadedFile->move($this->tempDir, $tempFileName);
    }

    public function mergeChunkToTempFile(UploadedFile $uploadedFile, string $tempFileName): void
    {
        $tempFilePath = $this->tempDir . \DIRECTORY_SEPARATOR . $tempFileName;
        $this->filesystem->appendToFile($tempFilePath, $uploadedFile->getContent());
    }

    public function upload(EntityAbstraction $entityAbstraction): void
    {
        if (EntityAnnotationReader::read(get_class($entityAbstraction->getEntity())) === null) {
            throw new \Exception("Error Processing Request", 1);
        }

        $mapping = $this->mappingReader->getClassRelatedMapping(get_class($entityAbstraction->getEntity()));

        $uploadDestination = $mapping['upload_destination'];
        $fileName = $entityAbstraction->getName();

        $entityAbstraction->getFile()->move($uploadDestination, $fileName);
    }

    public function resolveUri(object $entity): ?string
    {
        return $this->resolvePath($entity, 'uri_prefix');
    }

    public function resolvePath(object $entity, ?string $mappingPath = null): ?string
    {
        if (EntityAnnotationReader::read(get_class($entity)) === null) {
            return null;
        }

        $entityAbstraction = new EntityAbstraction($entity);
        $mapping = $this->mappingReader->getClassRelatedMapping(get_class($entity));

        $fileName = $entityAbstraction->getName();

        if ($fileName !== null) {
            return $mapping[$mappingPath ?? 'upload_destination'] . \DIRECTORY_SEPARATOR . $fileName;
        } else {
            return null;
        }
    }

    public function getTempDir(): string
    {
        return $this->tempDir;
    }
}
