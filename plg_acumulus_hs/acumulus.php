<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

defined('_JEXEC') or die;

use Siel\Acumulus\Invoice\Source;

/**
 * Acumulus plugin to react to HikaShop order status changes.
 *
 * These status changes are advertised via the onAfterOrderUpdate event.
 *
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
