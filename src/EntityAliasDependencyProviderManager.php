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
            'Plugin/PatternDependencies',
            $namespaces,
            $moduleHandler,
            EntityAliasDependencyProviderInterface::class,
            EntityAliasDependencyProvider::class
        );
        $this->alterInfo('wmpathauto_pattern_dependencies_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_pattern_dependencies');
    }
}
