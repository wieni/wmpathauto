<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;

interface EntityAliasDependencyProviderInterface extends PluginInspectionInterface
{
    public function addDependencies(EntityAliasDependencyCollectionInterface $dependencies, EntityInterface $entity): void;
}
