<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Entity\EntityInterface;
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
        $events[ConfigEvents::DELETE][] = ['onConfigDelete'];

        return $events;
    }

    public function onPathDelete(array $path): void
    {
        $this->repository->deleteDependenciesByType(
            EntityAliasDependencyInterface::TYPE_PATH_ALIAS,
            (int) $path['pid']
        );
    }

    public function onConfigDelete(ConfigCrudEvent $event): void
    {
        $this->repository->deleteDependenciesByType(
            EntityAliasDependencyInterface::TYPE_CONFIG,
            $event->getConfig()->getName()
        );
    }

    public function onEntityDelete(EntityInterface $entity): void
    {
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
