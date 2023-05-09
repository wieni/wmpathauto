<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\wmpathauto\Entity\EntityAliasDependency;
use Drupal\wmpathauto\Plugin\QueueWorker\AliasQueueWorker;

class EntityAliasDependencyRepository implements EntityAliasDependencyRepositoryInterface
{
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var QueueFactory */
    protected $queueFactory;

    public function __construct(
        EntityTypeManagerInterface $entityTypeManager,
        QueueFactory $queueFactory
    ) {
        $this->entityTypeManager = $entityTypeManager;
        $this->queueFactory = $queueFactory;
    }

    public function addDependencies(EntityInterface $entity, EntityAliasDependencyCollectionInterface $dependencies): void
    {
        foreach ($dependencies->getConfigs() as $dependentConfig) {
            $this->addDependency($entity, EntityAliasDependencyInterface::TYPE_CONFIG, $dependentConfig->getName());
        }

        foreach ($dependencies->getEntities() as $dependentEntity) {
            $value = implode(':', [
                $dependentEntity->getEntityTypeId(),
                $dependentEntity->id(),
                $dependentEntity->language()->getId(),
            ]);

            $this->addDependency($entity, EntityAliasDependencyInterface::TYPE_ENTITY, $value);
        }
    }

    public function addDependency(EntityInterface $entity, string $type, string $value): EntityAliasDependency
    {
        $storage = $this->entityTypeManager
            ->getStorage('entity_alias_dependency');

        if ($dependency = $this->getDependency($entity, $type, $value)) {
            return $dependency;
        }

        $dependency = $storage->create([
            'entity_id' => $entity->id(),
            'entity_type' => $entity->getEntityTypeId(),
            'entity_language' => $entity->language()->getId(),
            'dependency_type' => $type,
            'dependency_value' => $value,
        ]);

        $dependency->save();

        return $dependency;
    }

    public function getDependency(EntityInterface $entity, string $type, string $value): ?EntityAliasDependency
    {
        $storage = $this->entityTypeManager
            ->getStorage('entity_alias_dependency');

        $query = $storage->getQuery()
            ->condition('entity_id', $entity->id())
            ->condition('entity_type', $entity->getEntityTypeId())
            ->condition('entity_language', $entity->language()->getId())
            ->condition('dependency_type', $type)
            ->condition('dependency_value', $value);

        $query->accessCheck(false);
        $ids = $query->execute();

        if (empty($ids)) {
            return null;
        }

        return $storage->load(reset($ids));
    }

    /** @return EntityAliasDependency[] */
    public function getDependenciesByType(string $dependencyType, string $dependencyValue): array
    {
        $storage = $this->entityTypeManager
            ->getStorage('entity_alias_dependency');

        $query = $storage->getQuery()
            ->condition('dependency_type', $dependencyType)
            ->condition('dependency_value', $dependencyValue);

        $query->accessCheck(false);
        $ids = $query->execute();

        if (empty($ids)) {
            return [];
        }

        return $storage->loadMultiple($ids);
    }

    /** @return EntityAliasDependency[] */
    public function getDependenciesByEntity(EntityInterface $entity): array
    {
        $storage = $this->entityTypeManager
            ->getStorage('entity_alias_dependency');

        $query = $storage->getQuery()
            ->condition('entity_id', $entity->id())
            ->condition('entity_type', $entity->getEntityTypeId())
            ->condition('entity_language', $entity->language()->getId());

        $query->accessCheck(false);
        $ids = $query->execute();

        if (empty($ids)) {
            return [];
        }

        return $storage->loadMultiple($ids);
    }

    public function deleteDependency(EntityInterface $entity, string $type, string $value): void
    {
        $dependency = $this->getDependency($entity, $type, $value);

        if (!$dependency) {
            return;
        }

        $dependency->delete();
    }

    public function deleteDependenciesByType(string $dependencyType, string $dependencyValue, bool $updateDependentEntityAliases = true): void
    {
        $dependencies = $this->getDependenciesByType($dependencyType, $dependencyValue);

        foreach ($dependencies as $dependency) {
            $entity = $dependency->getDependentEntity();
            $dependency->delete();

            if ($entity && $updateDependentEntityAliases) {
                $this->updateEntityAlias($entity);
            }
        }
    }

    public function deleteDependenciesByEntity(EntityInterface $entity): void
    {
        $dependencies = $this->getDependenciesByEntity($entity);

        foreach ($dependencies as $dependency) {
            $dependency->delete();
        }
    }

    public function updateEntityAliasesByType(string $dependencyType, string $dependencyValue): void
    {
        $dependencies = $this->getDependenciesByType($dependencyType, $dependencyValue);

        foreach ($dependencies as $dependency) {
            if ($dependentEntity = $dependency->getDependentEntity()) {
                $this->updateEntityAlias($dependentEntity);
            }
        }
    }

    public function updateEntityAlias(EntityInterface $entity): void
    {
        $this->queueFactory
            ->get(AliasQueueWorker::ID)
            ->createItem([
                'id' => $entity->id(),
                'type' => $entity->getEntityTypeId(),
                'language' => $entity->language()->getId(),
            ]);
    }
}
