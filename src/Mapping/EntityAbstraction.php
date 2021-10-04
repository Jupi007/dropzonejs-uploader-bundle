<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Mapping;

use Jupi\DropzoneJsUploaderBundle\Mapping\Annotation\DropzoneUploadableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EntityAbstraction
{
    private PropertyAccessor $propertyAccessor;
    private DropzoneUploadableEntity $annotation;
    private object $entity;

    public function __construct(object $entity)
    {
        $this->entity = $entity;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->annotation = EntityAnnotationReader::read(get_class($entity));
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getFile(): File
    {
        $property = $this->annotation->getFile();

        return $this->getValue($property);
    }

    public function setFile(File $file): self
    {
        $fileProperty = $this->annotation->getFile();
        $this->setValue($fileProperty, $file);

        return $this;
    }

    public function getName(): string
    {
        $property = $this->annotation->getName();

        return (string) $this->getValue($property);
    }

    public function setName(string $name): self
    {
        $property = $this->annotation->getName();
        $this->setValue($property, $name);

        return $this;
    }

    public function getSize(): int
    {
        $property = $this->annotation->getSize();

        return (int) $this->getValue($property);
    }

    public function setSize(int $size): self
    {
        $property = $this->annotation->getSize();
        $this->setValue($property, $size);

        return $this;
    }

    public function getMimetype(): string
    {
        $property = $this->annotation->getMimetype();

        return (string) $this->getValue($property);
    }

    public function setMimetype(string $mimetype): self
    {
        $property = $this->annotation->getMimetype();
        $this->setValue($property, $mimetype);

        return $this;
    }

    public function getOriginalName(): string
    {
        $property = $this->annotation->getOriginalName();

        return (string) $this->getValue($property);
    }

    public function setOriginalName(string $originalName): self
    {
        $property = $this->annotation->getOriginalName();
        $this->setValue($property, $originalName);

        return $this;
    }

    public function getDimensions(): array
    {
        $property = $this->annotation->getDimensions();

        return (array) $this->getValue($property);
    }

    public function setDimensions(array $dimensions): self
    {
        $property = $this->annotation->getDimensions();
        $this->setValue($property, $dimensions);

        return $this;
    }

    private function getValue(?string $property)
    {
        if ($property !== null) {
            return $this->propertyAccessor->getValue(
                $this->entity,
                $property
            );
        }

        return null;
    }

    private function setValue(?string $property, $value): void
    {
        if ($property !== null) {
            $this->propertyAccessor->setValue(
                $this->entity,
                $property,
                $value
            );
        }
    }
}
