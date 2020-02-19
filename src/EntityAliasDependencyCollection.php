<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityInterface;

class EntityAliasDependencyCollection implements EntityAliasDependencyCollectionInterface
{
    /** @var string[] */
    protected $aliases = [];
    /** @var EntityInterface[] */
    protected $entities = [];
    /** @var Config[] */
    protected $configs = [];

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getConfigs(): array
    {
        return $this->configs;
    }

    public function addPathAlias(string $pid): void
    {
        $this->aliases[$pid] = $pid;
    }

    public function addEntity(EntityInterface $entity): void
    {
        $key = implode('.', [
            $entity->getEntityTypeId(),
            $entity->id(),
            $entity->language()->getId(),
        ]);
        $this->entities[$key] = $entity;
    }

    public function addConfig(Config $config): void
    {
        $this->configs[$config->getName()] = $config;
    }
}
