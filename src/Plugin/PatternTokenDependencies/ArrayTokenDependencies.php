<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\wmpathauto\PatternDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependenciesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "array",
 * )
 */
class ArrayTokenDependencies extends PatternTokenDependenciesBase
{
    use MenuLinkEntityTrait;

    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var LanguageManagerInterface */
    protected $languageManager;
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
        $instance->routeProvider = $container->get('router.route_provider');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, array $options, PatternDependencyCollectionInterface $dependencies): void
    {
        $array = $data['array'];
        $langcode = $options['langcode'] ?? $this->languageManager->getCurrentLanguage()->getId();

        foreach ($tokens as $name => $original) {
            if ($name === 'join-path') {
                foreach ($array as $item) {
                    if ($linkEntity = $this->getMenuLinkEntity($item, $langcode)) {
                        $dependencies->addEntity($linkEntity);

                        $referencedEntity = $this->getReferencedEntity($item, $langcode);
                        if ($referencedEntity && $alias = $this->getEntityAlias($referencedEntity)) {
                            $dependencies->addPathAlias($alias['pid']);
                        }
                    }
                }
            }
        }
    }
}
