<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Request;

use Jupi\DropzoneJsUploaderBundle\Exception\SoftFailException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DropzoneChunkedRequest
{
    private UploadedFile $file;
    private string $uuid;
    private int $chunkIndex;
    private int $chunkSize;
    private int $chunkByteOffset;
    private int $totalChunkCount;
    private int $totalFileSize;

    public function __construct(Request $request)
    {
        $this->file = $this->fetchRequestFile($request, 'file');

        $this->uuid = (string) $this->fetchRequestInput($request, 'dzuuid');
        $this->chunkIndex = (int) $this->fetchRequestInput($request, 'dzchunkindex');
        $this->chunkSize = (int) $this->fetchRequestInput($request, 'dzchunksize');
        $this->chunkByteOffset = (int) $this->fetchRequestInput($request, 'dzchunkbyteffset');
        $this->totalChunkCount = (int) $this->fetchRequestInput($request, 'dztotalchunkcount');
        $this->totalFileSize = (int) $this->fetchRequestInput($request, 'dztotalfilesize');
    }

    private function fetchRequestInput(Request $request, string $key): mixed
    {
        $input = $request->request->get($key);

        if (null === $input) {
            throw new SoftFailException(sprintf('The "%s" request input key is missing.', $key));
        }

        return $input;
    }

    private function fetchRequestFile(Request $request, string $key): UploadedFile
    {
        $file = $request->files->get($key);

        if (!$file instanceof UploadedFile) {
            throw new SoftFailException(sprintf('The "%s" request file is missing.', $key));
        }

        return $file;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getChunkIndex(): int
    {
        return $this->chunkIndex;
    }

    public function getChunkSize(): int
    {
        return $this->isLastChunk() ? $this->getLastChunkSize() : $this->chunkSize;
    }

    public function getDefaultChunkSize(): int
    {
        return $this->chunkSize;
    }

    public function getLastChunkSize(): int
    {
        return $this->totalFileSize % $this->chunkSize;
    }

    public function getChunkByteOffset(): int
    {
        return $this->chunkByteOffset;
    }

    public function getTotalChunkCount(): int
    {
        return $this->totalChunkCount;
    }

    public function isFirstChunk(): bool
    {
        return 0 === $this->chunkIndex;
    }

    public function isLastChunk(): bool
    {
        return $this->chunkIndex + 1 === $this->totalChunkCount;
    }

    public function getTotalFileSize(): int
    {
        return $this->totalFileSize;
    }
}
