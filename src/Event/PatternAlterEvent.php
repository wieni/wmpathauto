<?php

namespace Drupal\wmpathauto\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\pathauto\PathautoPatternInterface;

final class PatternAlterEvent extends Event
{
    public const NAME = 'pathauto.pattern_alter';

    /** @var PathautoPatternInterface */
    protected $pattern;
    /** @var array */
    protected $context;

    public function __construct(PathautoPatternInterface $pattern, array $context)
    {
        $this->pattern = $pattern;
        $this->context = $context;
    }

    public function getPattern(): PathautoPatternInterface
    {
        return $this->pattern;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
