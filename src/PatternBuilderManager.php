<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\PatternBuilder;

class PatternBuilderManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        parent::__construct(
            'Plugin/PatternBuilder',
            $namespaces,
            $moduleHandler,
            AliasBuilderInterface::class,
            PatternBuilder::class
        );
        $this->alterInfo('wmpathauto_pattern_builder_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_pattern_builders');
    }
}
