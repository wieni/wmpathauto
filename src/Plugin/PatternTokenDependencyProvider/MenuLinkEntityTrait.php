<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * @property EntityTypeManagerInterface $entityTypeManager
 * @property RouteProviderInterface $routeProvider
 */
trait MenuLinkEntityTrait
{
    protected function getMenuLinkEntity($menuLink, ?string $langcode = null): ?MenuLinkContentInterface
    {
        if ($menuLink instanceof MenuLinkContent) {
            $metadata = $menuLink->getPluginDefinition()['metadata'];
        }

        if ($menuLink instanceof MenuLinkInterface || $menuLink->getProvider() !== 'menu_link_content') {
            $metadata = $menuLink->getMetaData();
        }

        if (!isset($metadata['entity_id'])) {
            return null;
        }

        $entity = $this->entityTypeManager
            ->getStorage('menu_link_content')
            ->load($metadata['entity_id']);

        if ($langcode && $entity->isTranslatable() && $entity->hasTranslation($langcode)) {
            return $entity->getTranslation($langcode);
        }

        return $entity;
    }

    protected function getReferencedEntity($menuLink, ?string $langcode = null): ?EntityInterface
    {
        $routeName = null;
        $routeParameters = [];

        if ($menuLink instanceof MenuLinkInterface) {
            $routeName = $menuLink->getRouteName();
            $routeParameters = $menuLink->getRouteParameters();
        }

        if ($menuLink instanceof MenuLinkContentInterface) {
            $url = $menuLink->getUrlObject();

            if ($url->isRouted()) {
                $routeName = $url->getRouteName();
                $routeParameters = $url->getRouteParameters();
            }
        }

        if (!$routeName) {
            return null;
        }

        try {
            $route = $this->routeProvider->getRouteByName($routeName);
        } catch (RouteNotFoundException $e) {
            return null;
        }

        $entityTypeId = null;
        $entityId = null;

        if ($entityForm = $route->getDefault('_entity_form')) {
            [$entityTypeId] = explode('.', $entityForm);
            $entityId = $routeParameters[$entityTypeId];
        } elseif ($entityAccess = $route->getRequirement('_entity_access')) {
            [$entityTypeId] = explode('.', $entityAccess);
            $entityId = $routeParameters[$entityTypeId];
        }

        if ($entityTypeId && $entityId) {
            $entity = $this->entityTypeManager
                ->getStorage($entityTypeId)
                ->load($entityId);

            if ($langcode) {
                $entity = $this->getTranslation($entity, $langcode);
            }

            return $entity;
        }

        return null;
    }

    protected function getTranslation(EntityInterface $entity, string $langcode): ?EntityInterface
    {
        if ($entity->language()->getId() === $langcode) {
            return $entity;
        }

        if ($entity->hasTranslation($langcode)) {
            return $entity->getTranslation($langcode);
        }

        return $entity;
    }
}
