<?php

namespace Drupal\wmpathauto\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute counterpart of the AliasBuilder annotation.
 *
 * @see \Drupal\wmpathauto\Annotation\AliasBuilder
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AliasBuilder extends Plugin
{
}
