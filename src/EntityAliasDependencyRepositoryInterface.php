<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;
use Drupal\wmpathauto\Entity\EntityAliasDependency;

interface EntityAliasDependencyRepositoryInterface
{
    public function addDependencies(EntityInterface $entity, EntityAliasDependencyCollectionInterface $dependencies): void;

    public function addDependency(EntityInterface $entity, string $type, string $value): EntityAliasDependency;

    public function getDependency(EntityInterface $entity, string $type, string $value): ?EntityAliasDependency;

    public function getDependenciesByType(string $dependencyType, string $dependencyValue): array;

    public function getDependenciesByEntity(EntityInterface $entity): array;

    public function deleteDependency(EntityInterface $entity, string $type, string $value): void;

    public function deleteDependenciesByType(string $dependencyType, string $dependencyValue, bool $updateDependentEntityAliases = true): void;

    public function deleteDependenciesByEntity(EntityInterface $entity): void;

    public function updateEntityAliasesByType(string $dependencyType, string $dependencyValue): void;

    public function updateEntityAlias(EntityInterface $entity): void;
}
