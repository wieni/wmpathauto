<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
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
        // Drupal 10.2 added attribute discovery, taking the attribute class
        // before the annotation class; leaving it out is deprecated since 11.2.
        $definitionClasses = interface_exists(AttributeInterface::class)
            ? [EntityAliasDependencyProviderAttribute::class, EntityAliasDependencyProvider::class]
            : [EntityAliasDependencyProvider::class];

        parent::__construct(
            'Plugin/EntityAliasDependencyProvider',
            $namespaces,
            $moduleHandler,
            EntityAliasDependencyProviderInterface::class,
            ...$definitionClasses
        );
        $this->alterInfo('wmpathauto_entity_alias_dependency_provider_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_entity_alias_dependency_providers');
    }
}
