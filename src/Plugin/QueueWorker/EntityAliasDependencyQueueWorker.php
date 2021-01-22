<?php

namespace Drupal\wmpathauto\Plugin\QueueWorker;

use Drupal\Core\Annotation\QueueWorker;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\wmpathauto\EntityAliasDependencyRepositoryInterface;
use Drupal\wmpathauto\EntityAliasDependencyResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *     id = \Drupal\wmpathauto\Plugin\QueueWorker\EntityAliasDependencyQueueWorker::ID,
 *     title = @Translation("Calculate dependencies of entity aliases."),
 *     cron = {"time" : 30}
 * )
 */
class EntityAliasDependencyQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface
{
    public const ID = 'wmpathauto.entity_alias_dependency.queue';

    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var EntityAliasDependencyResolverInterface */
    protected $resolver;
    /** @var EntityAliasDependencyRepositoryInterface */
    protected $repository;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $pluginId, $pluginDefinition
    ) {
        $instance = new static($configuration, $pluginId, $pluginDefinition);
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->resolver = $container->get('wmpathauto.entity_alias_dependency.resolver');
        $instance->repository = $container->get('wmpathauto.entity_alias_dependency.repository');

        return $instance;
    }

    public function processItem($data)
    {
        $entity = $this->entityTypeManager
            ->getStorage($data['type'])
            ->load($data['id']);

        if (
            !$entity instanceof ContentEntityInterface
            || !$entity->hasTranslation($data['language'])
        ) {
            return;
        }

        $entity = $entity->getTranslation($data['language']);
        $dependencies = $this->resolver->getDependencies($entity);
        $this->repository->addDependencies($entity, $dependencies);
    }
}
