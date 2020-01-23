<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\PatternTokenDependencies;

/**
 * @method PatternTokenDependenciesInterface createInstance($plugin_id, array $configuration = [])
 */
class PatternTokenDependenciesManager extends DefaultPluginManager
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
            PatternTokenDependenciesInterface::class,
            PatternTokenDependencies::class
        );
        $this->alterInfo('wmpathauto_pattern_token_dependencies_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_pattern_token_dependencies');
    }
}
