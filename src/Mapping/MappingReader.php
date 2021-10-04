<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Mapping;

class MappingReader
{
    private array $mappings;

    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }

    public function getClassRelatedMapping(string $className): array
    {
        $annotation = EntityAnnotationReader::read($className);

        return $this->mappings[$annotation->getMapping()];
    }
}
