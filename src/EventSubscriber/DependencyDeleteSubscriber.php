<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\hook_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\hook_event_dispatcher\Event\Path\PathDeleteEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\wmpathauto\EntityAliasDependencyInterface;
use Drupal\wmpathauto\EntityAliasDependencyRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DependencyDeleteSubscriber implements EventSubscriberInterface
{
    /** @var EntityAliasDependencyRepository */
    protected $repository;

    public function __construct(
        EntityAliasDependencyRepository $repository
    ) {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        $events[HookEventDispatcherInterface::PATH_DELETE][] = ['onPathDelete'];
        $events[ConfigEvents::DELETE][] = ['onConfigDelete'];
        $events[HookEventDispatcherInterface::ENTITY_DELETE][] = ['onEntityDelete'];

        return $events;
    }

    public function onPathDelete(PathDeleteEvent $event): void
    {
        $this->repository->deleteDependenciesByType(
            EntityAliasDependencyInterface::TYPE_PATH_ALIAS,
            $event->getPid()
        );
    }

    public function onConfigDelete(ConfigCrudEvent $event): void
    {
        $this->repository->deleteDependenciesByType(
            EntityAliasDependencyInterface::TYPE_CONFIG,
            $event->getConfig()->getName()
        );
    }

    public function onEntityDelete(EntityDeleteEvent $event): void
    {
        $entity = $event->getEntity();

        $this->repository->deleteDependenciesByType(
            EntityAliasDependencyInterface::TYPE_ENTITY,
            $value = implode(':', [
                $entity->getEntityTypeId(),
                $entity->id(),
                $entity->language()->getId(),
            ])
        );

        $this->repository->deleteDependenciesByEntity($entity);
    }
}
