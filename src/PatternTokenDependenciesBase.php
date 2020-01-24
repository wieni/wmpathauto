<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\pathauto\AliasStorageHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class PatternTokenDependenciesBase extends PluginBase implements PatternTokenDependenciesInterface, ContainerFactoryPluginInterface
{
    /** @var Token */
    protected $tokens;
    /** @var AliasStorageHelperInterface */
    protected $aliases;
    /** @var PatternTokenDependenciesManager */
    protected $manager;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = new static($configuration, $plugin_id, $plugin_definition);
        $instance->tokens = $container->get('token');
        $instance->aliases = $container->get('pathauto.alias_storage_helper');
        $instance->manager = $container->get('plugin.manager.wmpathauto_pattern_token_dependencies');

        return $instance;
    }

    protected function addDependenciesByType(string $type, array $tokens, array $data, array $options, PatternDependencyCollectionInterface $dependencies): void
    {
        $this->manager
            ->createInstance($type)
            ->addDependencies($tokens, $data, $options, $dependencies);
    }

    protected function getEntityAlias(EntityInterface $entity): ?array
    {
        try {
            $source = '/' . $entity->toUrl()->getInternalPath();
        } catch (EntityMalformedException $e) {
            return null;
        }

        $language = $entity->language()->getId();

        if ($alias = $this->aliases->loadBySource($source, $language)) {
            return $alias;
        }

        return null;
    }
}
