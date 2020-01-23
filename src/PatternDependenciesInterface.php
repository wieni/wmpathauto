<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;

interface PatternDependenciesInterface extends PluginInspectionInterface
{
    public function addDependencies(array &$dependencies, EntityInterface $entity): void;
}
