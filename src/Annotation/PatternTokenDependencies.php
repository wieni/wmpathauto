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
    /** @var string */
    public $token;

    public function getId()
    {
        if (isset($this->definition['token'])) {
            return implode(':', [
                $this->definition['type'],
                $this->definition['token'],
            ]);
        }

        return $this->definition['type'];
    }
}
