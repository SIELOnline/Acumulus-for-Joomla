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
use Siel\Joomla\Component\Acumulus\Administrator\Extension\AcumulusComponent;

defined('_JEXEC') or die;

/**
 * Acumulus plugin to react to HikaShop order status changes.
 *
 * These status changes are advertised via the onAfterOrderUpdate event.
 *
 * @noinspection PhpUnused  Plugins are instantiated dynamically.
 */
class plgHikashopAcumulus extends CMSPlugin
{
    private function getAcumulusComponent(): AcumulusComponent
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Factory::getApplication()->bootComponent('acumulus');
    }

    /**
     * Event observer to react to order updates.
     *
     * @param object $order
     * param bool $send_email
     *
     * @return bool
     *   True on success, false on failure.
     *
     * @throws \Throwable
     *
     * @noinspection PhpUnused  event handler: not called directly.
     */
    public function onAfterOrderUpdate(object $order/*, &$send_email*/): bool
    {
        $this->getAcumulusComponent()->getAcumulusModel()->sourceStatusChange((int) $order->order_id);
        return true;
    }

    /**
     * Event observer to add our own info to the order detail screen.
     *
     * @param object $order
     * @param string $type
     *
     * @return bool
     *   True on success, false on failure.
     *
     * @throws \Throwable
     *
     * @noinspection PhpUnused  event handler: not called directly.
     */
    public function onAfterOrderProductsListingDisplay(object $order, string $type): bool
    {
        if ($type === 'order_back_show') {
            if ($this->getAcumulusComponent()->getAcumulusModel()->getAcumulusConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
                $this->getAcumulusComponent()->getAcumulusController()->invoice((int) $order->order_id);
            }
        }
        return true;
    }
}
