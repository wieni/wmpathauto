<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\AliasBuilder;
use Drupal\wmpathauto\Attribute\AliasBuilder as AliasBuilderAttribute;

class AliasBuilderManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        // Drupal 10.2 added attribute discovery, taking the attribute class
        // before the annotation class; leaving it out is deprecated since 11.2.
        $definitionClasses = interface_exists(AttributeInterface::class)
            ? [AliasBuilderAttribute::class, AliasBuilder::class]
            : [AliasBuilder::class];

        parent::__construct(
            'Plugin/AliasBuilder',
            $namespaces,
            $moduleHandler,
            AliasBuilderInterface::class,
            ...$definitionClasses
        );
        $this->alterInfo('wmpathauto_alias_builder_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_alias_builders');
    }
}
