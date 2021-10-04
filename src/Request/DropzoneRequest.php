<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Request;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DropzoneRequest
{
    private ?UploadedFile $file;
    private ?string $chunkUuid;
    private ?int $chunkIndex;
    private ?int $totalChunkCount;

    public function __construct(Request $request)
    {
        $this->file = $request->files->get('file');

        $this->chunkUuid = (string) $request->request->get('dzuuid');
        $this->chunkIndex = (int) $request->request->get('dzchunkindex');
        $this->totalChunkCount = (int) $request->request->get('dztotalchunkcount');
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function getChunkUuid(): ?string
    {
        return $this->chunkUuid;
    }

    public function getChunkIndex(): ?int
    {
        return $this->chunkIndex;
    }

    public function getTotalChunkCount(): ?int
    {
        return $this->totalChunkCount;
    }

    public function isChunkedRequest(): bool
    {
        return $this->chunkUuid != null;
    }

    public function isFirstChunk(): bool
    {
        return $this->chunkIndex == 0;
    }

    public function isLastChunk(): bool
    {
        return $this->chunkIndex + 1 == $this->totalChunkCount;
    }
}
