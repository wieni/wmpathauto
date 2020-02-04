<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;

class PatternDependencyStorage implements PatternDependencyStorageInterface
{
    /** @var KeyValueFactoryInterface */
    protected $keyValueFactory;

    public function __construct(
        KeyValueFactoryInterface $keyValueFactory
    ) {
        $this->keyValueFactory = $keyValueFactory;
    }

    public function storeDependencies(EntityInterface $entity, PatternDependencyCollectionInterface $dependencies): void
    {
        foreach ($dependencies->getAliases() as $pid) {
            $suffix = implode(':', ['pid', $pid]);
            $this->addEntityAsDependency($entity, $suffix);
        }

        foreach ($dependencies->getConfigs() as $dependentConfig) {
            $suffix = implode(':', [
                'config',
                $dependentConfig->getName(),
            ]);
            $this->addEntityAsDependency($entity, $suffix);
        }

        foreach ($dependencies->getEntities() as $dependentEntity) {
            $suffix = implode(':', [
                'entity',
                $dependentEntity->getEntityTypeId(),
                $dependentEntity->id(),
            ]);
            $this->addEntityAsDependency($entity, $suffix);
        }
    }

    protected function addEntityAsDependency(EntityInterface $entity, string $suffix): void
    {
        $storage = $this->getStorage($suffix);

        $cid = sprintf(
            '%s.%s.%s',
            $entity->getEntityTypeId(),
            $entity->id(),
            $entity->language()->getId()
        );

        if ($storage->has($cid)) {
            return;
        }

        $storage->set(
            $cid,
            [
                'entityTypeId' => $entity->getEntityTypeId(),
                'entityId' => $entity->id(),
                'langcode' => $entity->language()->getId(),
            ]
        );
    }

    protected function getStorage(string $suffix): KeyValueStoreInterface
    {
        return $this->keyValueFactory->get(
            'wmpathauto.dependencies.' . $suffix
        );
    }
}
