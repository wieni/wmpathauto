<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\wmpathauto\EntityAliasDependencyInterface;
use Drupal\wmpathauto\EntityAliasDependencyRepository;
use Drupal\wmpathauto\EntityAliasDependencyResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DependencyUpdateSubscriber implements EventSubscriberInterface
{
    /** @var Connection */
    protected $database;
    /** @var EntityAliasDependencyResolverInterface */
    protected $resolver;
    /** @var EntityAliasDependencyRepository */
    protected $repository;

    public function __construct(
        Connection $database,
        EntityAliasDependencyResolverInterface $resolver,
        EntityAliasDependencyRepository $repository
    ) {
        $this->database = $database;
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
        if (!$this->isSchemaInstalled()) {
            return;
        }

        // Update entity aliases depending on this path
        $this->repository->updateEntityAliasesByType(
            EntityAliasDependencyInterface::TYPE_PATH_ALIAS,
            (int) $path['pid']
        );
    }

    public function onConfigUpdate(ConfigCrudEvent $event): void
    {
        if (!$this->isSchemaInstalled()) {
            return;
        }

        // Update entity aliases depending on this config
        $this->repository->updateEntityAliasesByType(
            EntityAliasDependencyInterface::TYPE_CONFIG,
            $event->getConfig()->getName()
        );
    }

    public function onEntityUpdate(EntityInterface $entity): void
    {
        if (!$this->isSchemaInstalled()) {
            return;
        }

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

    protected function isSchemaInstalled(): bool
    {
        return $this->database->schema()
            ->tableExists('entity_alias_dependency');
    }
}
