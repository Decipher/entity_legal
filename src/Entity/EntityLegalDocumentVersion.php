<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Entity\EntityLegalDocumentVersion.
 */

namespace Drupal\entity_legal\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;

/**
 * Defines the entity legal document version entity.
 *
 * @ContentEntityType(
 *   id = "entity_legal_document_version",
 *   label = @Translation("Legal document version"),
 *   handlers = {
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "view_builder" = "Drupal\entity_legal\EntityLegalDocumentVersionViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\entity_legal\Form\EntityLegalDocumentVersionForm"
 *     }
 *   },
 *   admin_permission = "administer entity legal",
 *   base_table = "entity_legal_document_version",
 *   data_table = "entity_legal_document_version_data",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "vid",
 *     "label" = "label",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "bundle" = "document_name"
 *   },
 *   bundle_entity_type = "entity_legal_document",
 * )
 */
class EntityLegalDocumentVersion extends ContentEntityBase implements EntityLegalDocumentVersionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['vid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The entity ID of this document.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language'))
      ->setDescription(t('The document version language code.'))
      ->setTranslatable(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The entity UUID of this document'))
      ->setReadOnly(TRUE);

    $fields['document_name'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Form ID'))
      ->setDescription(t('The name of the document this version is bound to.'))
      ->setSetting('target_type', ENTITY_LEGAL_DOCUMENT_ENTITY_NAME)
      ->setRequired(TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The title of the document.'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['acceptance_label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Acceptance label'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The date the document was created.'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The date the document was changed.'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

//  $entity_legal_document['author'] = array(
//    'label' => t('Author'),
//    'type' => 'user',
//    'description' => t("The author of the document."),
//    'getter callback' => 'entity_legal_get_properties',
//    'required' => TRUE,
//    'schema field' => 'uid',
//  );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedDate($type = 'changed') {
    switch ($type) {
      case 'changed':
        return \Drupal::service('date.formatter')->format($this->getChangedTime());

      case 'created':
        return \Drupal::service('date.formatter')->format($this->getCreatedTime());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptances(AccountInterface $account = NULL) {
    $query = \Drupal::entityQuery(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME)
      ->condition('document_version_name', $this->id());

    if ($account) {
      $query->condition('uid', $account->id());
    }

    $results = $query->execute();
    if (!empty($results)) {
      return \Drupal::entityTypeManager()->getStorage(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME)->loadMultiple($results);
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDocument() {
    return \Drupal::entityTypeManager()->getStorage(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME)->load($this->bundle());
  }

}

//  $schema['entity_legal_document_version'] = array(
//    'description' => 'Stores information about all defined entity legals.',
//    'fields' => array(
//      'vid' => array(
//        'description' => 'The entity id of this document',
//        'type' => 'serial',
//        'unsigned' => TRUE,
//        'not null' => TRUE,
//      ),
//      'name' => array(
//        'description' => 'The machine-readable name of this entity_legal_form.',
//        'type' => 'varchar',
//        'length' => 64,
//        'not null' => TRUE,
//      ),
//      'document_name' => array(
//        'description' => 'The name of the document this version is bound to.',
//        'type' => 'varchar',
//        'length' => 32,
//        'not null' => TRUE,
//      ),
//      'label' => array(
//        'description' => 'The title of the document.',
//        'type' => 'varchar',
//        'length' => 255,
//        'not null' => TRUE,
//        'default' => '',
//      ),
//      'status' => array(
//        'description' => 'The exportable status of the entity.',
//        'type' => 'int',
//        'not null' => TRUE,
//        'default' => 0x01,
//        'size' => 'tiny',
//      ),
//      'module' => array(
//        'description' => 'The name of the providing module if the entity has been defined in code.',
//        'type' => 'varchar',
//        'length' => 255,
//        'not null' => FALSE,
//      ),
//      'uid' => array(
//        'type' => 'int',
//        'unsigned' => TRUE,
//        'not null' => FALSE,
//        'default' => NULL,
//        'description' => "The user who created this version.",
//      ),
//      'acceptance_label' => array(
//        'type' => 'varchar',
//        'length' => 255,
//        'not null' => TRUE,
//        'default' => 'I agree',
//      ),
//      'created' => array(
//        'description' => 'A Unix timestamp indicating when this version was created.',
//        'type' => 'int',
//        'not null' => TRUE,
//        'default' => 0,
//      ),
//      'updated' => array(
//        'description' => 'A Unix timestamp indicating when this version was updated.',
//        'type' => 'int',
//        'not null' => TRUE,
//        'default' => 0,
//      ),
//    ),
//    'primary key' => array('vid'),
//    'indexes' => array(
//      'uid' => array('uid'),
//    ),
//    'foreign keys' => array(
//      'uid' => array('users' => 'uid'),
//      'document_name' => array('entity_legal_document' => 'name'),
//    ),
//  );
