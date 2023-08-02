<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Service;

use Jupi\DropzoneJsUploaderBundle\Exception\SoftFailException;
use Jupi\DropzoneJsUploaderBundle\Request\DropzoneChunkedRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UploadManager
{
    private Request $request;
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    private bool $currentRequestHandled = false;
    private bool $softUploadFail = false;

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

        return $this->throwSoftUploadFail('The file is missing in the request');
    }

    private function handleChunkedRequest(): ?UploadedFile
    {
        $this->logger->info('Handling chunked request');

        try {
            $dzRequest = new DropzoneChunkedRequest($this->request);
        } catch (SoftFailException $e) {
            return $this->throwSoftUploadFail($e->getMessage());
        }

        $chunk = $dzRequest->getFile();

        if ($chunk->getSize() !== $dzRequest->getChunkSize()) {
            return $this->throwSoftUploadFail('Chunk size doesn\'t match the expected one');
        }

        $tempFileName = $dzRequest->getUuid().$chunk->getClientOriginalExtension();
        $tempDir = sys_get_temp_dir();
        $tempFilePath = $tempDir.\DIRECTORY_SEPARATOR.$tempFileName;

        if ($dzRequest->isFirstChunk()) {
            $chunk->move($tempDir, $tempFileName);
        } else {
            $this->filesystem->appendToFile($tempFilePath, $chunk->getContent());

            if ($dzRequest->isLastChunk()) {
                // "test" is true because we are creating a fake upload with a local file
                $file = new UploadedFile($tempFilePath, $chunk->getClientOriginalName(), test: true);

                return $file;
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

    public function isUploadSoftFailed(): bool
    {
        return $this->softUploadFail;
    }

    /** @return null */
    public function throwSoftUploadFail(string $message)
    {
        $this->logger->warning($message);
        $this->softUploadFail = true;

        return null;
    }
}
