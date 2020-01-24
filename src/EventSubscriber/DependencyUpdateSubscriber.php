<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\Event\Path\PathUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\wmpathauto\Plugin\QueueWorker\AliasQueueWorker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DependencyUpdateSubscriber implements EventSubscriberInterface
{
    /** @var KeyValueFactoryInterface */
    protected $keyValueFactory;
    /** @var QueueFactory */
    protected $queueFactory;

    public function __construct(
        KeyValueFactoryInterface $keyValueFactory,
        QueueFactory $queueFactory
    ) {
        $this->keyValueFactory = $keyValueFactory;
        $this->queueFactory = $queueFactory;
    }

    public static function getSubscribedEvents()
    {
        $events[HookEventDispatcherInterface::PATH_UPDATE][] = ['onPathUpdate'];
        $events[ConfigEvents::SAVE][] = ['onConfigUpdate'];
        $events[HookEventDispatcherInterface::ENTITY_UPDATE][] = ['onEntityUpdate'];

        return $events;
    }

    public function onPathUpdate(PathUpdateEvent $event): void
    {
        $this->queueDependents('pid:' . $event->getPid());
    }

    public function onConfigUpdate(ConfigCrudEvent $event): void
    {
        $this->queueDependents('config:' . $event->getConfig()->getName());
    }

    public function onEntityUpdate(EntityUpdateEvent $event): void
    {
        $entity = $event->getEntity();
        $suffix = implode(':', [
            'entity',
            $entity->getEntityTypeId(),
            $entity->id(),
        ]);

        $this->queueDependents($suffix);
    }

    protected function queueDependents(string $suffix): void
    {
        $queue = $this->queueFactory->get(AliasQueueWorker::ID);
        $storage = $this->getStorage($suffix);

        // Queue all entities depending on this pid
        foreach ($storage->getAll() as $dependent) {
            $queue->createItem($dependent);
        }
    }

    protected function getStorage(string $suffix): KeyValueStoreInterface
    {
        return $this->keyValueFactory->get(
            'wmpathauto.dependencies.' . $suffix
        );
    }
}
