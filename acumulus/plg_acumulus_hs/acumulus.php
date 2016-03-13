<?php
/**
 * @copyright   Buro RaDer.
 * @license     GPLv3; see license.txt
 */

use Siel\Acumulus\Invoice\Source;

/**
 * Acumulus plugin to react to HikaShop order status changes.
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
 */
class plgHikashopAcumulus extends JPlugin
{
    /** @var bool */
    protected $initialized = false;

    /** @var AcumulusModelAcumulus */
    protected $model;

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
            // Get access to our library classes via the auto loader.
            JLoader::registerNamespace('Siel', "$componentPath/libraries");
            $this->initialized = true;
        }
    }

    /**
     * Returns an Acumulus model
     *
     * @param array $config
     *
     * @return AcumulusModelAcumulus
     */
    public function getModel($config = array())
    {
        if ($this->model === null) {
            $this->model = JModelLegacy::getInstance('Acumulus', 'AcumulusModel', $config);
        }
        return $this->model;
    }

    /**
     * Event observer to react to order updates.
     *
     * @param object $order
     * param bool $send_email
     *
     * @return bool
     *   True on success, false on failure.
     */
    public function onAfterOrderUpdate(&$order/*, &$send_email*/)
    {
        $this->init();
        $source = $this->getModel()->getSource(Source::Order, $order->order_id);
        $this->getModel()->sourceStatusChange($source);
        return true;
    }
}
