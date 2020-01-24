<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\wmpathauto\PatternDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependenciesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "node",
 * )
 *
 * @see token.api.php
 */
class Node extends PatternTokenDependenciesBase
{
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->entityTypeManager = $container->get('entity_type.manager');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, PatternDependencyCollectionInterface $dependencies): void
    {
        $entity = $data['node'];

        foreach ($tokens as $token => $rawToken) {
            if ($token === 'author') {
                $storage = $this->entityTypeManager
                    ->getStorage('user');

                $dependencies->addEntity($entity->getOwner() ?? $storage->load(0));
            }

            if ($token === 'created') {
                $storage = $this->entityTypeManager
                    ->getStorage('date_format');

                $dependencies->addEntity($storage->load('medium'));
            }
        }

        if ($createdTokens = $this->tokens->findWithPrefix($tokens, 'created')) {
            $this->addDependenciesByType('date', $createdTokens, ['date' => $entity->getCreatedTime()], $dependencies);
        }
    }
}
