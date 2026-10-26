<?php

namespace Drupal\wmpathauto\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute counterpart of the PatternBuilder annotation.
 *
 * @see \Drupal\wmpathauto\Annotation\PatternBuilder
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PatternBuilder extends Plugin
{
}
