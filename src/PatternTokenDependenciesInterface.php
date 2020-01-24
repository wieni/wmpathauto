<?php

namespace Drupal\wmpathauto;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface PatternTokenDependenciesInterface extends PluginInspectionInterface
{
    public function addDependencies(array $tokens, array $data, PatternDependencyCollectionInterface $dependencies): void;
}
