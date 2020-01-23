<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface AliasBuilderInterface extends PluginInspectionInterface
{
    public function getAlias(string $alias, array $context): string;
}
