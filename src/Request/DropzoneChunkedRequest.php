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

        $this->uuid = $this->fetchStringRequestInput($request, 'dzuuid');
        $this->chunkIndex = $this->fetchIntRequestInput($request, 'dzchunkindex');
        $this->chunkSize = $this->fetchIntRequestInput($request, 'dzchunksize');
        $this->chunkByteOffset = $this->fetchIntRequestInput($request, 'dzchunkbyteoffset');
        $this->totalChunkCount = $this->fetchIntRequestInput($request, 'dztotalchunkcount');
        $this->totalFileSize = $this->fetchIntRequestInput($request, 'dztotalfilesize');
    }

    private function fetchStringRequestInput(Request $request, string $key): string
    {
        $input = $request->request->get($key);

        if (!\is_string($input)) {
            throw new SoftFailException(sprintf('The "%s" request input key is missing.', $key));
        }

        return $input;
    }

    private function fetchIntRequestInput(Request $request, string $key): int
    {
        $input = $request->request->get($key);

        if (!\is_int($input)) {
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
