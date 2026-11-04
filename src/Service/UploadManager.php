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
use Symfony\Contracts\Service\ResetInterface;

class UploadManager implements ResetInterface
{
    private Filesystem $filesystem;

    private bool $currentRequestHandled = false;
    private bool $uploadFailed = false;
    private string $uploadFailedMessage = '';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function reset(): void
    {
        $this->currentRequestHandled = false;
        $this->uploadFailed = false;
        $this->uploadFailedMessage = '';
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

    private function getCurrentRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            throw new \LogicException('A current request is required.');
        }

        return $request;
    }

    private function handleSingleRequest(): ?UploadedFile
    {
        $this->logger->info('Handling single request');
        $file = $this->getCurrentRequest()->files->get('file');

        if (!$file instanceof UploadedFile) {
            return $this->throwUploadFailed('The file is missing in the request');
        }

        return $file;
    }

    private function handleChunkedRequest(): ?UploadedFile
    {
        $this->logger->info('Handling chunked request');

        try {
            $dzRequest = new DropzoneChunkedRequest($this->getCurrentRequest());
        } catch (BadDropzoneRequest $e) {
            return $this->throwUploadFailed($e->getMessage());
        }

        $chunk = $dzRequest->getFile();

        if ($chunk->getSize() !== $dzRequest->getChunkSize()) {
            return $this->throwUploadFailed(
                \sprintf('Chunk size doesn\'t match the expected one. Expected %s, current %s.', $dzRequest->getChunkSize(), $chunk->getSize()),
            );
        }

        $tempFileName = 'phpdropzonejs'.$dzRequest->getUuid();
        $tempDir = sys_get_temp_dir();
        $tempFilePath = $tempDir.\DIRECTORY_SEPARATOR.$tempFileName;

        if ($dzRequest->isFirstChunk()) {
            try {
                $chunk->move($tempDir, $tempFileName);
            } catch (PartialFileException $e) {
                return $this->throwUploadFailed($e->getMessage());
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
        return null !== $this->getCurrentRequest()->request->get('dzuuid');
    }

    public function isCurrentRequestHandled(): bool
    {
        return $this->currentRequestHandled;
    }

    public function isUploadFailed(): bool
    {
        return $this->uploadFailed;
    }

    public function getUploadFailedMessage(): string
    {
        return $this->uploadFailedMessage;
    }

    public function throwUploadFailed(string $message): null
    {
        $this->logger->error($message);
        $this->uploadFailed = true;
        $this->uploadFailedMessage = $message;

        return null;
    }
}
