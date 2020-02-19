<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\PatternTokenDependencies;

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
            'Plugin/PatternTokenDependencies',
            $namespaces,
            $moduleHandler,
            PatternTokenDependencyProviderInterface::class,
            PatternTokenDependencies::class
        );
        $this->alterInfo('pattern_token_dependencies');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_pattern_token_dependencies');
    }
}
