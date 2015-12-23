<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
/** @var AcumulusView $this */
?>
<!--suppress HtmlUnknownTarget -->
<form action="<?= $this->action; ?>" method="post" id="adminForm" name="adminForm">
  <div class="form-horizontal">
    <?= $this->getModel()->getFormRenderer()->render($this->form); ?>
    <input type="hidden" name="task" value="<?= $this->task ?>" />
    <?= JHtml::_('form.token'); ?>
  </div>
</form>
