<?php
/**
 * Acumulus Entry Table class.
 */
class AcumulusTableAcumulusEntry extends JTable {

  /** @var int */
  public $id = null;

  /** @var string */
  public $entry_id = null;

  /** @var string */
  public $token = null;

  /** @var string */
  public $source_type = null;

  /** @var int */
  public $source_id = null;

  /** @var string */
  public $created = null;

  /** @var string */
  public $updated = null;

  /**
   * Constructor
   *
   * @param JDatabaseDriver $db
   *   A database connector object. Leave empty for the default instance.
   */
  public function __construct(JDatabaseDriver $db = null) {
    parent::__construct('#__acumulus_entry', 'id', $db ? $db : JFactory::getDBO());
  }

}
