<?php
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Shop\Config;
use Siel\Acumulus\Joomla\Helpers\FormRenderer;
use Siel\Acumulus\Joomla\Shop\BatchForm;
use Siel\Acumulus\Joomla\VirtueMart\Shop\ConfigForm;
use Siel\Acumulus\Web\ConfigInterface;

/**
 * Acumulus Model
 */
class AcumulusModelAcumulus extends JModelLegacy {

  /** @var \Siel\Acumulus\Shop\Config */
  protected $acumulusConfig;

  /** @var \Siel\Acumulus\Helpers\Form */
  protected $form;

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->acumulusConfig = new Config('Joomla\\VirtueMart', substr(JFactory::getLanguage()->getTag(), 0, 2));
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
   * @param string $task
   *
   * @return \Siel\Acumulus\Helpers\Form
   */
  public function getForm($task) {
    // Get the form.
    if (!isset($this->form)) {
      switch ($task) {
        case 'batch':
          $this->form = new BatchForm($this->acumulusConfig->getTranslator(), $this->acumulusConfig->getManager());
          break;
        case 'config':
          $this->form = new ConfigForm($this->acumulusConfig->getTranslator(), $this->acumulusConfig);
          break;
        default:
          $this->acumulusConfig->getLog()->error('InvoiceManager::getForm(): unknown formType "%s"', $task);
          break;
      }
    }
    return $this->form;
  }

  /**
   * Processes an order update by notifying the invoiceManager.
   *
   * @param \TableOrders $order
   * @param string $old_order_status
   *
   * @return int
   *   Status, one of the WebConfigInterface::Status_ constants.
   */
  public function sourceStatusChange(TableOrders $order, $old_order_status) {
    $result = ConfigInterface::Status_NotSent;
    if ($order->order_status !== $old_order_status) {
      $source = $this->acumulusConfig->getSource(Source::Order, $order->virtuemart_order_id);
      $result = $this->acumulusConfig->getManager()->sourceStatusChange($source, $order->order_status);
    }
    return $result;
  }

}
