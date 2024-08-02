<?php
/**
 * @noinspection AutoloadingIssuesInspection
 *
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Version;
use Siel\Joomla\Component\Acumulus\Administrator\Extension\AcumulusComponent;

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
 * @noinspection PhpUnused  Plugins are instantiated dynamically.
 */
class plgVmExtendedAcumulus extends CMSPlugin
{
    /**
     * Constructor
     *
     * @param \Joomla\Event\DispatcherInterface &$subject
     *   The object to observe.
     * @param array $config
     *    An optional associative array of configuration settings. Recognized
     *    key values include 'name', 'group', 'params', 'language' (this list is
     *    not meant to be comprehensive).
     */
    public function __construct(&$subject, array $config = [])
    {
        $this->initialized = false;
        parent::__construct($subject, $config);

        // Since J4, events that do not start with 'on' are no longer registered
        // automatically.
        if (Version::MAJOR_VERSION >= 4) {
            $this->registerLegacyListener('plgVmCouponUpdateOrderStatus');
            $this->registerLegacyListener('plgVmOnShowOrderBEPayment');
        }
    }

    private function getAcumulusComponent(): AcumulusComponent
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Factory::getApplication()->bootComponent('acumulus');
    }

    /**
     * Event observer to react to order updates.
     *
     * @param TableOrders $order
     *   A {@see Table} for the  VirtueMart orders
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
        $this->getAcumulusComponent()->getAcumulusModel()->sourceStatusChange((int) $order->virtuemart_order_id);

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
     * @todo: VM4 (render as other blocks)
     * @noinspection PhpUnused  event handler: not called directly.
     */
    public function plgVmOnShowOrderBEPayment(int $orderId): string
    {
        if ($this->getAcumulusComponent()->getAcumulusModel()->getAcumulusConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
            ob_start();
            $this->getAcumulusComponent()->getAcumulusController()->invoice($orderId);
            return ob_get_clean();
        }
        return '';
    }
}
