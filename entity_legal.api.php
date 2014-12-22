<?php
/**
 * @file
 * API documentation for entity_legal module.
 */

/**
 * Alter available user notification methods.
 *
 * @param array $methods
 *   Available methods.
 */
function hook_entity_legal_document_method(array &$methods) {
  $methods['email'] = t('Email all users');
}
