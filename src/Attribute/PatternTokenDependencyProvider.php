<?php

namespace Drupal\wmpathauto\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute counterpart of the PatternTokenDependencyProvider annotation.
 *
 * Like the annotation, the token type is the plugin ID.
 *
 * @see \Drupal\wmpathauto\Annotation\PatternTokenDependencyProvider
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PatternTokenDependencyProvider extends Plugin
{
    public function __construct(
        public readonly string $type,
        ?string $deriver = null
    ) {
        parent::__construct($type, $deriver);
    }
}
