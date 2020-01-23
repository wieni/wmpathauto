<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wmpathauto\Annotation\AliasBuilder;

class AliasBuilderManager extends DefaultPluginManager
{
    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        parent::__construct(
            'Plugin/AliasBuilder',
            $namespaces,
            $moduleHandler,
            AliasBuilderInterface::class,
            AliasBuilder::class
        );
        $this->alterInfo('wmpathauto_alias_builder_info');
        $this->setCacheBackend($cacheBackend, 'wmpathauto_alias_builders');
    }
}
