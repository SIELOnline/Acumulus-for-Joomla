<?php
use Siel\Acumulus\VirtueMart\Helpers\FormRenderer;

/**
 * Acumulus batch view.
 */
class AcumulusViewBatch extends JViewLegacy {
  /** @var \Siel\Acumulus\VirtueMart\Shop\BatchForm */
  protected $form;

  /** @var array */
  protected $formFields;

  // @todo: inject form renderer?
  /** @var \Siel\Acumulus\VirtueMart\Helpers\FormRenderer */
  protected $formRenderer;

  public function __construct($config = array()) {
    $this->_defaultModel = 'Acumulus';
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
    return $this->getModel()->getTranslator()->get($key);
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
    $this->formFields = $this->form->getFields();
    $this->formFields = $this->form->addValues($this->formFields);

    // Display the template
    $this->formRenderer = new FormRenderer($this->form);
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
    $input = JFactory::getApplication()->input;

    // Show Joomla Administrator Main menu.
    $input->set('hidemainmenu', false);

    JToolBarHelper::title($this->t('batch_form_header'), 'acumulus');
    JToolBarHelper::save('batch', $this->t('button_send'));
    JToolBarHelper::cancel('cancel', $this->t('button_cancel'));
  }

  /**
   * Method to set up the document properties.
   *
   * @return void
   */
  protected function setDocument() {
    $document = JFactory::getDocument();
    $document->setTitle($this->t('batch_form_title'));
    $document->addStyleDeclaration('.icon-acumulus ' .
      '{background-image: url(./components/com_acumulus/media/logo-acumulus-16.png);}');
  }

}
