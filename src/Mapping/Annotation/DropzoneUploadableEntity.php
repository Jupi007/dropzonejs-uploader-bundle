<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class DropzoneUploadableEntity
{
    /** @Required */
    private string $mapping;

    /** * @Required */
    private string $file;

    /** * @Required */
    private string $name;

    private ?string $size = null;

    private ?string $mimetype = null;

    private ?string $originalName = null;

    private ?string $dimensions = null;

    public function __construct(array $options = [])
    {
        $this->checkRequiredAttibutes($options, ['mapping', 'file', 'name']);

        foreach ($options as $property => $value) {
            if (!\property_exists($this, $property)) {
                throw new \RuntimeException(\sprintf('Unknown key "%s" for annotation "@%s".', $property, static::class));
            }

            $this->$property = $value;
        }
    }

    private function checkRequiredAttibutes(array $options = [], array $requiredAttibutes = []): void
    {
        foreach ($requiredAttibutes as $attribute) {
            if (empty($options[$attribute])) {
                throw new \InvalidArgumentException('The "' . $attribute . '" attribute of DropzoneUploadableEntity is required.');
            }
        }
    }

    public function getMapping(): string
    {
        return $this->mapping;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }
}
