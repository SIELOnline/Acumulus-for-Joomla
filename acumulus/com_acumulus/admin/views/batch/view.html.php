<?php
/**
 * @copyright   Buro RaDer.
 * @license     GPLv3; see license.txt
 */
require_once __DIR__ . '/../view.php';

/**
 * Acumulus batch view.
 */
class AcumulusViewBatch extends AcumulusView
{
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->task = 'batch';
        $this->action = 'index.php?option=com_acumulus';
        $this->saveButton = 'button_send';
    }
}
