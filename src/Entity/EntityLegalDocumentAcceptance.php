<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Entity\EntityLegalDocumentAcceptance.
 */

namespace Drupal\entity_legal\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\entity_legal\EntityLegalDocumentAcceptanceInterface;

/**
 * Defines the entity legal document acceptance entity.
 *
 * @ContentEntityType(
 *   id = "entity_legal_document_acceptance",
 *   label = @Translation("Legal document acceptance"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   admin_permission = "administer entity legal",
 *   base_table = "entity_legal_document_acceptance",
 *   entity_keys = {
 *     "id" = "aid",
 *     "uid" = "uid",
 *   },
 * )
 */
class EntityLegalDocumentAcceptance extends ContentEntityBase implements EntityLegalDocumentAcceptanceInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['aid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The entity ID of this agreement.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['document_version_name'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Document version'))
      ->setDescription(t('The name of the document version this acceptance is bound to.'))
      ->setSetting('target_type', ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
      ->setRequired(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The author of the acceptance.'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\entity_legal\Entity\EntityLegalDocumentAcceptance::getCurrentUserId')
      ->setRequired(TRUE);

    $fields['acceptance_date'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Date accepted'))
      ->setDescription(t('The date the document was accepted.'));

//      'data' => array(
//        'description' => 'A dump of user data to help verify acceptances.',
//        'type' => 'text',
//        'not null' => FALSE,
//        'size' => 'big',
//        'serialize' => TRUE,
//        'merge' => FALSE,
//      ),

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function getDocumentVersion() {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $item */
    $item = $this->get('document_version_name');
    return $item->referencedEntities()[0];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return t('Accepted on @date', [
      '@date' => \Drupal::service('date.formatter')->format($this->get('acceptance_date')->value),
    ]);
  }

}

// @TODO - Should be storing the $_SERVER variables.
///**
// * {@inheritdoc}
// *
// * Adds legal document identifier and revision values to the acceptance
// * entity.
// */
//public function save($entity, DatabaseTransaction $transaction = NULL) {
//  // Dump all available data from the current users browsing session.
//  $entity->data = $_SERVER;
//
//  return parent::save($entity, $transaction);
//}

// @TODO - Is this check needed?
///**
// * {@inheritdoc}
// */
//public function create(array $values = array()) {
//  if (empty($values['document_version_name'])) {
//    throw new EntityFieldQueryException(t('Acceptance does not have a valid associated document version name'));
//  }
//  return parent::create($values);
//}