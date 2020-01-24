<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\wmpathauto\PatternDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependenciesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "url",
 * )
 */
class Url extends PatternTokenDependenciesBase
{
    /** @var LanguageManagerInterface */
    protected $languageManager;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->languageManager = $container->get('language_manager');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, array $options, PatternDependencyCollectionInterface $dependencies): void
    {
        $url = $data['url'];
        $path = $this->getPathFromUrl($url);
        $langcode = $options['langcode'] ?? $this->languageManager->getCurrentLanguage()->getId();

        foreach ($tokens as $name => $original) {
            if ($name === 'path' && $alias = $this->aliases->loadBySource($path, $langcode)) {
                $dependencies->addPathAlias($alias['pid']);
            }
        }
    }

    protected function getPathFromUrl(\Drupal\Core\Url $url): string
    {
        $path = '/';

        // Ensure the URL is routed to avoid throwing an exception.
        if ($url->isRouted()) {
            $path .= (clone $url)
                ->setAbsolute(false)
                ->setOption('fragment', null)
                ->getInternalPath();
        }

        return $path;
    }
}