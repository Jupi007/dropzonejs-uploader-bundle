<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\ValueResolver;

use Jupi\DropzoneJsUploaderBundle\Attribute\MapDropzoneJsUpload;
use Jupi\DropzoneJsUploaderBundle\Service\UploadManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DropzoneJsValueResolver implements ValueResolverInterface
{
    public function __construct(
        private UploadManager $uploader
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (UploadedFile::class !== $argument->getType()) {
            return [];
        }

        $attributes = $argument->getAttributes(MapDropzoneJsUpload::class, ArgumentMetadata::IS_INSTANCEOF);

        if (!isset($attributes[0]) && !$attributes[0] instanceof MapDropzoneJsUpload) {
            return [];
        }

        return [
            $this->uploader->handleCurrentRequest(),
        ];
    }
}
