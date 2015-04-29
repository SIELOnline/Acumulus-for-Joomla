<?php
use Siel\Acumulus\Helpers\Translator;
use Siel\Acumulus\Shop\Config;
use Siel\Acumulus\VirtueMart\BatchForm;
use Siel\Acumulus\VirtueMart\ConfigForm;
use Siel\Acumulus\VirtueMart\ConfigStore;
use Siel\Acumulus\VirtueMart\InvoiceManager;

/**
 * Acumulus Model
 */
class AcumulusModelAcumulus extends JModelLegacy {

  /** @var \Siel\Acumulus\Shop\InvoiceManager */
  protected $invoiceManager;

  /** @var \Siel\Acumulus\Shop\ConfigStoreInterface */
  protected $acumulusConfigStore;

  /** @var \Siel\Acumulus\Shop\Config */
  protected $acumulusConfig;

  /** @var \Siel\Acumulus\Helpers\TranslatorInterface */
  protected $translator;

  /** @var string */
  protected $formType;

  /** @var \Siel\Acumulus\Helpers\Form */
  protected $form;

  public function __construct($config = array()) {
    parent::__construct($config);

    $languageCode = substr(JFactory::getLanguage()->getTag(), 0, 2);
    $this->translator = new Translator($languageCode);
    $this->acumulusConfigStore = new ConfigStore();
    $this->acumulusConfig = new Config($this->acumulusConfigStore, $this->translator);
    $this->formType = $config['name'];
  }

  /**
   * @return \Siel\Acumulus\Helpers\TranslatorInterface
   */
  public function getTranslator() {
    return $this->translator;
  }

  /**
   * @return \Siel\Acumulus\Helpers\Form
   */
  public function getForm() {
    // Get the form.
    if (!isset($this->form)) {
      switch ($this->formType) {
        case 'batch':
          if (!isset($this->invoiceManager)) {
            $this->invoiceManager = new InvoiceManager($this->acumulusConfig, $this->translator);
          }
          $this->form = new BatchForm($this->acumulusConfig, $this->translator, $this->invoiceManager);
          break;
        case 'config':
          $this->form = new ConfigForm($this->acumulusConfig, $this->translator);
          break;
      }
    }
    return $this->form;
  }
}
