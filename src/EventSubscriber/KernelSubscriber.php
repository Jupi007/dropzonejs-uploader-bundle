<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\EventSubscriber;

use Jupi\DropzoneJsUploaderBundle\Service\UploadManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UploadManager $uploader
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->uploader->isCurrentRequestHandled()) {
            // Return an empty response to fix the broken Dropzone error message
            $event->setResponse(new Response('',
                $this->uploader->isUploadSoftFailed() && 200 === $event->getResponse()->getStatusCode()
                    ? 500
                    : $event->getResponse()->getStatusCode()
            ));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
