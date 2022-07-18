<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\Attribute;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[\Attribute(\Attribute::TARGET_METHOD)]
class DropzoneJsParamConverter extends ParamConverter
{
    public function __construct(string $data)
    {
        parent::__construct($data, converter: 'jupi.dropzone_js_converter');
    }
}
