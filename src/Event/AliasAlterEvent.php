<?php

namespace Drupal\wmpathauto\Event;

use Drupal\Component\EventDispatcher\Event;

final class AliasAlterEvent extends Event
{
    public const NAME = 'pathauto.alias_alter';

    /** @var string */
    protected $alias;
    /** @var array */
    protected $context;

    public function __construct(string &$alias, array $context)
    {
        $this->alias = &$alias;
        $this->context = $context;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $value): void
    {
        $this->alias = $value;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
