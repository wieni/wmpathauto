<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\wmpathauto\AliasBuilderInterface;
use Drupal\wmpathauto\AliasBuilderManager;
use Drupal\wmpathauto\Event\AliasAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AliasBuilderSubscriber implements EventSubscriberInterface
{
    /** @var AliasBuilderManager */
    protected $aliasBuilderManager;

    public function __construct(
        AliasBuilderManager $aliasBuilderManager
    ) {
        $this->aliasBuilderManager = $aliasBuilderManager;
    }

    public static function getSubscribedEvents()
    {
        $events[AliasAlterEvent::NAME][] = ['onAliasAlter'];

        return $events;
    }

    public function onAliasAlter(AliasAlterEvent $event): void
    {
        $context = $event->getContext();
        $entity = $context['data']['node'] ?? $context['data']['term'] ?? null;
        $pattern = $context['pattern'];

        if (
            !$entity instanceof ContentEntityInterface
            || !$pattern instanceof PathautoPatternInterface
            || !$this->aliasBuilderManager->hasDefinition($pattern->id())
        ) {
            return;
        }

        /** @var AliasBuilderInterface $builder */
        $builder = $this->aliasBuilderManager->createInstance($pattern->id());

        $newAlias = $builder->getAlias($event->getAlias(), $event->getContext());
        $event->setAlias($newAlias);
    }
}
