<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

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
    protected function getMenuLinkEntity($menuLink, string $langcode): ?MenuLinkContentInterface
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

        if ($entity->isTranslatable() && $entity->hasTranslation($langcode)) {
            return $entity->getTranslation($langcode);
        }

        return $entity;
    }

    protected function getReferencedEntity(MenuLinkInterface $menuLink, string $langcode): ?EntityInterface
    {
        $routeName = $menuLink->getRouteName();
        $routeParameters = $menuLink->getRouteParameters();

        try {
            $route = $this->routeProvider->getRouteByName($routeName);
        } catch (RouteNotFoundException $e) {
            return null;
        }

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

            return $this->getTranslation($entity, $langcode);
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
