<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Attribute;

use Jupi\DropzoneJsUploaderBundle\ValueResolver\DropzoneJsValueResolver;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MapDropzoneJsUpload extends ValueResolver
{
    public function __construct()
    {
        parent::__construct(DropzoneJsValueResolver::class);
    }
}
