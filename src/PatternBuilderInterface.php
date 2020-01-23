<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface PatternBuilderInterface extends PluginInspectionInterface
{
    public function getPattern(string $original, array $context): string;
}
