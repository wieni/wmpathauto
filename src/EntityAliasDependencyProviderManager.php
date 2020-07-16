<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\EntityAliasDependencyProvider;

class EntityAliasDependencyProviderManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        parent::__construct(
            'Plugin/EntityAliasDependencyProvider',
            $namespaces,
            $moduleHandler,
            EntityAliasDependencyProviderInterface::class,
            EntityAliasDependencyProvider::class
        );
        $this->alterInfo('wmpathauto_entity_alias_dependency_provider_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_entity_alias_dependency_providers');
    }
}
