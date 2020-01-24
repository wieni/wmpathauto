<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\wmpathauto\PatternDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependenciesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "site",
 * )
 */
class SystemSite extends PatternTokenDependenciesBase
{
    /** @var ConfigFactoryInterface */
    protected $configFactory;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->configFactory = $container->get('config.factory');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, array $options, PatternDependencyCollectionInterface $dependencies): void
    {
        foreach ($tokens as $token => $rawToken) {
            switch ($token) {
                case 'name':
                case 'slogan':
                case 'mail':
                    $dependencies->addConfig($this->configFactory->get('system.site'));
            }
        }
    }
}
