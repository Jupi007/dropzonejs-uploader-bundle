<h1 align="center">Jupi DropzoneJS Uploader Bundle</h1>

A Symfony bundle to handle [Dropzone.js](https://github.com/dropzone/dropzone) upload request.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require "jupi/dropzonejs-uploader-bundle"
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require "jupi/dropzonejs-uploader-bundle"
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Jupi\DropzoneJsUploaderBundle\DropzoneJsUploaderBundle::class => ['all' => true],
];
```

Usage
=====

The way of working of this bundle is very simple. It provides a `DropzoneJsUploader` service which handle the current request and pass the uploaded file to the callback function.

If the request is chunked, a temp file is created inside the system temp folder (using `sys_get_temp_dir()`) and the callback function is only called when the file is entirely uploaded.

I hightly recommend to use [VichUploaderBundle](https://github.com/dustin10/VichUploaderBundle) to handle the database saving side.

```php
<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\File;
// ...
use Jupi\DropzoneJsUploaderBundle\Service\DropzoneJsUploader;

class AppController extends AbstractController
{
    /**
     * @Route("/upload", name="upload")
     */
    public function upload(DropzoneJsUploader $dropzoneUploader): Response
    {
        $dropzoneUploader->handleRequest(function (UploadedFile $file) {
            $entity = new File(); // Assuming it is a correctly configured VichUploadable class
            $entity->setFile($file);

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
        });

        return new JsonResponse(['success' => true]);
    }
}
```
