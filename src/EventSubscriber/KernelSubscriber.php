<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\EventSubscriber;

use Jupi\DropzoneJsUploaderBundle\Service\UploadManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelSubscriber implements EventSubscriberInterface
{
    private bool $exception = false;

    public function __construct(
        private UploadManager $uploader
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $this->exception = true;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->uploader->isCurrentRequestHandled() && ($this->exception || $this->uploader->isUploadSoftFailed())) {
            // Return an empty response to display the default Dropzone "500" error message
            $event->setResponse(new Response('', 500));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
