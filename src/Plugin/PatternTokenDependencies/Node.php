<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\node\NodeInterface;
use Drupal\wmpathauto\EntityAliasDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "node",
 * )
 *
 * @see hook_tokens
 * @see menu_ui_tokens
 */
class Node extends PatternTokenDependencyProviderBase
{
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var MenuLinkManagerInterface */
    protected $menuLinkManager;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->menuLinkManager = $container->get('plugin.manager.menu.link');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, array $options, EntityAliasDependencyCollectionInterface $dependencies): void
    {
        $node = $data['node'];
        $link = $this->getMenuLinkByNode($node);

        foreach ($tokens as $token => $rawToken) {
            if ($token === 'author') {
                $storage = $this->entityTypeManager
                    ->getStorage('user');

                $dependencies->addEntity($node->getOwner() ?? $storage->load(0));
            }

            if ($token === 'created') {
                $storage = $this->entityTypeManager
                    ->getStorage('date_format');

                $dependencies->addEntity($storage->load('medium'));
            }

            if ($link && $token === 'menu-link') {
                $this->addDependenciesByType('menu-link', ['menu-link:title'], ['menu-link' => $link], $options, $dependencies);
            }
        }

        if ($createdTokens = $this->tokens->findWithPrefix($tokens, 'created')) {
            $this->addDependenciesByType('date', $createdTokens, ['date' => $node->getCreatedTime()], $options, $dependencies);
        }

        if ($link && $menuTokens = $this->tokens->findWithPrefix($tokens, 'menu-link')) {
            $this->addDependenciesByType('menu-link', $menuTokens, ['menu-link' => $link], $options, $dependencies);
        }

        $tokenData = [
            'entity_type' => 'node',
            'entity' => $node,
            'token_type' => 'node',
        ];

        $this->addDependenciesByType('entity', $tokens, $tokenData, $options, $dependencies);
    }

    /** @return MenuLinkInterface|MenuLinkContentInterface|null */
    protected function getMenuLinkByNode(NodeInterface $node)
    {
        if ($node->getFieldDefinition('menu_link') && $menuLink = $node->menu_link->entity) {
            return $menuLink;
        }

        $url = $node->toUrl();
        $links = $this->menuLinkManager->loadLinksByRoute($url->getRouteName(), $url->getRouteParameters());

        if (empty($links)) {
            return null;
        }

        module_load_include('inc', 'token', 'token.tokens');

        return _token_menu_link_best_match($node, $links);
    }
}
