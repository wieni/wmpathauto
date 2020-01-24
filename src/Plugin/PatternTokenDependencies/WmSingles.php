<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\wmpathauto\PatternDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependenciesBase;
use Drupal\wmsingles\Service\WmSingles as WmSinglesService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "wmsingles",
 * )
 */
class WmSingles extends PatternTokenDependenciesBase
{
    /** @var WmSinglesService */
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

    public function addDependencies(array $tokens, array $data, array $options, PatternDependencyCollectionInterface $dependencies): void
    {
        if (!$this->wmSingles) {
            return;
        }

        foreach ($tokens as $token => $rawToken) {
            [$entityTypeId, $tokenName] = explode(':', $token);

            if ($tokenName === 'url') {
                $single = $this->wmSingles->getSingleByBundle($entityTypeId);

                if ($alias = $this->getEntityAlias($single)) {
                    $dependencies->addPathAlias($alias['pid']);
                }
            }
        }
    }
}
