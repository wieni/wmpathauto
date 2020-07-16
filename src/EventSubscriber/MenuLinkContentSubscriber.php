<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\wmpathauto\EntityAliasDependencyRepositoryInterface;
use Drupal\wmpathauto\EntityAliasDependencyResolverInterface;
use Drupal\wmpathauto\Plugin\PatternTokenDependencyProvider\MenuLinkEntityTrait;

class MenuLinkContentSubscriber
{
    use MenuLinkEntityTrait;

    /** @var Connection */
    protected $database;
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var RouteProviderInterface */
    protected $routeProvider;
    /** @var EntityAliasDependencyResolverInterface */
    protected $resolver;
    /** @var EntityAliasDependencyRepositoryInterface */
    protected $repository;

    public function __construct(
        Connection $database,
        EntityTypeManagerInterface $entityTypeManager,
        RouteProviderInterface $routeProvider,
        EntityAliasDependencyResolverInterface $resolver,
        EntityAliasDependencyRepositoryInterface $repository
    ) {
        $this->database = $database;
        $this->entityTypeManager = $entityTypeManager;
        $this->routeProvider = $routeProvider;
        $this->resolver = $resolver;
        $this->repository = $repository;
    }

    public function onMenuLinkUpdate(EntityInterface $entity): void
    {
        if (!$this->isSchemaInstalled()) {
            return;
        }

        if (!$entity instanceof \Drupal\menu_link_content\MenuLinkContentInterface) {
            return;
        }

        if (!$referencedEntity = $this->getReferencedEntity($entity)) {
            return;
        }

        // Re-resolve dependencies from this entity's pathauto pattern
        // since the menu link tree might have changed.
        $dependencies = $this->resolver->getDependencies($referencedEntity);
        $this->repository->addDependencies($referencedEntity, $dependencies);
    }

    protected function isSchemaInstalled(): bool
    {
        return $this->database->schema()
            ->tableExists('entity_alias_dependency');
    }
}
