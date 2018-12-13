<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

defined('_JEXEC') or die;

use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Invoice\Source;

/**
 * Acumulus Model
 */
class AcumulusModelAcumulus extends JModelLegacy
{
    /** @var \Siel\Acumulus\Helpers\Container */
    protected $container;

    /** @var string */
    protected $shopNamespace;

    public function __construct($config = array())
    {
        parent::__construct($config);
        if ($this->loadVirtueMart()) {
            $this->shopNamespace = 'Joomla\\VirtueMart';
        } else if ($this->loadHikaShop()) {
            $this->shopNamespace = 'Joomla\\HikaShop';
        }
        $this->container = new Container($this->shopNamespace, substr(JFactory::getLanguage()->getTag(), 0, 2));
    }

    /**
     * Checks if VirtueMart is installed and enabled and loads its base classes.
     *
     * @return bool
     *   True if VirtueMart is installed and enabled, false otherwise.
     */
    protected function loadVirtueMart()
    {
        if ($this->isEnabled('com_virtuemart')) {
            // Load VirtueMart: we need access to its models and data.
            // Copied from administrator/components/com_virtuemart/virtuemart.php
            if (!class_exists('VmConfig')) {
                /** @noinspection PhpIncludeInspection */
                require_once JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php';
            }
            VmConfig::loadConfig();

            if (!class_exists('VmController')) {
                /** @noinspection PhpIncludeInspection */
                require VMPATH_ADMIN . '/helpers/vmcontroller.php';
            }
            if (!class_exists('VmModel')) {
                /** @noinspection PhpIncludeInspection */
                require VMPATH_ADMIN . '/helpers/vmmodel.php';
            }
            return true;
        }
        return false;
    }

    /**
     * Checks if HikaShop is installed and enabled and loads its base classes.
     *
     * @return bool
     *   True if HikaShop is installed and enabled, false otherwise.
     */
    protected function loadHikaShop()
    {
        if ($this->isEnabled('com_hikashop')) {
            /** @noinspection PhpIncludeInspection */
            return include_once JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
        }
        return false;
    }

    /**
     * Checks if a component is installed and enabled.
     *
     * Note that JComponentHelper::isEnabled shows a warning if the component is
     * not installed, which we don't want.
     *
     * @param string $component
     *   The element/name of the extension.
     *
     * @return bool
     *   True if the extension is installed and enabled, false otherwise
     */
    protected function isEnabled($component)
    {
        $db = JFactory::getDbo();
        $db->setQuery(sprintf("SELECT enabled FROM #__extensions WHERE element = '%s' and type = 'component'", $db->escape($component)));
        $enabled = $db->loadResult();
        return $enabled == 1;
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
    public function t($key)
    {
        return $this->container->getTranslator()->get($key);
    }

    /**
     * @return \Siel\Acumulus\Config\Config
     */
    public function getAcumulusConfig()
    {
        return $this->container->getConfig();
    }

    /**
     * @param string $task
     *
     * @return \Siel\Acumulus\Helpers\Form
     */
    public function getForm($task)
    {
        // Get the form.
        return $this->container->getForm($task);
    }

    /**
     * @return \Siel\Acumulus\Helpers\FormRenderer
     */
    public function getFormRenderer()
    {
        return $this->container->getFormRenderer();
    }

    /**
     * Wrapper method around \Siel\Acumulus\Shop\Config::getSource().
     *
     * @param string $invoiceSourceType
     *   The type of the invoice source to create.
     * @param string|object|array $invoiceSourceOrId
     *   The invoice source itself or its id to create a Source wrapper for.
     *
     * @return \Siel\Acumulus\Invoice\Source
     *   A wrapper object around a shop specific invoice source object.
     */
    public function getSource($invoiceSourceType, $invoiceSourceOrId)
    {
        return $this->container->getSource($invoiceSourceType, $invoiceSourceOrId);
    }

    /**
     * Wrapper method around \Siel\Acumulus\Shop\InvoiceManager::sourceStatusChange().
     *
     * @param Source $source
     *
     * @return \Siel\Acumulus\Invoice\Result
     *   The result of sending (or not sending) the invoice.
     */
    public function sourceStatusChange(Source $source)
    {
        return $this->container->getInvoiceManager()->sourceStatusChange($source);
    }
}
