<?php

namespace Drupal\wmpathauto\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute counterpart of the EntityAliasDependencyProvider annotation.
 *
 * @see \Drupal\wmpathauto\Annotation\EntityAliasDependencyProvider
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class EntityAliasDependencyProvider extends Plugin
{
}
