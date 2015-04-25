<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * General Controller of the Acumulus component
 */
class AcumulusController extends JControllerLegacy {

  /** @var AcumulusModelAcumulus */
  protected $model;

  /**
   * @param string $name
   * @param string $prefix
   * @param array $config
   *
   * @return AcumulusModelAcumulus
   */
  public function getModel($name = '', $prefix = '', $config = array()) {
    if ($this->model === null) {
      $this->model = parent::getModel('Acumulus', $prefix, $config + array('name' => $name));
    }
    return $this->model;
  }

  public function display($cachable = false, $urlparams = array()) {
    $this->default_view = 'batch';
    return parent::display($cachable, $urlparams);
  }

  public function batch() {
    return $this->executeTask('batch');
  }

  public function config() {
    return $this->executeTask('config');
  }

  protected function executeTask($task) {
    /** @var AcumulusModelAcumulus $model */
    $model = $this->getModel('Acumulus', '', array('name' => $task));
    $form = $model->getForm();
    $form->process();
    // Show messages.
    foreach($form->getSuccessMessages() as $message) {
      JFactory::getApplication()->enqueueMessage($message, 'message');
    }
    foreach($form->getErrorMessages() as $message) {
      JFactory::getApplication()->enqueueMessage($message, 'error');
    }

    // Check for serious errors.
    if (count($errors = $this->get('Errors'))) {
      throw new Exception(implode('<br />', $errors), 500);
    }

    $this->default_view = $task;
    return parent::display();
  }

}
