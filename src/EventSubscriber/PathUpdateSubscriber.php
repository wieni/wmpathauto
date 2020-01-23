<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\hook_event_dispatcher\Event\Path\PathUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\wmpathauto\Plugin\QueueWorker\AliasQueueWorker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PathUpdateSubscriber implements EventSubscriberInterface
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
        $events[HookEventDispatcherInterface::PATH_UPDATE][] = ['updateDependentAliases'];

        return $events;
    }

    public function updateDependentAliases(PathUpdateEvent $event): void
    {
        $queue = $this->queueFactory->get(AliasQueueWorker::ID);
        $storage = $this->getStorage('pid:' . $event->getPid());

        // Queue all entities depending on this pid
        foreach ($storage->getAll() as $dependent) {
            $queue->createItem($dependent);
        }
    }

    protected function getStorage(string $suffix): KeyValueStoreInterface
    {
        return $this->keyValueFactory->get(
            'wmpathauto.custom.alias.dependencies.' . $suffix
        );
    }
}
