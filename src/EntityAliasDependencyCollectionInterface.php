<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityInterface;

interface EntityAliasDependencyCollectionInterface
{
    public function getEntities(): array;

    public function getConfigs(): array;

    public function addEntity(EntityInterface $entity): void;

    public function addConfig(Config $config): void;
}
