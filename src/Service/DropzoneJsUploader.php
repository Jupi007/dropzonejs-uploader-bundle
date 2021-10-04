<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Jupi\DropzoneJsUploaderBundle\Mapping\EntityAbstraction;
use Jupi\DropzoneJsUploaderBundle\Request\DropzoneRequest;
use Jupi\DropzoneJsUploaderBundle\Storage\StorageManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DropzoneJsUploader
{
    private EntityManagerInterface $em;
    private DropzoneRequest $request;
    private StorageManager $storageManager;
    private ValidatorInterface $validator;

    private string $entityClassName;

    private bool $isRequestHandled = false;

    public function __construct(
        StorageManager $storageManager,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ) {
        $this->storageManager = $storageManager;
        $this->em = $em;
        $this->validator = $validator;

        $this->request = new DropzoneRequest($requestStack->getCurrentRequest());
    }

    public function handleRequest(string $entityClassName): void
    {
        if ($this->isRequestHandled) throw new \Exception("Error: the current request has already been handled");

        $this->entityClassName = $entityClassName;

        if ($this->request->isChunkedRequest()) {
            $this->handleChunkedRequest();
        } else {
            $this->handleSingleRequest();
        }

        $this->isRequestHandled = true;
    }

    private function handleSingleRequest(): void
    {
        $file = $this->request->getFile();

        $entityAbstraction = $this->createEntityAbstraction($file, $file->getClientOriginalName());

        $this->validateEntity($entityAbstraction);

        $this->storageManager->upload($entityAbstraction);

        $this->createDbRow($entityAbstraction);
    }

    private function handleChunkedRequest(): void
    {
        $tempFileName = $this->generateNewFileName($this->request->getChunkUuid());

        $uploadedFile = $this->request->getFile();

        if ($this->request->isFirstChunk()) {
            $this->storageManager->createTempFile($uploadedFile, $tempFileName);
        } else {
            $this->storageManager->mergeChunkToTempFile($uploadedFile, $tempFileName);

            if ($this->request->isLastChunk()) {
                $file = new File($this->storageManager->getTempDir() . \DIRECTORY_SEPARATOR . $tempFileName);

                $entityAbstraction = $this->createEntityAbstraction($file, $this->request->getFile()->getClientOriginalName());

                $this->validateEntity($entityAbstraction);
                $this->storageManager->upload($entityAbstraction);
                $this->createDbRow($entityAbstraction);
            }
        }
    }

    private function createEntityAbstraction(
        File $file,
        string $originalName
    ): EntityAbstraction {
        $entityAbstraction = new EntityAbstraction(new $this->entityClassName);

        $name = $this->generateNewFileName();
        $size = $file->getSize();
        $mimeType = $file->getMimeType();

        $entityAbstraction
            ->setFile($file)
            ->setName($name)
            ->setOriginalName($originalName)
            ->setSize($size)
            ->setMimetype($mimeType);

        if (false !== \strpos($mimeType, 'image/') && 'image/svg+xml' !== $mimeType && false !== $dimensions = @\getimagesize($file->getPath() . \DIRECTORY_SEPARATOR . $file->getFilename())) {
            $entityAbstraction->setDimensions(\array_splice($dimensions, 0, 2));
        }

        return $entityAbstraction;
    }

    private function validateEntity(EntityAbstraction $entityAbstraction): void
    {
        $errors = $this->validator->validate($entityAbstraction->getEntity());

        if (count($errors) > 0) {
            throw new \Exception((string) $errors);
        }
    }

    private function createDbRow(EntityAbstraction $entityAbstraction): void
    {
        $this->em->persist($entityAbstraction->getEntity());
        $this->em->flush();
    }

    private function generateNewFileName(string $name = null): string
    {
        return ($name ?? uniqid()) . '.' . $this->request->getFile()->getClientOriginalExtension();
    }
}
