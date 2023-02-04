<?php
/**
 * @noinspection AutoloadingIssuesInspection
 *
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

declare(strict_types=1);

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;

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
    protected bool $initialized = false;
    protected AcumulusModelAcumulus $model;
    protected AcumulusController $controller;

    /**
     * Initializes the environment for the plugin:
     * - Register autoloader for our own library.
     */
    protected function init(): void
    {
        if (!$this->initialized) {
            $componentPath = JPATH_ADMINISTRATOR . '/components/com_acumulus';
            // Get access to our models and tables.
            /**
             * @noinspection PhpDeprecationInspection : I think, eventually, we
             *   should replace legacy models with J4 PSR4 models.
             */
            BaseDatabaseModel::addIncludePath("$componentPath/models", 'AcumulusModel');
            /**
             * @noinspection PhpDeprecationInspection : I think, eventually, we
             *   should replace legacy table classes with J4 PSR4 table classes.
             */
            Table::addIncludePath("$componentPath/tables");
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
        if (!isset($this->model)) {
            /**
             * @noinspection PhpDeprecationInspection : Get the model through
             *   the MVCFactory instead.
             * @noinspection PhpFieldAssignmentTypeMismatchInspection
             */
            $this->model = BaseDatabaseModel::getInstance('Acumulus', 'AcumulusModel');
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
        if (!isset($this->controller)) {
            /**
             * @noinspection PhpUnhandledExceptionInspection
             * @noinspection PhpDeprecationInspection : Get the controller
             *   through the MVCFactory instead.
             * @noinspection PhpFieldAssignmentTypeMismatchInspection
             */
            $this->controller = BaseController::getInstance('Acumulus', ['base_path' => JPATH_ROOT . '/administrator/components/com_acumulus']);
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
