<?php
/**
 * @file
 * Page callbacks for entity_legal module.
 */

/**
 * The page view for the entity legal document.
 *
 * @param EntityLegalDocument $entity_legal_document
 *   The entity object for the document.
 *
 * @return string
 *   The page markup.
 */
function entity_legal_document_page(EntityLegalDocument $entity_legal_document) {
  return entity_view(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, array($entity_legal_document));
}
