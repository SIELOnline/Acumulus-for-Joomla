<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * This file may have side effects, so checking if Joomla has been initialized is in place.
 */

defined('_JEXEC') or die;

use Siel\Acumulus\Invoice\Source;

if (!class_exists('vmCouponPlugin')) {
    require(JPATH_VM_PLUGINS . '/vmcouponplugin.php');
}

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
 */
class plgVmCouponAcumulus extends vmCouponPlugin
{
    /** @var bool */
    protected $initialized = false;

    /** @var AcumulusModelAcumulus */
    protected $model;

    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->_tablename = '';
    }

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
			JLoader::registerNamespace('Siel\\Acumulus', '$componentPath/lib/siel/acumulus/src', false, false, 'psr4');
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
    protected function getModel($config = array())
    {
        if ($this->model === null) {
            $this->model = JModelLegacy::getInstance('Acumulus', 'AcumulusModel', $config);
        }
        return $this->model;
    }


    /**
     * Event observer to react to order updates.
     *
     * @param TableOrders $order
     * param string $old_order_status
     *
     * @return bool|null
     *   True on success, false on failure, or null when this method does not want
     *   to influence the return value of the dispatching method
     *   (for now only VirtueMartModelOrders::updateStatusForOneOrder)
     */
    public function plgVmCouponUpdateOrderStatus(TableOrders $order/*, $old_order_status*/)
    {
        $this->init();
        $source = $this->getModel()->getSource(Source::Order, $order->virtuemart_order_id);
        $this->getModel()->sourceStatusChange($source);

        // We return null as we do not want to influence the return value of
        // VirtueMartModelOrders::updateStatusForOneOrder().
        return null;
    }

    /*
     * Methods we don't want to be implemented.
     */
    public function loadJLangThis($fname, $type = 0, $name = 0)
    {
        return;
    }

    public function onStoreInstallPluginTable($psType, $name = false)
    {
        return true;
    }

    protected function removePluginInternalData($id, $primaryKey = 0)
    {
        return;
    }

    public function renderByLayout($layout = 'default', $viewData = null, $name = null, $psType = null)
    {
        return;
    }
}

