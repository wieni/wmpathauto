<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\wmpathauto\Annotation\PatternTokenDependencyProvider;
use Drupal\wmpathauto\EntityAliasDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencyProvider(
 *     type = "site",
 * )
 */
class SystemSite extends PatternTokenDependencyProviderBase
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

    public function addDependencies(array $tokens, array $data, array $options, EntityAliasDependencyCollectionInterface $dependencies): void
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
