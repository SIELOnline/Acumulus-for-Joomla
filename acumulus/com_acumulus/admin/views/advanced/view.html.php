<?php
/**
 * @copyright   Buro RaDer.
 * @license     GPLv3; see license.txt.
 */
require_once __DIR__ . '/../view.php';

/**
 * Acumulus advanced config view.
 */
class AcumulusViewAdvanced extends AcumulusView
{
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->task = 'advanced';
        $this->action = 'index.php?option=com_acumulus&task=advanced';
    }
}
