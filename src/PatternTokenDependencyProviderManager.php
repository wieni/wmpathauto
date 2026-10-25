<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\PatternTokenDependencyProvider;
use Drupal\wmpathauto\Attribute\PatternTokenDependencyProvider as PatternTokenDependencyProviderAttribute;

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
        // Drupal 10.2 added attribute discovery, taking the attribute class
        // before the annotation class; leaving it out is deprecated since 11.2.
        $definitionClasses = interface_exists(AttributeInterface::class)
            ? [PatternTokenDependencyProviderAttribute::class, PatternTokenDependencyProvider::class]
            : [PatternTokenDependencyProvider::class];

        parent::__construct(
            'Plugin/PatternTokenDependencyProvider',
            $namespaces,
            $moduleHandler,
            PatternTokenDependencyProviderInterface::class,
            ...$definitionClasses
        );
        $this->alterInfo('wmpathauto_pattern_token_dependency_provider_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_pattern_token_dependency_providers');
    }
}
