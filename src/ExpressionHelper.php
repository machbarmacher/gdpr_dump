<?php

namespace Drupal\gdpr_dump;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;

class ExpressionHelper {

  /** @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfoManager */
  private $bundleInfoManager;

  /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
  private $entityFieldManager;

  /** @var \Drupal\Core\TypedData\TypedDataManagerInterface */
  private $typedDataManager;

  /**
   * GdprExpressions constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfoManager
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   */
  public function __construct(\Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfoManager, \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager, TypedDataManagerInterface $typedDataManager) {
    $this->bundleInfoManager = $bundleInfoManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->typedDataManager = $typedDataManager;
  }


  public function getFieldExpressions($entityTypeId) {
    $expressions = [];
    $bundles = $this->bundleInfoManager->getBundleInfo($entityTypeId);
    foreach ($bundles as $bundle) {
      $fields = $this->entityFieldManager->getFieldDefinitions($entityTypeId, $bundle);
      // @fixme Only process configurable fields.
      $fields = array_filter($fields, function (FieldDefinitionInterface $field) {
        return !$field->getFieldStorageDefinition()->isBaseField();
      });
      /** @var \Drupal\Core\Field\FieldDefinitionInterface $field */
      foreach ($fields as $fieldName => $field) {
        /** @var \Drupal\Core\Field\FieldItemInterface $fieldItemClass */
        $fieldItemClass = $this->getFieldItemClass($field->getType());
        $sample = $fieldItemClass::generateSampleValue($field);
        // Add column name prefixes.
        /** @see \Drupal\Core\Field\FieldItemInterface::schema */
        /** @see \Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema::saveFieldSchemaData */
        /** @see \Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema::getDedicatedTableSchema */
        // $definition = $field->getFieldStorageDefinition();
        // $columnDefinitions = $definition->getColumns();
        foreach ($sample as $column => $value) {
          // We're hardcoding this here as the API does not expose this.
          $sqlColumn = "{$fieldName}_{$column}";
          // @todo Better use field storage definitions.
          $sqlValue = (is_int($value) || is_float($value)) ? (string) $value : "'$value'";
          // Revision table expressons get silently ignored if not applicable.
          $sqlTables = ["{$entityTypeId}__{$fieldName}", "{$entityTypeId}_revision__{$fieldName}"];
          foreach ($sqlTables as $sqlTable) {
            $expressions[$sqlTable][$sqlColumn] = $sqlValue;
          }
        }
      }
    }
    return $expressions;
  }

  /**
   * Helper to get field item class.
   *
   * Copy of private
   * @see \Drupal\field\Entity\FieldStorageConfig::getFieldItemClass
   * Also note diffenrent implementation in
   * @see \Drupal\Core\Field\BaseFieldDefinition::getSchema
   */
  private function getFieldItemClass($fieldType) {
    $type_definition = $this->typedDataManager
      ->getDefinition('field_item:' . $fieldType);
    return $type_definition['class'];
  }
}
