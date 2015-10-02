<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
/** @var AcumulusViewBatch $this */
?>
<!--suppress HtmlUnknownTarget -->
<form action="index.php?option=com_acumulus" method="post" id="adminForm" name="adminForm">
  <div class="form-horizontal">
    <?= $this->formRenderer->render($this->form); ?>
    <input type="hidden" name="task" value="batch" />
    <?= JHtml::_('form.token'); ?>
  </div>
</form>

