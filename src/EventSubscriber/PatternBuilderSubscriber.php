<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\wmpathauto\Event\PatternAlterEvent;
use Drupal\wmpathauto\PatternBuilderInterface;
use Drupal\wmpathauto\PatternBuilderManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PatternBuilderSubscriber implements EventSubscriberInterface
{
    /** @var PatternBuilderManager */
    protected $patternBuilderManager;

    public function __construct(
        PatternBuilderManager $patternBuilderManager
    ) {
        $this->patternBuilderManager = $patternBuilderManager;
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
            || !$this->patternBuilderManager->hasDefinition($pattern->id())
        ) {
            return;
        }

        /** @var PatternBuilderInterface $builder */
        $builder = $this->patternBuilderManager->createInstance($pattern->id());

        $original = $pattern->getPattern();
        $new = $builder->getPattern($original, $context);

        $pattern->setPattern($new);
    }
}
