<?php

namespace Drupal\wmpathauto\Plugin\QueueWorker;

use Drupal\Core\Annotation\QueueWorker;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\pathauto\PathautoGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *     id = \Drupal\wmpathauto\Plugin\QueueWorker\AliasQueueWorker::ID,
 *     title = @Translation("Regenerate alias for entities that depend on another alias."),
 *     cron = {"time" : 30}
 * )
 */
class AliasQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface
{
    public const ID = 'wmpathauto.alias.queue';

    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var PathautoGeneratorInterface */
    protected $aliasGenerator;
    /** @var CacheTagsInvalidatorInterface */
    protected $tagsInvalidator;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $pluginId, $pluginDefinition
    ) {
        $instance = new static($configuration, $pluginId, $pluginDefinition);
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->tagsInvalidator = $container->get('cache_tags.invalidator');
        $instance->aliasGenerator = $container->get('pathauto.generator');

        return $instance;
    }

    public function processItem($data)
    {
        $entity = $this->entityTypeManager
            ->getStorage($data['entityTypeId'])
            ->load($data['entityId']);

        if (
            !$entity instanceof ContentEntityInterface
            || !$entity->hasTranslation($data['langcode'])
        ) {
            return;
        }

        $entity = $entity->getTranslation($data['langcode']);

        $this->aliasGenerator->updateEntityAlias(
            $entity,
            'update'
        );

        $this->tagsInvalidator->invalidateTags(
            $entity->getCacheTagsToInvalidate()
        );
    }
}
