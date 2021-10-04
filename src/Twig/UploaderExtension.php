<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Twig;

use Jupi\DropzoneJsUploaderBundle\Twig\UploaderRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UploaderExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('jupi_dropzone_uploader_asset', [UploaderRuntime::class, 'asset']),
        ];
    }
}
