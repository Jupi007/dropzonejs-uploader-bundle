<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Service;

use Jupi\DropzoneJsUploaderBundle\Request\DropzoneRequest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DropzoneJsUploader
{
    private DropzoneRequest $request;
    private Filesystem $filesystem;

    private bool $isRequestHandled = false;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = new DropzoneRequest($requestStack->getCurrentRequest());
        $this->filesystem = new Filesystem();
    }

    public function handleRequest(callable $callback): void
    {
        if ($this->isRequestHandled) throw new \Exception("Error: the current request has already been handled");

        if ($this->request->isChunkedRequest()) {
            $this->handleChunkedRequest($callback);
        } else {
            $this->handleSingleRequest($callback);
        }

        $this->isRequestHandled = true;
    }

    private function handleSingleRequest(callable $callback): void
    {
        $file = $this->request->getFile();

        $callback($file);
    }

    private function handleChunkedRequest(callable $callback): void
    {
        $chunk = $this->request->getFile();

        $tempFileName = $this->request->getChunkUuid() . $chunk->getClientOriginalExtension();
        $tempDir = sys_get_temp_dir();
        $tempFilePath = $tempDir . \DIRECTORY_SEPARATOR . $tempFileName;

        if ($this->request->isFirstChunk()) {
            $chunk->move($tempDir, $tempFileName);
        } else {
            $this->filesystem->appendToFile($tempFilePath, $chunk->getContent());

            if ($this->request->isLastChunk()) {
                $file = new UploadedFile($tempFilePath, $chunk->getClientOriginalName(), null, null, true);

                $callback($file);
            }
        }
    }
}
