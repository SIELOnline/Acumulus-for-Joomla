<?php
use Siel\Acumulus\Shop\Config;
use Siel\Acumulus\Joomla\Helpers\FormRenderer;
use Siel\Acumulus\Joomla\Shop\BatchForm;
use Siel\Acumulus\Joomla\VirtueMart\Shop\ConfigForm;

/**
 * Acumulus Model
 */
class AcumulusModelAcumulus extends JModelLegacy {

  /** @var \Siel\Acumulus\Shop\Config */
  protected $acumulusConfig;

  /** @var string */
  protected $formType;

  /** @var \Siel\Acumulus\Helpers\Form */
  protected $form;

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->acumulusConfig = new Config('Joomla\\VirtueMart', substr(JFactory::getLanguage()->getTag(), 0, 2));
    $this->formType = $config['name'];
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
  public function t($key) {
    return $this->acumulusConfig->getTranslator()->get($key);
  }

  /**
   * @return \Siel\Acumulus\Helpers\FormRenderer
   */
  public function getFormRenderer() {
    return new FormRenderer();
  }

  /**
   * @return \Siel\Acumulus\Helpers\Form
   */
  public function getForm() {
    // Get the form.
    if (!isset($this->form)) {
      switch ($this->formType) {
        case 'batch':
          $this->form = new BatchForm($this->acumulusConfig->getTranslator(), $this->acumulusConfig->getManager());
          break;
        case 'config':
          $this->form = new ConfigForm($this->acumulusConfig->getTranslator(), $this->acumulusConfig);
          break;
      }
    }
    return $this->form;
  }
}
