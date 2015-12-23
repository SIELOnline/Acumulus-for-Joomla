<?php
/**
 * Acumulus config view.
 */
class AcumulusView extends JViewLegacy {
  /** @var string */
  protected $action;

  /** @var string */
  protected $task;

  /** @var string */
  protected $saveButton;

  /** @var \Siel\Acumulus\Helpers\Form */
  protected $form;

  public function __construct($config = array()) {
    $this->_defaultModel = 'Acumulus';
    $this->saveButton = 'button_save';
    parent::__construct($config);
  }

  /**
   * Override only for improved type checking and auto complete.
   *
   * @param string|null $name
   *
   * @return AcumulusModelAcumulus
   */
  public function getModel($name = null) {
    return parent::getModel($name);
  }

  /**
   * Helper method to translate strings.
   *
   * @param string $key
   *  The key to get a translation for.
   *
   * @return string
   *   The translation for the given key or the key itself if no translation
   *   could be found.
   */
  protected function t($key) {
    return $this->getModel()->t($key);
  }

  /**
   * Display the Acumulus view
   *
   * @param string $tpl
   *   The name of the template file to parse; automatically searches through
   *   the template paths.
   *
   * @return void
   *
   * @throws \Exception
   */
  public function display($tpl = NULL) {
    // Get the config form.
    $this->form = $this->getForm();
    // Get the fields and their values for display.
    $this->form->addValues();

    // Display the template.
    parent::display($tpl);

    $this->addToolBar();
    $this->setDocument();
  }

  /**
   * Add the page title and toolbar.
   *
   * @return  void
   *
   * @since   1.6
   */
  protected function addToolBar() {
    // Show Joomla Administrator Main menu.
    $input = JFactory::getApplication()->input;
    $input->set('hidemainmenu', false);

    // Add title and buttons.
    JToolBarHelper::title($this->t($this->task . '_form_header'), 'acumulus');
    JToolBarHelper::save($this->task, $this->t($this->saveButton));
    JToolBarHelper::cancel('cancel', $this->t('button_cancel'));
  }

  /**
   * Method to set up the document properties.
   */
  protected function setDocument() {
    $document = JFactory::getDocument();
    $document->setTitle($this->t($this->task . '_form_title'));
    $document->addStyleDeclaration('.icon-acumulus ' .
      '{background-image: url(./components/com_acumulus/media/logo-acumulus-16.png);}');
  }

}