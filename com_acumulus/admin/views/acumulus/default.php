<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * Default template file for the Acumulus component views.
 *
 * This file has side effects, so checking if Joomla has been initialized is in place.
 */

defined('_JEXEC') or die;

/** @var AcumulusViewAcumulus $this */
?>
<!--suppress CssUnusedSymbol -->
<style>
div.control-label {
    max-width: 180px;
}
</style>
<form action="<?= $this->action; ?>" method="post" id="adminForm" name="adminForm">
    <div class="form-horizontal">
        <?= $this->getModel()->getFormRenderer()->render($this->form); ?>
        <input type="hidden" name="task" value="<?= $this->task ?>"/>
        <?= JHtml::_('form.token'); ?>
    </div>
</form>
