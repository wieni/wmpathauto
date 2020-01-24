<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Drupal\wmpathauto\PatternDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependenciesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "menu-link",
 * )
 */
class MenuLink extends PatternTokenDependenciesBase
{
    use MenuLinkEntityTrait;

    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var LanguageManagerInterface */
    protected $languageManager;
    /** @var MenuLinkManagerInterface */
    protected $menuLinkManager;
    /** @var RouteProviderInterface */
    protected $routeProvider;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->languageManager = $container->get('language_manager');
        $instance->menuLinkManager = $container->get('plugin.manager.menu.link');
        $instance->routeProvider = $container->get('router.route_provider');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, array $options, PatternDependencyCollectionInterface $dependencies): void
    {
        $langcode = $options['langcode'] ?? $this->languageManager->getCurrentLanguage()->getId();

        if (!$linkPlugin = $this->getMenuLinkFromData($data)) {
            return;
        }

        foreach ($tokens as $name => $original) {
            if ($name === 'id') {
                continue;
            }

            if ($name === 'title') {
                $linkEntity = $this->getMenuLinkEntity($linkPlugin, $langcode);
                $dependencies->addEntity($linkEntity);
            }

            if ($name === 'url') {
                $linkEntity = $this->getMenuLinkEntity($linkPlugin, $langcode);
                $dependencies->addEntity($linkEntity);

                $referencedEntity = $this->getReferencedEntity($linkPlugin, $langcode);
                if ($referencedEntity && $alias = $this->getEntityAlias($referencedEntity)) {
                    $dependencies->addPathAlias($alias['pid']);
                }
            }

            if ($name === 'parent' && $parentId = $linkPlugin->getParent()) {
                $this->addDependenciesByType('menu-link', ['menu-link:title' => null], ['menu-link' => $this->getMenuLink($parentId)], $options, $dependencies);
            }

            if ($name === 'parents') {
                foreach ($this->getMenuLinkParents($linkPlugin) as $parent) {
                    $this->addDependenciesByType('menu-link', ['menu-link:title' => null], ['menu-link' => $parent], $options, $dependencies);
                }
            }

            if ($name === 'root' && $parents = $this->getMenuLinkParents($linkPlugin)) {
                $this->addDependenciesByType('menu-link', ['menu-link:title' => null], ['menu-link' => array_shift($parents)], $options, $dependencies);
            }
        }

        if ($parentId = $linkPlugin->getParent()) {
            if ($parentTokens = $this->tokens->findWithPrefix($tokens, 'parent')) {
                $this->addDependenciesByType('menu-link', $parentTokens, ['menu-link' => $this->getMenuLink($parentId)], $options, $dependencies);
            }

            if ($rootTokens = $this->tokens->findWithPrefix($tokens, 'root')) {
                if ($parents = $this->getMenuLinkParents($linkPlugin)) {
                    $this->addDependenciesByType('menu-link', $rootTokens, ['menu-link' => array_shift($parents)], $options, $dependencies);
                }
            }
        }

        if ($parentsTokens = $this->tokens->findWithPrefix($tokens, 'parents')) {
            if ($parents = $this->getMenuLinkParents($linkPlugin)) {
                $this->addDependenciesByType('array', $parentsTokens, ['array' => $parents], $options, $dependencies);
            }
        }

        if ($urlTokens = $this->tokens->findWithPrefix($tokens, 'url')) {
            $this->addDependenciesByType('url', $urlTokens, ['url' => $linkPlugin->getUrlObject()], $options, $dependencies);
        }
    }

    protected function getMenuLinkFromData(array $data): ?MenuLinkInterface
    {
        $link = $data['menu-link'];

        if ($link instanceof MenuLinkInterface) {
            return $link;
        }

        if ($link instanceof MenuLinkContentInterface) {
            return $this->menuLinkManager->createInstance($link->getPluginId());
        }

        return null;
    }

    protected function getMenuLinkParents(MenuLinkInterface $menuLink): array
    {
        $parentIds = $this->menuLinkManager->getParentIds($menuLink->getPluginId());
        unset($parentIds[$menuLink->getPluginId()]);

        return array_map(
            function (string $parentId) {
                return $this->getMenuLink($parentId);
            },
            $parentIds
        );
    }

    protected function getMenuLink(string $pluginId): MenuLinkContent
    {
        return $this->menuLinkManager->createInstance($pluginId);
    }
}
