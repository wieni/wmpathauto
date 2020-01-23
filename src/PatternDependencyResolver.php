<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Utility\Token;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\pathauto\AliasStorageHelperInterface;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\token\TokenEntityMapperInterface;

class PatternDependencyResolver implements PatternDependencyResolverInterface
{
    /** @var Token */
    protected $token;
    /** @var TokenEntityMapperInterface */
    protected $tokenEntityMapper;
    /** @var AliasCleanerInterface */
    protected $aliasCleaner;
    /** @var AliasStorageHelperInterface */
    protected $aliasStorageHelper;
    /** @var AliasBuilderManager */
    protected $aliasBuilderManager;
    /** @var PatternBuilderManager */
    protected $patternBuilderManager;
    /** @var PatternDependenciesManager */
    protected $patternDependenciesManager;
    /** @var PatternTokenDependenciesManager */
    protected $patternTokenDependenciesManager;

    public function __construct(
        Token $token,
        TokenEntityMapperInterface $tokenEntityMapper,
        AliasCleanerInterface $aliasCleaner,
        AliasStorageHelperInterface $aliasStorageHelper,
        AliasBuilderManager $aliasBuilderManager,
        PatternBuilderManager $patternBuilderManager,
        PatternDependenciesManager $patternDependenciesManager,
        PatternTokenDependenciesManager $patternTokenDependenciesManager
    ) {
        $this->token = $token;
        $this->tokenEntityMapper = $tokenEntityMapper;
        $this->aliasCleaner = $aliasCleaner;
        $this->aliasStorageHelper = $aliasStorageHelper;
        $this->aliasBuilderManager = $aliasBuilderManager;
        $this->patternBuilderManager = $patternBuilderManager;
        $this->patternDependenciesManager = $patternDependenciesManager;
        $this->patternTokenDependenciesManager = $patternTokenDependenciesManager;
    }

    public function getDependencies(PathautoPatternInterface $pattern, EntityInterface $entity): array
    {
        $dependencies = [
            'aliases' => [],
            'entities' => [],
        ];

        $this->addDependenciesFromTokens($dependencies, $pattern, $entity);
        $this->addDependenciesFromPlugins($dependencies, $pattern, $entity);

        foreach ($dependencies['entities'] as $i => $dependantEntity) {
            if ($dependantEntity instanceof EntityInterface) {
                $source = '/' . $dependantEntity->toUrl()->getInternalPath();
                $language = $dependantEntity->language()->getId();
                $alias = $this->aliasStorageHelper->loadBySource($source, $language);

                if ($alias) {
                    $dependencies['aliases'][] = $alias['pid'];
                }
            }

            unset($dependencies['entities'][$i]);
        }

        return $dependencies['aliases'];
    }

    protected function addDependenciesFromTokens(array &$dependencies, PathautoPatternInterface $pattern, EntityInterface $entity): void
    {
        $allTokens = $this->token->scan($pattern->getPattern());
        $entityTokens = [
            $this->tokenEntityMapper->getTokenTypeForEntityType($entity->getEntityTypeId()) => $entity,
        ];
        $tokensByType = array_diff_key($allTokens, $entityTokens);

        $options = [
            'callback' => [$this->aliasCleaner, 'cleanTokenValues'],
            'langcode' => $entity->language()->getId(),
            'pathauto' => true,
        ];

        foreach ($tokensByType as $type => $tokens) {
            $replacementValues = $this->token->generate($type, $tokens, $entityTokens, $options, new BubbleableMetadata);

            foreach ($tokens as $token => $rawToken) {
                $possibleImplementations = [
                    implode(':', [$type, $token]),
                    $type,
                ];

                foreach ($possibleImplementations as $id) {
                    if (!$this->patternTokenDependenciesManager->hasDefinition($id)) {
                        continue;
                    }

                    $this->patternTokenDependenciesManager
                        ->createInstance($id)
                        ->addDependencies($token, $replacementValues[$rawToken], $dependencies);
                }
            }
        }
    }

    protected function addDependenciesFromPlugins(array &$dependencies, PathautoPatternInterface $pattern, EntityInterface $entity): void
    {
        $managers = [
            $this->patternDependenciesManager,
            $this->patternBuilderManager,
            $this->aliasBuilderManager,
        ];

        foreach ($managers as $manager) {
            if (!$manager->hasDefinition($pattern->id())) {
                continue;
            }

            $builder = $manager->createInstance($pattern->id());

            if (!$builder instanceof PatternDependenciesInterface) {
                continue;
            }

            $builder->addDependencies($dependencies, $entity);
        }
    }
}
