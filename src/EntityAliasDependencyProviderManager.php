<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\EntityAliasDependencyProvider;
use Drupal\wmpathauto\Attribute\EntityAliasDependencyProvider as EntityAliasDependencyProviderAttribute;

class EntityAliasDependencyProviderManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        // The annotation class keeps annotated plugins of other modules working.
        parent::__construct(
            'Plugin/EntityAliasDependencyProvider',
            $namespaces,
            $moduleHandler,
            EntityAliasDependencyProviderInterface::class,
            EntityAliasDependencyProviderAttribute::class,
            EntityAliasDependencyProvider::class
        );
        $this->alterInfo('wmpathauto_entity_alias_dependency_provider_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_entity_alias_dependency_providers');
    }
}
