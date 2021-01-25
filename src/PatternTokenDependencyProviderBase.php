<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto\AliasStorageHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class PatternTokenDependencyProviderBase extends PluginBase implements PatternTokenDependencyProviderInterface, ContainerFactoryPluginInterface
{
    /** @var Token */
    protected $tokens;
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var AliasStorageHelperInterface */
    protected $aliases;
    /** @var PatternTokenDependencyProviderManager */
    protected $manager;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = new static($configuration, $plugin_id, $plugin_definition);
        $instance->tokens = $container->get('token');
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->aliases = $container->get('pathauto.alias_storage_helper');
        $instance->manager = $container->get('plugin.manager.pattern_token_dependency_provider');

        return $instance;
    }

    protected function addDependenciesByType(string $type, array $tokens, array $data, array $options, EntityAliasDependencyCollectionInterface $dependencies): void
    {
        $this->manager
            ->createInstance($type)
            ->addDependencies($tokens, $data, $options, $dependencies);
    }

    protected function getPathAliasByEntity(EntityInterface $entity): ?PathAliasInterface
    {
        try {
            return $this->getPathAlias(
                '/' . $entity->toUrl()->getInternalPath(),
                $entity->language()->getId()
            );
        } catch (EntityMalformedException $e) {
            return null;
        }
    }

    protected function getPathAlias(string $path, string $langcode): ?PathAliasInterface
    {
        $entities = $this->entityTypeManager
            ->getStorage('path_alias')
            ->loadByProperties([
                'langcode' => $langcode,
                'path' => $path,
            ]);

        return array_pop($entities);
    }
}
