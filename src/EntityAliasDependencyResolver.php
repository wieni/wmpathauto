<?php

namespace Drupal\wmpathauto;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Utility\Token;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\pathauto\PathautoGeneratorInterface;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\token\TokenEntityMapperInterface;

class EntityAliasDependencyResolver implements EntityAliasDependencyResolverInterface
{
    /** @var Token */
    protected $token;
    /** @var TokenEntityMapperInterface */
    protected $tokenEntityMapper;
    /** @var AliasCleanerInterface */
    protected $aliasCleaner;
    /** @var PathautoGeneratorInterface */
    protected $aliasGenerator;
    /** @var AliasBuilderManager */
    protected $aliasBuilderManager;
    /** @var PatternBuilderManager */
    protected $patternBuilderManager;
    /** @var EntityAliasDependencyProviderManager */
    protected $patternDependenciesManager;
    /** @var PatternTokenDependencyProviderManager */
    protected $patternTokenDependenciesManager;

    public function __construct(
        Token $token,
        TokenEntityMapperInterface $tokenEntityMapper,
        AliasCleanerInterface $aliasCleaner,
        PathautoGeneratorInterface $aliasGenerator,
        AliasBuilderManager $aliasBuilderManager,
        PatternBuilderManager $patternBuilderManager,
        EntityAliasDependencyProviderManager $patternDependenciesManager,
        PatternTokenDependencyProviderManager $patternTokenDependenciesManager
    ) {
        $this->token = $token;
        $this->tokenEntityMapper = $tokenEntityMapper;
        $this->aliasCleaner = $aliasCleaner;
        $this->aliasGenerator = $aliasGenerator;
        $this->aliasBuilderManager = $aliasBuilderManager;
        $this->patternBuilderManager = $patternBuilderManager;
        $this->patternDependenciesManager = $patternDependenciesManager;
        $this->patternTokenDependenciesManager = $patternTokenDependenciesManager;
    }

    public function getDependencies(EntityInterface $entity): EntityAliasDependencyCollectionInterface
    {
        $pattern = $this->aliasGenerator->getPatternByEntity($entity);
        $dependencies = new EntityAliasDependencyCollection;

        if ($pattern) {
            $this->addDependenciesFromTokens($dependencies, $pattern, $entity);
            $this->addDependenciesFromPlugins($dependencies, $pattern, $entity);
        }

        return $dependencies;
    }

    protected function addDependenciesFromTokens(EntityAliasDependencyCollectionInterface $dependencies, PathautoPatternInterface $pattern, EntityInterface $entity): void
    {
        $tokensByType = $this->token->scan($pattern->getPattern());
        $entityTokenType = $this->tokenEntityMapper->getTokenTypeForEntityType($entity->getEntityTypeId());
        $data = [$entityTokenType => $entity];

        $langcode = $entity->language()->getId();
        // Core does not handle aliases with language Not Applicable.
        if ($langcode === LanguageInterface::LANGCODE_NOT_APPLICABLE) {
            $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED;
        }

        $options = [
            'clear' => true,
            'callback' => [$this->aliasCleaner, 'cleanTokenValues'],
            'langcode' => $langcode,
            'pathauto' => true,
        ];

        foreach ($tokensByType as $type => $tokens) {
            if (!$this->patternTokenDependenciesManager->hasDefinition($type)) {
                continue;
            }

            $this->patternTokenDependenciesManager
                ->createInstance($type)
                ->addDependencies($tokens, $data, $options, $dependencies);
        }
    }

    protected function addDependenciesFromPlugins(EntityAliasDependencyCollectionInterface $dependencies, PathautoPatternInterface $pattern, EntityInterface $entity): void
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

            if (!$builder instanceof EntityAliasDependencyProviderInterface) {
                continue;
            }

            $builder->addDependencies($dependencies, $entity);
        }
    }
}
