<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

defined('_JEXEC') or die;

/**
 * Acumulus plugin to react to VirtueMart order status changes.
 *
 * We have chosen to listen to the plgVmCouponUpdateOrderStatus event as this
 * is the last event that gets triggered when an order gets updated through
 * VirtueMartModelOrders::updateStatusForOneOrder(). At that moment the
 * shipment and payment plugins have been called successfully, and the order
 * and order history have been stored successfully.
 *
 * Events we did not choose to use:
 * - plgVmOnUpdateOrderShipment: too early as it gets called before the
 *   payment plugin can have reacted to the order update.
 * - plgVmOnUpdateOrderPayment: still quite early as we might get called
 *   before the actual payment plugin can have reacted to it (and possibly
 *   indicate failure) and the order itself and the order history have not yet
 *   been stored: so the database might not be used at that point as it is
 *   "outdated".
 *
 * @noinspection PhpUnused
 */
class plgVmCouponAcumulus extends JPlugin
{
    /** @var bool */
    protected $initialized = false;

    /** @var AcumulusModelAcumulus */
    protected $model;

    /** @var \AcumulusController */
    protected $controller;

    /**
     * Initializes the environment for the plugin:
     * - Register autoloader for our own library.
     */
    protected function init()
    {
        if (!$this->initialized) {
            $componentPath = JPATH_ADMINISTRATOR . '/components/com_acumulus';
            // Get access to our models and tables.
            JModelLegacy::addIncludePath("$componentPath/models", 'AcumulusModel');
            JTable::addIncludePath("$componentPath/tables");
            $this->initialized = true;
        }
    }

    /**
     * Returns an Acumulus model.
     *
     * @return AcumulusModelAcumulus
     */
    protected function getModel(): AcumulusModelAcumulus
    {
        if ($this->model === null) {
            $this->model = JModelLegacy::getInstance('Acumulus', 'AcumulusModel');
        }
        return $this->model;
    }

    /**
     * Returns an Acumulus controller.
     *
     * @return \AcumulusController
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function getController(): AcumulusController
    {
        if ($this->controller === null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->controller = JControllerLegacy::getInstance('Acumulus', ['base_path' => JPATH_ROOT . '/administrator/components/com_acumulus']);
        }
        return $this->controller;
    }

    /**
     * Event observer to react to order updates.
     *
     * @param TableOrders $order
     * param string $old_order_status
     *
     * @return bool|null
     *   True on success, false on failure, or null when this method does not
     *   want to influence the return value of the dispatching method.
     *   For now only VirtueMartModelOrders::updateStatusForOneOrder is
     *   dispatching this, and we always return null.
     *
     * @throws \Throwable
     *
     * @noinspection PhpUnused  event handler: not called directly.
     */
    public function plgVmCouponUpdateOrderStatus(TableOrders $order/*, $old_order_status*/): ?bool
    {
        $this->init();
        $this->getModel()->sourceStatusChange($order->virtuemart_order_id);

        // We return null as we do not want to influence the return value of
        // VirtueMartModelOrders::updateStatusForOneOrder().
        return null;
    }

    /**
     * Event observer to add our own info to the order detail screen.
     *
     * @param int $orderId
     *
     * @return string
     *   The rendered invoice status overview form.
     *
     * @throws \Throwable
     *
     * @noinspection PhpUnused  event handler: not called directly.
     */
    public function plgVmOnUpdateOrderBEPayment(int $orderId): string
    {
        $this->init();
        if ($this->getModel()->getAcumulusConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
            ob_start();
            $this->getController()->invoice($orderId);
            return ob_get_clean();
        }
        return '';
    }
}
