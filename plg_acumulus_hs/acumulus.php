<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

defined('_JEXEC') or die;

/**
 * Acumulus plugin to react to HikaShop order status changes.
 *
 * These status changes are advertised via the onAfterOrderUpdate event.
 *
 * @noinspection PhpUnused
 */
class plgHikashopAcumulus extends JPlugin
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
        $this->init();
        $this->getModel()->sourceStatusChange($order->order_id);
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
            $this->init();
            if ($this->getModel()->getAcumulusConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
                $this->getController()->invoice($order->order_id);
            }
        }
        return true;
    }
}
