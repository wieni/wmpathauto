<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\hook_event_dispatcher\Event\Entity\BaseEntityEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\wmpathauto\Event\PatternAlterEvent;
use Drupal\wmpathauto\PatternDependencyCollection;
use Drupal\wmpathauto\PatternDependencyResolverInterface;
use Drupal\wmpathauto\PatternDependencyStorageInterface;
use Drupal\wmpathauto\Plugin\PatternTokenDependencies\MenuLinkEntityTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PatternDependenciesSubscriber implements EventSubscriberInterface
{
    use MenuLinkEntityTrait;

    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var RouteProviderInterface */
    protected $routeProvider;
    /** @var PatternDependencyResolverInterface */
    protected $resolver;
    /** @var PatternDependencyStorageInterface */
    protected $storage;

    public function __construct(
        EntityTypeManagerInterface $entityTypeManager,
        RouteProviderInterface $routeProvider,
        PatternDependencyResolverInterface $resolver,
        PatternDependencyStorageInterface $storage
    ) {
        $this->entityTypeManager = $entityTypeManager;
        $this->routeProvider = $routeProvider;
        $this->resolver = $resolver;
        $this->storage = $storage;
    }

    public static function getSubscribedEvents()
    {
        $events[PatternAlterEvent::NAME][] = ['onPatternAlter'];
        $events[HookEventDispatcherInterface::ENTITY_INSERT][] = ['onMenuLinkCreate'];

        return $events;
    }

    /** Add new menu links as dependencies of their referenced entities */
    public function onMenuLinkCreate(BaseEntityEvent $event): void
    {
        $menuLink = $event->getEntity();

        if (!$menuLink instanceof MenuLinkContentInterface) {
            return;
        }

        if (!$referencedEntity = $this->getReferencedEntity($menuLink)) {
            return;
        }

        $dependencies = new PatternDependencyCollection;
        $dependencies->addEntity($menuLink);

        $this->storage->storeDependencies($referencedEntity, $dependencies);
    }

    /** Collect dependencies when generating the entity alias */
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

        $dependencies = $this->resolver->getDependencies($pattern, $entity);
        $this->storage->storeDependencies($entity, $dependencies);
    }
}
