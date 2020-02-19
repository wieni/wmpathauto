<?php

namespace Drupal\wmpathauto\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\wmpathauto\EntityAliasDependencyInterface;

/**
 * @ContentEntityType(
 *     id = "entity_alias_dependency",
 *     label = @Translation("Entity alias dependency"),
 *     base_table = "entity_alias_dependency",
 *     translatable = FALSE,
 *     entity_keys = {
 *         "id" : "did",
 *     },
 * )
 */
class EntityAliasDependency extends ContentEntityBase implements EntityAliasDependencyInterface
{
    public static function baseFieldDefinitions(EntityTypeInterface $entityType)
    {
        $fields['did'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Entity alias dependency ID'))
            ->setDescription(t('The entity alias dependency ID.'))
            ->setReadOnly(true);

        $fields['entity_id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Dependent entity ID'))
            ->setDescription(t('The ID of the entity whose alias has a dependency.'));

        $fields['entity_type'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Dependent entity type'))
            ->setDescription(t('The type of the entity whose alias has a dependency.'));

        $fields['entity_language'] = BaseFieldDefinition::create('language')
            ->setLabel(t('Dependent entity language'))
            ->setDescription(t('The language of the entity whose alias has a dependency.'))
            ->setDisplayOptions('form', [
                'type' => 'language_select',
                'weight' => 2,
            ]);

        $fields['dependency_type'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Dependency type'))
            ->setDescription(t('The dependency type.'));

        $fields['dependency_value'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Dependency value'))
            ->setDescription(t('The dependency value.'));

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The date when the entity alias dependency was created.'));

        return $fields;
    }

    public function getDependentEntity(): ?EntityInterface
    {
        $langcode = $this->get('entity_language')->value;

        $entity = $this->entityTypeManager()
            ->getStorage($this->get('entity_type')->value)
            ->load($this->get('entity_id')->value);

        if (!$entity instanceof TranslatableInterface) {
            return $entity;
        }

        if (!$entity->hasTranslation($langcode)) {
            return $entity;
        }

        return $entity->getTranslation($langcode);
    }

    public function getDependencyType(): string
    {
        return $this->get('dependency_type')->value;
    }

    public function getDependencyValue(): string
    {
        return $this->get('dependency_value')->value;
    }
}
