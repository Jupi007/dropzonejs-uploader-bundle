<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Jupi\DropzoneJsUploaderBundle\Mapping\Annotation\DropzoneUploadableEntity;

class EntityAnnotationReader
{
    private static array $readAnnotations = [];

    /**
     * @return DropzoneUploadableEntity
     */
    public static function read(string $mappingClassName)
    {
        if (key_exists($mappingClassName, self::$readAnnotations)) {
            return self::$readAnnotations[$mappingClassName];
        } else {
            $annotationReader = new AnnotationReader();
            $reflClass = new \ReflectionClass($mappingClassName);

            $annotation = $annotationReader->getClassAnnotation($reflClass, DropzoneUploadableEntity::class);

            if ($annotation !== null) {
                self::$readAnnotations[$mappingClassName] = $annotation;
            }

            return $annotation;
        }
    }
}
