<?php

namespace Drupal\wmpathauto\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class PatternTokenDependencies extends Plugin
{
    /** @var string */
    public $type;

    public function getId()
    {
        return $this->definition['type'];
    }
}
