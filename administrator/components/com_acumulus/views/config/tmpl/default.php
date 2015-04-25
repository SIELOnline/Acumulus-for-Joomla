<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<form action="index.php?option=com_acumulus&task=config" method="post" id="adminForm" name="adminForm">
  <div class="form-horizontal">
    <?= $this->formRenderer->fields($this->formFields); ?>
    <input type="hidden" name="task" value="config" />
    <?= JHtml::_('form.token'); ?>
  </div>
</form>

