<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Twig;

use Jupi\DropzoneJsUploaderBundle\Storage\StorageManager;
use Twig\Extension\RuntimeExtensionInterface;

class UploaderRuntime implements RuntimeExtensionInterface
{
    private StorageManager $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    public function asset(object $entity): ?string
    {
        return $this->storageManager->resolveUri($entity);
    }
}
