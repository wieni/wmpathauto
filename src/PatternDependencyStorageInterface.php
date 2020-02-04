<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;

interface PatternDependencyStorageInterface
{
    public function storeDependencies(EntityInterface $entity, PatternDependencyCollectionInterface $dependencies): void;
}
