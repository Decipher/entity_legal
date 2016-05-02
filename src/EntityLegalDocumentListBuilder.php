<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalDocumentListBuilder.
 */

namespace Drupal\entity_legal;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of entity legal document entities.
 *
 * @see \Drupal\entity_legal\Entity\EntityLegalDocument
 */
class EntityLegalDocumentListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

}
