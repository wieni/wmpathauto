<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\PatternBuilder;
use Drupal\wmpathauto\Attribute\PatternBuilder as PatternBuilderAttribute;

class PatternBuilderManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        // Drupal 10.2 added attribute discovery, taking the attribute class
        // before the annotation class; leaving it out is deprecated since 11.2.
        $definitionClasses = interface_exists(AttributeInterface::class)
            ? [PatternBuilderAttribute::class, PatternBuilder::class]
            : [PatternBuilder::class];

        parent::__construct(
            'Plugin/PatternBuilder',
            $namespaces,
            $moduleHandler,
            PatternBuilderInterface::class,
            ...$definitionClasses
        );
        $this->alterInfo('wmpathauto_pattern_builder_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_pattern_builders');
    }
}
