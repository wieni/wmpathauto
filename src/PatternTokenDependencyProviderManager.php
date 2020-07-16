<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\PatternTokenDependencyProvider;

/**
 * @method PatternTokenDependencyProviderInterface createInstance($plugin_id, array $configuration = [])
 */
class PatternTokenDependencyProviderManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        parent::__construct(
            'Plugin/PatternTokenDependencyProvider',
            $namespaces,
            $moduleHandler,
            PatternTokenDependencyProviderInterface::class,
            PatternTokenDependencyProvider::class
        );
        $this->alterInfo('wmpathauto_pattern_token_dependency_provider_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_pattern_token_dependency_providers');
    }
}
