<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\Event\Path\PathUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\wmpathauto\EntityAliasDependencyInterface;
use Drupal\wmpathauto\EntityAliasDependencyRepository;
use Drupal\wmpathauto\EntityAliasDependencyResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DependencyUpdateSubscriber implements EventSubscriberInterface
{
    /** @var EntityAliasDependencyResolverInterface */
    protected $resolver;
    /** @var EntityAliasDependencyRepository */
    protected $repository;

    public function __construct(
        EntityAliasDependencyResolverInterface $resolver,
        EntityAliasDependencyRepository $repository
    ) {
        $this->resolver = $resolver;
        $this->repository = $repository;
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
        // Update entity aliases depending on this path
        $this->repository->updateEntityAliasesByType(
            EntityAliasDependencyInterface::TYPE_PATH_ALIAS,
            $event->getPid()
        );
    }

    public function onConfigUpdate(ConfigCrudEvent $event): void
    {
        // Update entity aliases depending on this config
        $this->repository->updateEntityAliasesByType(
            EntityAliasDependencyInterface::TYPE_CONFIG,
            $event->getConfig()->getName()
        );
    }

    public function onEntityUpdate(EntityUpdateEvent $event): void
    {
        $entity = $event->getEntity();

        // If the updated entity has a pathauto pattern,
        // resolve and add its dependencies
        $dependencies = $this->resolver->getDependencies($entity);
        $this->repository->addDependencies($entity, $dependencies);

        // Update entity aliases depending on this entity
        $this->repository->updateEntityAliasesByType(
            EntityAliasDependencyInterface::TYPE_ENTITY,
            implode(':', [
                $entity->getEntityTypeId(),
                $entity->id(),
                $entity->language()->getId(),
            ])
        );
    }
}
