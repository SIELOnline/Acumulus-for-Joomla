<?php

defined('_JEXEC') or die('Restricted access');

if (!class_exists('vmCouponPlugin')) {
  /** @noinspection PhpIncludeInspection */
  require(JPATH_VM_PLUGINS . DS . 'vmcouponplugin.php');
}

/**
 * Acumulus plugin to react to order status changes.
 *
 * We have chosen to listen to the plgVmCouponUpdateOrderStatus event as this
 * is the last event that gets triggered when an order gets updated through
 * VirtueMartModelOrders::updateStatusForOneOrder(). At that moment the
 * shipment and payment plugins have been called successfully, and the order
 * and order history have been stored successfully.
 *
 * Events we did not choose to  use:
 * - plgVmOnUpdateOrderShipment: too early as it gets called before the
 *   payment plugin can have reacted to the order update.
 * - plgVmOnUpdateOrderPayment: still quite early as we might get called
 *   before the actual payment plugin can have reacted to it (and possibly
 *   indicate failure) and the order itself and the order history have not yet
 *   been stored: so the database might not be used at that point as it is
 *   "outdated".
 */
class plgVmCouponAcumulus extends vmCouponPlugin {

  /** @var bool */
  protected $initialized = FALSE;

  /** @var AcumulusModelAcumulus */
  protected $model;

  public function __construct(& $subject, $config) {
    parent::__construct($subject, $config);
    $this->_tablename = '';
  }

  /**
   * Initializes the environment for the plugin:
   * - Register autoloader for our own library.
   */
  protected function init() {
    if (!$this->initialized) {
      // Get access to our classes via the auto loader.
      JLoader::registerNamespace('Siel', JPATH_ADMINISTRATOR . '/components/com_acumulus/libraries');
      $this->initialized = TRUE;
    }
  }

  /**
   * Returns an Acumulus model
   *
   * @param array $config
   *
   * @return AcumulusModelAcumulus
   */
  public function getModel($config = array()) {
    if ($this->model === null) {
      $this->model = JModelLegacy::getInstance('Acumulus', '', $config);
    }
    return $this->model;
  }


  /**
   * Event observer to react to order updates.
   *
   * @param TableOrders $order
   * @param string $old_order_status
   *
   * @return bool|null
   *   True on success, false on failure, or null when this method does not want
   *   to influence the return value of the dispatching method
   *   (for now only VirtueMartModelOrders::updateStatusForOneOrder)
   */
  public function plgVmCouponUpdateOrderStatus(TableOrders $order, $old_order_status) {
    $this->init();
    $this->getModel()->sourceStatusChange($order, $old_order_status);

    // We return null as we do not want to influence the return value of
    // VirtueMartModelOrders::updateStatusForOneOrder().
    return null;
  }
}

