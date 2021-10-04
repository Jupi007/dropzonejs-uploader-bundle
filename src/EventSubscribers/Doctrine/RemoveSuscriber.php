<?php

namespace Jupi\DropzoneJsUploaderBundle\EventSubscribers\Doctrine;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Jupi\DropzoneJsUploaderBundle\Storage\StorageManager;
use Symfony\Component\Filesystem\Filesystem;

class RemoveSuscriber implements EventSubscriberInterface
{
    private Filesystem $filesystem;
    private StorageManager $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
        $this->filesystem = new Filesystem();
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postRemove,
        ];
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $filepath = $this->storageManager->resolvePath($entity);

        if ($filepath !== null) {
            $this->filesystem->remove($filepath);
        }
    }
}
