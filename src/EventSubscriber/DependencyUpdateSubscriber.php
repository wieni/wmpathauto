<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Entity\EntityInterface;
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
        $events[ConfigEvents::SAVE][] = ['onConfigUpdate'];

        return $events;
    }

    public function onPathUpdate(array $path): void
    {
        // Update entity aliases depending on this path
        $this->repository->updateEntityAliasesByType(
            EntityAliasDependencyInterface::TYPE_PATH_ALIAS,
            (int) $path['pid']
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

    public function onEntityUpdate(EntityInterface $entity): void
    {
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
