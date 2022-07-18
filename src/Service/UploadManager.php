<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Service;

use Jupi\DropzoneJsUploaderBundle\Exception\SoftFailException;
use Jupi\DropzoneJsUploaderBundle\Request\DropzoneChunkedRequest;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UploadManager
{
    private Request $request;
    private Filesystem $filesystem;

    private bool $currentRequestHandled = false;
    private bool $softUploadFail = false;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->filesystem = new Filesystem();
    }

    public function handleCurrentRequest(): ?UploadedFile
    {
        if ($this->currentRequestHandled) {
            throw new \LogicException('This request has already been handled');
        }

        $this->currentRequestHandled = true;

        if ($this->isChunkedRequest()) {
            return $this->handleChunkedRequest();
        }

        return $this->handleSingleRequest();
    }

    private function handleSingleRequest(): ?UploadedFile
    {
        $file = $this->request->files->get('file');

        if ($file instanceof UploadedFile) {
            return $file;
        }

        return $this->throwSoftUploadFail();
    }

    private function handleChunkedRequest(): ?UploadedFile
    {
        try {
            $dzRequest = new DropzoneChunkedRequest($this->request);
        } catch (SoftFailException $e) {
            return $this->throwSoftUploadFail();
        }

        $chunk = $dzRequest->getFile();

        if ($chunk->getSize() !== $dzRequest->getChunkSize()) {
            return $this->throwSoftUploadFail();
        }

        $tempFileName = $dzRequest->getUuid().$chunk->getClientOriginalExtension();
        $tempDir = sys_get_temp_dir();
        $tempFilePath = $tempDir.\DIRECTORY_SEPARATOR.$tempFileName;

        if ($dzRequest->isFirstChunk()) {
            $chunk->move($tempDir, $tempFileName);
        } else {
            $this->filesystem->appendToFile($tempFilePath, $chunk->getContent());

            if ($dzRequest->isLastChunk()) {
                $file = new UploadedFile($tempFilePath, $chunk->getClientOriginalName(), null, null, true);

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
    public function throwSoftUploadFail()
    {
        $this->softUploadFail = true;

        return null;
    }
}
