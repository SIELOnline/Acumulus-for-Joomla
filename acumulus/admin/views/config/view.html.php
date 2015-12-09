<?php
require_once(__DIR__ . '/../view.php');

/**
 * Acumulus config view.
 */
class AcumulusViewConfig extends AcumulusView {

  public function __construct($config = array()) {
    parent::__construct($config);
    $this->task = 'config';
    $this->action = 'index.php?option=com_acumulus&task=config';
  }

}
