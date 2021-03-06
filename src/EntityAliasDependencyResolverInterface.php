<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;

interface EntityAliasDependencyResolverInterface
{
    /** Collect dependencies of a pathauto pattern generated for a certain entity. */
    public function getDependencies(EntityInterface $entity): EntityAliasDependencyCollectionInterface;
}
