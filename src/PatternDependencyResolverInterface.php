<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;
use Drupal\pathauto\PathautoPatternInterface;

interface PatternDependencyResolverInterface
{
    /**
     * Collect dependencies of a pathauto pattern generated for a certain entity.
     *
     * @return array
     *      An array of pids (path alias identifiers)
     */
    public function getDependencies(PathautoPatternInterface $pattern, EntityInterface $entity): array;
}
