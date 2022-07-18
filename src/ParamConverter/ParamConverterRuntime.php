<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\ParamConverter;

use Jupi\DropzoneJsUploaderBundle\Service\UploadManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ParamConverterRuntime implements ParamConverterInterface
{
    public function __construct(
        private UploadManager $uploader
    ) {
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $request->attributes->set(
            $configuration->getName(),
            $this->uploader->handleCurrentRequest()
        );

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return UploadedFile::class === $configuration->getClass();
    }
}
