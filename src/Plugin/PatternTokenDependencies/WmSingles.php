<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wmpathauto\PatternTokenDependenciesInterface;
use Drupal\wmsingles\Service\WmSingles as WmSinglesService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "wmsingles",
 * )
 */
class WmSingles extends PluginBase implements PatternTokenDependenciesInterface, ContainerFactoryPluginInterface
{
    /** @var WmSinglesService */
    protected $wmSingles;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = new static($configuration, $plugin_id, $plugin_definition);

        if ($container->has('wmsingles')) {
            $instance->wmSingles = $container->get('wmsingles');
        }

        return $instance;
    }

    public function addDependencies(string $token, string $value, array &$dependencies): void
    {
        if (!$this->wmSingles) {
            return;
        }

        [$entityTypeId, $tokenName] = explode(':', $token);

        if ($tokenName !== 'url') {
            return;
        }

        $dependencies['entities'][] = $this->wmSingles->getSingleByBundle($entityTypeId);
    }
}
