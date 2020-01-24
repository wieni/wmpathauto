<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\wmpathauto\PatternDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependenciesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "date",
 * )
 */
class SystemDate extends PatternTokenDependenciesBase
{
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = new static($configuration, $plugin_id, $plugin_definition);
        $instance->entityTypeManager = $container->get('entity_type.manager');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, PatternDependencyCollectionInterface $dependencies): void
    {
        foreach ($tokens as $token => $rawToken) {
            switch ($token) {
                case 'short':
                case 'medium':
                case 'long':
                    $dateFormat = $this->entityTypeManager
                        ->getStorage('date_format')
                        ->load($token);
                    $dependencies->addEntity($dateFormat);
            }
        }
    }
}
