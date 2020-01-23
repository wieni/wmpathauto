<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\wmpathauto\Event\PatternAlterEvent;
use Drupal\wmpathauto\PatternDependencyResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PatternDependenciesSubscriber implements EventSubscriberInterface
{
    /** @var KeyValueFactoryInterface */
    protected $keyValueFactory;
    /** @var PatternDependencyResolverInterface */
    protected $dependencyResolver;

    public function __construct(
        KeyValueFactoryInterface $keyValueFactory,
        PatternDependencyResolverInterface $dependencyResolver
    ) {
        $this->keyValueFactory = $keyValueFactory;
        $this->dependencyResolver = $dependencyResolver;
    }

    public static function getSubscribedEvents()
    {
        $events[PatternAlterEvent::NAME][] = ['onPatternAlter'];

        return $events;
    }

    public function onPatternAlter(PatternAlterEvent $event): void
    {
        $context = $event->getContext();
        $entity = $context['data']['node'] ?? $context['data']['term'] ?? null;
        $pattern = $event->getPattern();

        if (
            !$entity instanceof ContentEntityInterface
            || !$pattern instanceof PathautoPatternInterface
        ) {
            return;
        }

        $dependencies = $this->dependencyResolver->getDependencies($pattern, $entity);

        foreach ($dependencies as $pid) {
            $storage = $this->getStorage('pid:' . $pid);

            $cid = sprintf(
                '%s.%s.%s',
                $entity->getEntityTypeId(),
                $entity->id(),
                $entity->language()->getId()
            );

            if ($storage->has($cid)) {
                continue;
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
    }

    protected function getStorage(string $suffix): KeyValueStoreInterface
    {
        return $this->keyValueFactory->get(
            'wmpathauto.custom.alias.dependencies.' . $suffix
        );
    }
}
