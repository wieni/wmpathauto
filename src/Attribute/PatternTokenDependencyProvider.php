<?php

namespace Drupal\wmpathauto\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute counterpart of the PatternTokenDependencyProvider annotation.
 *
 * Drupal 10.2+. Like the annotation, the token type is the plugin ID.
 *
 * @see \Drupal\wmpathauto\Annotation\PatternTokenDependencyProvider
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PatternTokenDependencyProvider extends Plugin
{
    /** @var string */
    public $type;

    public function __construct(string $type, ?string $deriver = null)
    {
        parent::__construct($type, $deriver);
        $this->type = $type;
    }
}
