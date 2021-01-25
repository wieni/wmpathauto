<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencyProvider;

use Drupal\wmpathauto\Annotation\PatternTokenDependencyProvider;
use Drupal\wmpathauto\EntityAliasDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencyProvider(
 *     type = "wmsingles",
 * )
 */
class WmSingles extends PatternTokenDependencyProviderBase
{
    /** @var \Drupal\wmsingles\Service\WmSingles */
    protected $wmSingles;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

        if ($container->has('wmsingles')) {
            $instance->wmSingles = $container->get('wmsingles');
        }

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, array $options, EntityAliasDependencyCollectionInterface $dependencies): void
    {
        if (!$this->wmSingles) {
            return;
        }

        foreach ($tokens as $token => $rawToken) {
            [$entityTypeId, $tokenName] = explode(':', $token);

            if ($tokenName === 'url') {
                $single = $this->wmSingles->getSingleByBundle($entityTypeId);

                if ($alias = $this->getPathAliasByEntity($single)) {
                    $dependencies->addEntity($alias);
                }
            }
        }
    }
}
