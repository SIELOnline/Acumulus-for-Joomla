<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
/** @var AcumulusViewConfig $this */
?>
<!--suppress HtmlUnknownTarget -->
<form action="index.php?option=com_acumulus&task=config" method="post" id="adminForm" name="adminForm">
  <div class="form-horizontal">
    <?= $this->formRenderer->render($this->form); ?>
    <input type="hidden" name="task" value="config" />
    <?= JHtml::_('form.token'); ?>
  </div>
</form>

