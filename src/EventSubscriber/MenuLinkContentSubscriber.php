<?php

namespace Drupal\wmpathauto\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\hook_event_dispatcher\Event\Entity\BaseEntityEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\wmpathauto\EntityAliasDependencyRepositoryInterface;
use Drupal\wmpathauto\EntityAliasDependencyResolverInterface;
use Drupal\wmpathauto\Plugin\PatternTokenDependencies\MenuLinkEntityTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuLinkContentSubscriber implements EventSubscriberInterface
{
    use MenuLinkEntityTrait;

    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var RouteProviderInterface */
    protected $routeProvider;
    /** @var EntityAliasDependencyResolverInterface */
    protected $resolver;
    /** @var EntityAliasDependencyRepositoryInterface */
    protected $repository;

    public function __construct(
        EntityTypeManagerInterface $entityTypeManager,
        RouteProviderInterface $routeProvider,
        EntityAliasDependencyResolverInterface $resolver,
        EntityAliasDependencyRepositoryInterface $repository
    ) {
        $this->entityTypeManager = $entityTypeManager;
        $this->routeProvider = $routeProvider;
        $this->resolver = $resolver;
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        $events[HookEventDispatcherInterface::ENTITY_INSERT][] = ['onMenuLinkUpdate'];
        /** Make sure this runs before \Drupal\wmpathauto\EventSubscriber\DependencyUpdateSubscriber::onEntityUpdate */
        $events[HookEventDispatcherInterface::ENTITY_UPDATE][] = ['onMenuLinkUpdate', 100];

        return $events;
    }

    public function onMenuLinkUpdate(BaseEntityEvent $event): void
    {
        $menuLink = $event->getEntity();

        if (!$menuLink instanceof \Drupal\menu_link_content\MenuLinkContentInterface) {
            return;
        }

        if (!$referencedEntity = $this->getReferencedEntity($menuLink)) {
            return;
        }

        // Re-resolve dependencies from this entity's pathauto pattern
        // since the menu link tree might have changed.
        $dependencies = $this->resolver->getDependencies($referencedEntity);
        $this->repository->addDependencies($referencedEntity, $dependencies);
    }
}
