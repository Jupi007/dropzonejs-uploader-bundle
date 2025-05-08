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
        private UploadManager $uploader,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || 200 !== $event->getResponse()->getStatusCode()) {
            return;
        }

        if ($this->uploader->isCurrentRequestHandled()) {
            $success = !$this->uploader->isUploadFailed();
            $event->setResponse(new Response(
                $success ? 'SUCCESS' : $this->uploader->getUploadFailedMessage(),
                $success ? $event->getResponse()->getStatusCode() : Response::HTTP_BAD_REQUEST,
            ));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
