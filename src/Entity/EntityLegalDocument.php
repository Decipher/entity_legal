<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Entity\EntityLegalDocument.
 */

namespace Drupal\entity_legal\Entity;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_legal\EntityLegalDocumentInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;
use Drupal\entity_legal\Form\EntityLegalDocumentAcceptanceForm;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Defines the entity legal document entity.
 *
 * @ConfigEntityType(
 *   id = "entity_legal_document",
 *   label = @Translation("Legal document"),
 *   handlers = {
 *     "access" = "Drupal\entity_legal\EntityLegalDocumentAccessControlHandler",
 *     "list_builder" = "Drupal\entity_legal\EntityLegalDocumentListBuilder",
 *     "form" = {
 *       "add" = "Drupal\entity_legal\Form\EntityLegalDocumentForm",
 *       "edit" = "Drupal\entity_legal\Form\EntityLegalDocumentForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "document",
 *   admin_permission = "administer entity legal",
 *   bundle_of = "entity_legal_document_version",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/legal/manage/{entity_legal_document}/delete",
 *     "edit-form" = "/admin/structure/legal/manage/{entity_legal_document}",
 *     "collection" = "/admin/structure/legal",
 *     "canonical" = "/legal/document/{entity_legal_document}",
 *   }
 * )
 */
class EntityLegalDocument extends ConfigEntityBundleBase implements EntityLegalDocumentInterface {

  /**
   * The legal document ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label of the legal document.
   *
   * @var string
   */
  protected $label;

  /**
   * The current published version of this legal document.
   *
   * @var string
   */
  protected $published_version;

  /**
   * Require new users to accept this document on signup.
   *
   * @var bool
   */
  protected $require_signup = FALSE;

  /**
   * Require existing users to accept this document.
   *
   * @var bool
   */
  protected $require_existing = FALSE;

  /**
   * Am array of additional data related to the legal document.
   *
   * @var array
   */
  protected $settings = [];

//  /**
//   * {@inheritdoc}
//   */
//  public function __construct(array $values = array(), $entity_type = NULL) {
//    Entity::__construct($values, ENTITY_LEGAL_DOCUMENT_ENTITY_NAME);
//  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptanceForm() {
    $form = new EntityLegalDocumentAcceptanceForm($this);

    return \Drupal::formBuilder()->getForm($form);
  }

//  /**
//   * Get a version of this document.
//   *
//   * @param bool $version_name
//   *   If set, load the version otherwise load the default version or create
//   *   one.
//   *
//   * @return EntityLegalDocumentVersion
//   *   The legal document version.
//   */
//  public function getVersion($version_name = FALSE) {
//    // If a version name is supplied, load it.
//    if ($version_name) {
//      return entity_load_single(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME, $version_name);
//    }
//
//    // If no version name is supplied but a published version exists, return it.
//    $published_version = $this->getPublishedVersion();
//    if (!$version_name && $published_version) {
//      return $published_version;
//    }
//
//    $all_versions = $this->getAllVersions();
//
//    // If no versions exist return a new one.
//    if (empty($all_versions) && !$version_name) {
//      return $this->getNewVersion();
//    }
//
//    // Return the first version.
//    if (!$version_name && !$published_version) {
//      return array_pop($all_versions);
//    }
//  }
//
//  /**
//   * Get the label of the legal document entity.
//   *
//   * @param bool $sanitize
//   *   Whether or not to sanitize the label, defaults to TRUE.
//   *
//   * @return string
//   *   The label string.
//   */
//  public function label($sanitize = FALSE) {
//    $label_text = isset($this->label) ? $this->label : '';
//
//    if ($sanitize) {
//      $label_text = check_plain($label_text);
//    }
//
//    return $label_text;
//  }
//
//  /**
//   * Get a new version of this legal document.
//   *
//   * @return LegalDocumentVersion
//   *   The legal document version entity.
//   */
//  public function getNewVersion() {
//    return entity_create(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME, array(
//      'document_name' => $this->identifier(),
//    ));
//  }

  /**
   * {@inheritdoc}
   */
  public function getAllVersions() {
    $query = \Drupal::entityQuery(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
      ->condition('document_name', $this->id());
    $results = $query->execute();
    if (!empty($results)) {
      return \Drupal::entityTypeManager()
        ->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
        ->loadMultiple($results);
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPublishedVersion() {
    $published_version = FALSE;

    \Drupal::moduleHandler()
      ->alter('entity_legal_published_version', $this->published_version, $this);

    if (!empty($this->published_version)) {
      $published_version = \Drupal::entityTypeManager()
        ->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
        ->load($this->published_version);
    }

    return $published_version;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublishedVersion(EntityLegalDocumentVersionInterface $version_entity) {
    // If the version entity is not of this bundle, fail.
    if ($version_entity->bundle() != $this->id()) {
      return FALSE;
    }

    $this->published_version = $version_entity->id();

    return TRUE;
  }

//  /**
//   * Get the title of the attached version.
//   *
//   * @return bool|string
//   *   The title of the published version or FALSE if no title found.
//   */
//  public function getVersionLabel() {
//    $version_entity = $this->getPublishedVersion();
//    if ($version_entity) {
//      return $version_entity->label(TRUE);
//    }
//    else {
//      return FALSE;
//    }
//  }
//
//  /**
//   * Specifies the default uri, which is picked up by uri() by default.
//   */
//  protected function defaultURI() {
//    return array(
//      'path' => 'legal/document/' . str_replace('_', '-', $this->identifier()),
//    );
//  }
//
//  /**
//   * Use the entity name as the identifier.
//   */
//  public function identifier() {
//    return $this->name;
//  }
//
//  /**
//   * URI callback.
//   */
//  public function uri() {
//    return $this->defaultURI();
//  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptanceLabel() {
    $label = '';
    $published_version = $this->getPublishedVersion();

    if ($published_version) {
      $label = $published_version->get('acceptance_label')->value;
    }

    $token_service = \Drupal::service('token');
    $label = $token_service->replace($label, [ENTITY_LEGAL_DOCUMENT_ENTITY_NAME => $this]);

    return Xss::filter($label);
  }

  /**
   * {@inheritdoc}
   */
  public function userMustAgree($new_user = FALSE, AccountInterface $account = NULL) {
    // User cannot agree unless there is a published version.
    if (!$this->getPublishedVersion()) {
      return FALSE;
    }

    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    if ($new_user) {
      return !empty($this->require_signup);
    }
    else {
      return !empty($this->require_existing) && $account->hasPermission($this->getPermissionExistingUser());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function userHasAgreed(AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    return count($this->getAcceptances($account)) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptances(AccountInterface $account = NULL, $published = TRUE) {
    $acceptances = array();
    $versions = array();

    if ($published) {
      $versions[] = $this->getPublishedVersion();
    }
    else {
      $versions = $this->getAllVersions();
    }

    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $version */
    foreach ($versions as $version) {
      $acceptances += $version->getAcceptances($account);
    }

    return $acceptances;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionView() {
    return 'legal view ' . $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionExistingUser() {
    return 'legal re-accept ' . $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptanceDeliveryMethod($new_user = FALSE) {
    $setting_group = $new_user ? 'new_users' : 'existing_users';

    return isset($this->get('settings')[$setting_group]['require_method']) ? $this->get('settings')[$setting_group]['require_method'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $status = parent::save();

    if ($status == SAVED_NEW) {
      // Add or remove the body field, as needed.
      $field = FieldConfig::loadByName('entity_legal_document_version', $this->id(), 'entity_legal_document_text');
      if (empty($field)) {
        FieldConfig::create([
          'field_storage' => FieldStorageConfig::loadByName('entity_legal_document_version', 'entity_legal_document_text'),
          'bundle'        => $this->id(),
          'label'         => 'Document text',
          'settings'      => ['display_summary' => FALSE],
        ])->save();

        // Assign widget settings for the 'default' form mode.
        entity_get_form_display('entity_legal_document_version', $this->id(), 'default')
          ->setComponent('entity_legal_document_text', [
            'type' => 'text_textarea_with_summary',
          ])
          ->save();

        // Assign display settings for 'default' view mode.
        entity_get_display('entity_legal_document_version', $this->id(), 'default')
          ->setComponent('entity_legal_document_text', [
            'label' => 'hidden',
            'type'  => 'text_default',
          ])
          ->save();
      }
    }

    else {
      Cache::invalidateTags(["entity_legal_document:{$this->id()}"]);
    }

    return $status;
  }

}

//  $schema['entity_legal_document'] = array(
//    'description' => 'Stores information about all defined legal document types.',
//    'fields' => array(
//      'name' => array(
//        'description' => 'The machine-readable name of this legal document.',
//        'type' => 'varchar',
//        'length' => 32,
//        'not null' => TRUE,
//      ),
//      'label' => array(
//        'description' => 'The human-readable administrative name of this legal document.',
//        'type' => 'varchar',
//        'length' => 255,
//        'not null' => TRUE,
//        'default' => '',
//      ),
//      'status' => array(
//        'type' => 'int',
//        'not null' => TRUE,
//        // Set the default to ENTITY_CUSTOM without using the constant as it is
//        // not safe to use it at this point.
//        'default' => 0x01,
//        'size' => 'tiny',
//        'description' => 'The exportable status of the entity.',
//      ),
//      'module' => array(
//        'description' => 'The name of the providing module if the entity has been defined in code.',
//        'type' => 'varchar',
//        'length' => 255,
//        'not null' => FALSE,
//      ),
//      'published_version' => array(
//        'description' => 'The current published version of this legal document.',
//        'type' => 'varchar',
//        'length' => 64,
//      ),
//      'require_signup' => array(
//        'description' => 'Require new users to accept this document on signup.',
//        'type' => 'int',
//        'not null' => TRUE,
//        'default' => 0,
//        'size' => 'tiny',
//      ),
//      'require_existing' => array(
//        'description' => 'Require existing users to accept this document.',
//        'type' => 'int',
//        'not null' => TRUE,
//        'default' => 0,
//        'size' => 'tiny',
//      ),
//      'settings' => array(
//        'description' => 'A serialized array of additional data related to this entity_legal_form.',
//        'type' => 'text',
//        'not null' => FALSE,
//        'size' => 'big',
//        'serialize' => TRUE,
//        'merge' => FALSE,
//      ),
//    ),
//    'primary key' => array('name'),
//  );

///**
// * {@inheritdoc}
// */
//public function save($entity) {
//  // When creating a new legal document, add the document text to the bundle.
//  if (!empty($entity->is_new)) {
//    $instance = array(
//      'field_name' => 'entity_legal_document_text',
//      'entity_type' => ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME,
//      'bundle' => $entity->identifier(),
//      'label' => 'Document text',
//      'widget' => array(
//        'type' => 'text_textarea_with_summary',
//        'weight' => 1,
//      ),
//      'settings' => array('display_summary' => TRUE),
//      'display' => array(
//        'default' => array(
//          'label' => 'hidden',
//          'type' => 'text_default',
//        ),
//        'teaser' => array(
//          'label' => 'hidden',
//          'type' => 'text_summary_or_trimmed',
//        ),
//      ),
//    );
//    field_create_instance($instance);
//  }
//
//  $success = parent::save($entity);
//
//  // Flush the entity info cache to allow the new bundle to be registered.
//  entity_info_cache_clear();
//
//  return $success;
//}
//
///**
// * {@inheritdoc}
// */
//public function delete($ids, DatabaseTransaction $transaction = NULL) {
//  // Delete all associated versions.
//  foreach ($ids as $document_name) {
//    $version_query = new EntityFieldQuery();
//    $version_query->entityCondition('entity_type', ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
//      ->propertyCondition('document_name', $document_name);
//
//    $version_result = $version_query->execute();
//
//    if (!empty($version_result) && !empty($version_result[ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME])) {
//      foreach (array_keys($version_result[ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME]) as $version_name) {
//        entity_delete(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME, $version_name);
//      }
//    }
//
//    // Delete field instance.
//    $instances = field_read_instances(array(
//      'entity_type' => 'entity_legal_document_version',
//      'bundle' => $document_name,
//    ), array(
//      'include_inactive' => FALSE,
//      'include_deleted' => FALSE,
//    ));
//
//    foreach ($instances as $instance) {
//      field_delete_instance($instance, FALSE);
//    }
//  }
//
//  parent::delete($ids, $transaction);
//}
//
///**
// * Override the document view to instead output the version view.
// */
//public function view($entities, $view_mode = 'full', $langcode = NULL, $page = NULL) {
//  $entities = entity_key_array_by_property($entities, $this->idKey);
//
//  $view = array();
//  foreach ($entities as $entity) {
//    $published_version = $entity->getPublishedVersion();
//    if ($published_version) {
//      $key = isset($entity->{$this->idKey}) ? $entity->{$this->idKey} : NULL;
//      $view[$this->entityType][$key] = $published_version->view('full', NULL, TRUE);
//    }
//  }
//
//  return $view;
//}