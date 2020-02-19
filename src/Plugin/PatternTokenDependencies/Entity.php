<?php

namespace Drupal\wmpathauto\Plugin\PatternTokenDependencies;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\wmpathauto\EntityAliasDependencyCollectionInterface;
use Drupal\wmpathauto\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PatternTokenDependencies(
 *     type = "entity",
 * )
 *
 * @see field_tokens
 */
class Entity extends PatternTokenDependencyProviderBase
{
    /** @var EntityRepositoryInterface */
    protected $entityRepository;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $plugin_id, $plugin_definition
    ) {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->entityRepository = $container->get('entity.repository');

        return $instance;
    }

    public function addDependencies(array $tokens, array $data, array $options, EntityAliasDependencyCollectionInterface $dependencies): void
    {
        if (empty($data['entity_type']) || empty($data['entity']) || empty($data['token_type'])) {
            return;
        }

        $entity = $data['entity'];

        if (!$entity instanceof ContentEntityInterface) {
            return;
        }

        if (!isset($options['langcode'])) {
            $options['langcode'] = $entity->language()->getId();
        }

        $entity = $this->entityRepository->getTranslationFromContext($entity, $options['langcode']);

        foreach ($tokens as $name => $original) {
            if (strpos($name, ':') === false) {
                // For the [entity:field_name] token.
                $fieldName = $name;
            } else {
                // For [entity:field_name:0], [entity:field_name:0:value] and
                // [entity:field_name:value] tokens.
                [$fieldName] = explode(':', $name, 2);
            }

            if (!$entity->hasField($fieldName) || $entity->get($fieldName)->isEmpty()) {
                continue;
            }

            if ($fieldTokens = $this->tokens->findWithPrefix($tokens, $fieldName)) {
                $tokenData = [
                    'field_property' => TRUE,
                    $data['entity_type'] . '-' . $fieldName => $entity->$fieldName,
                    'field_name' => $data['entity_type'] . '-' . $fieldName,
                ];

                $this->addFieldDependencies($fieldTokens, $tokenData, $options, $dependencies);
            }
        }
    }

    public function addFieldDependencies(array $tokens, array $data, array $options, EntityAliasDependencyCollectionInterface $dependencies): void
    {
        if (empty($data['field_property'])) {
            return;
        }

        foreach ($tokens as $token => $original) {
            $delta = 0;
            $parts = explode(':', $token);

            if (is_numeric($parts[0])) {
                if (count($parts) > 1) {
                    $delta = $parts[0];
                    $propertyName = $parts[1];
                    // Pre-filter the tokens to select those with the correct delta.
                    $filteredTokens = $this->tokens->findWithPrefix($tokens, $delta);
                    // Remove the delta to unify between having and not having one.
                    array_shift($parts);
                } else {
                    // Token is fieldname:delta, which is invalid.
                    continue;
                }
            } else {
                $propertyName = $parts[0];
            }

            if (isset($data[$data['field_name']][$delta])) {
                $fieldItem = $data[$data['field_name']][$delta];
            } else {
                // The field has no such delta, abort replacement.
                continue;
            }

            if (isset($fieldItem->$propertyName) && ($fieldItem->$propertyName instanceof FieldableEntityInterface)) {
                // Entity reference field.
                $entity = $fieldItem->$propertyName;
                // Obtain the referenced entity with the correct language.
                $entity = $this->entityRepository->getTranslationFromContext($entity, $options['langcode']);

                $dependencies->addEntity($entity);
            }

            if ($fieldItem->getFieldDefinition()->getType() === 'image' && ($style = ImageStyle::load($propertyName))) {
                $dependencies->addEntity($style);
            }
        }
    }
}
