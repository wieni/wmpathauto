<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class PatternTokenDependenciesBase extends PluginBase implements PatternTokenDependenciesInterface, ContainerFactoryPluginInterface
{
    /** @var Token */
    protected $tokens;
    /** @var PatternTokenDependenciesManager */
    protected $manager;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = new static($configuration, $plugin_id, $plugin_definition);
        $instance->tokens = $container->get('token');
        $instance->manager = $container->get('plugin.manager.wmpathauto_pattern_token_dependencies');

        return $instance;
    }

    protected function addDependenciesByType(string $type, array $tokens, array $data, PatternDependencyCollectionInterface $dependencies): void
    {
        $this->manager
            ->createInstance($type)
            ->addDependencies($tokens, $data, $dependencies);
    }
}
