<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface PatternTokenDependenciesInterface extends PluginInspectionInterface
{
    public function addDependencies(string $token, string $value, array &$dependencies): void;
}
