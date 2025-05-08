<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Service;

use Jupi\DropzoneJsUploaderBundle\Exception\BadDropzoneRequest;
use Jupi\DropzoneJsUploaderBundle\Request\DropzoneChunkedRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UploadManager
{
    private Request $request;
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    private bool $currentRequestHandled = false;
    private bool $uploadFail = false;
    private string $uploadFailMessage = '';

    public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        $request = $requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            throw new \LogicException('A current request is required.');
        }

        $this->request = $request;
        $this->logger = $logger;
        $this->filesystem = new Filesystem();
    }

    public function handleCurrentRequest(): ?UploadedFile
    {
        if ($this->currentRequestHandled) {
            throw new \LogicException('This request has already been handled.');
        }

        $this->currentRequestHandled = true;

        if ($this->isChunkedRequest()) {
            return $this->handleChunkedRequest();
        }

        return $this->handleSingleRequest();
    }

    private function handleSingleRequest(): ?UploadedFile
    {
        $this->logger->info('Handling single request');
        $file = $this->request->files->get('file');

        if ($file instanceof UploadedFile) {
            return $file;
        }

        return $this->throwUploadFail('The file is missing in the request');
    }

    private function handleChunkedRequest(): ?UploadedFile
    {
        $this->logger->info('Handling chunked request');

        try {
            $dzRequest = new DropzoneChunkedRequest($this->request);
        } catch (BadDropzoneRequest $e) {
            return $this->throwUploadFail($e->getMessage());
        }

        $chunk = $dzRequest->getFile();

        if ($chunk->getSize() !== $dzRequest->getChunkSize()) {
            return $this->throwUploadFail('Chunk size doesn\'t match the expected one');
        }

        $tempFileName = 'phpdropzonejs'.$dzRequest->getUuid();
        $tempDir = sys_get_temp_dir();
        $tempFilePath = $tempDir.\DIRECTORY_SEPARATOR.$tempFileName;

        if ($dzRequest->isFirstChunk()) {
            try {
                $chunk->move($tempDir, $tempFileName);
            } catch (PartialFileException $e) {
                return $this->throwUploadFail($e->getMessage());
            }
        } else {
            $this->filesystem->appendToFile($tempFilePath, $chunk->getContent());

            if ($dzRequest->isLastChunk()) {
                return new UploadedFile(
                    path: $tempFilePath,
                    originalName: $chunk->getClientOriginalName(),
                    mimeType: mime_content_type($tempFilePath),
                    test: true,
                );
            }
        }

        return null;
    }

    private function isChunkedRequest(): bool
    {
        return null !== $this->request->request->get('dzuuid');
    }

    public function isCurrentRequestHandled(): bool
    {
        return $this->currentRequestHandled;
    }

    public function isUploadFailed(): bool
    {
        return $this->uploadFail;
    }

    public function getUploadFailedMessage(): string
    {
        return $this->uploadFailMessage;
    }

    public function throwUploadFail(string $message): null
    {
        $this->logger->error($message);
        $this->uploadFail = true;
        $this->uploadFailMessage = $message;

        return null;
    }
}
