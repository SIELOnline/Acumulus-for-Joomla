<?php
/**
 * @noinspection SqlNoDataSourceInspection
 *
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

defined('_JEXEC') or die;

use Siel\Acumulus\Config\Config;
use Siel\Acumulus\Config\ConfigUpgrade;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Helpers\CrashReporter;
use Siel\Acumulus\Helpers\Form;
use Siel\Acumulus\Helpers\FormRenderer;
use Siel\Acumulus\Helpers\Log;
use Siel\Acumulus\Invoice\InvoiceAddResult;
use Siel\Acumulus\Invoice\Source;

/**
 * Acumulus Model
 */
class AcumulusModelAcumulus extends JModelLegacy
{
    /** @var \Siel\Acumulus\Helpers\Container */
    protected static $instance;

    /** @var \Siel\Acumulus\Helpers\Container */
    protected $container;

    /** @var string */
    protected $shopNamespace;

    /** @var bool  */
    public $isVirtueMart = false;

    /** @var bool  */
    public $isHikaShop = false;

    public function __construct($config = array())
    {
        // Get access to our classes via the auto loader.
        JLoader::registerNamespace('Siel\\Acumulus', __DIR__ . '/../lib/siel/acumulus/src', false, false, 'psr4');

        parent::__construct($config);
        if ($this->loadVirtueMart()) {
            $this->isVirtueMart = true;
            $this->shopNamespace = 'Joomla\\VirtueMart';
        } elseif ($this->loadHikaShop()) {
            $this->isHikaShop = true;
            $this->shopNamespace = 'Joomla\\HikaShop';
        }
        if (static::$instance === null) {
            static::$instance = new Container($this->shopNamespace, substr(JFactory::getLanguage()->getTag(), 0, 2));
        }
        $this->container = static::$instance;
    }

    /**
     * Checks if VirtueMart is installed and enabled and loads its base classes.
     *
     * @return bool
     *   True if VirtueMart is installed and enabled, false otherwise.
     */
    protected function loadVirtueMart(): bool
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
    protected function loadHikaShop(): bool
    {
        if ($this->isEnabled('com_hikashop')) {
            /** @noinspection PhpIncludeInspection */
            return require_once JPATH_ROOT . '/administrator/components/com_hikashop/helpers/helper.php';
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
    protected function isEnabled(string $component): bool
    {
        $db = JFactory::getDbo();
        /** @noinspection SqlResolve */
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
    public function t(string $key): string
    {
        return $this->getAcumulusContainer()->getTranslator()->get($key);
    }

    protected function getAcumulusContainer(): Container
    {
        return $this->container;
    }

    public function getCrashReporter(): CrashReporter
    {
        return $this->getAcumulusContainer()->getCrashReporter();
    }

    public function getAcumulusConfig(): Config
    {
        return $this->getAcumulusContainer()->getConfig();
    }

    public function getAcumulusConfigUpgrade(): ConfigUpgrade
    {
        return $this->getAcumulusContainer()->getConfigUpgrade();
    }

    public function getLog(): Log
    {
        return $this->getAcumulusContainer()->getLog();
    }

    public function getForm(string $task): Form
    {
        return $this->getAcumulusContainer()->getForm($task);
    }

    public function getFormRenderer(): FormRenderer
    {
        return $this->getAcumulusContainer()->getFormRenderer();
    }

    /**
     * Wrapper method around \Siel\Acumulus\Shop\Config::getSource().
     *
     * @param string $invoiceSourceType
     *   The type of the invoice source to create.
     * @param int|array $invoiceSourceOrId
     *   The invoice source itself or its id to create a Source wrapper for.
     *
     * @return \Siel\Acumulus\Invoice\Source
     *   A wrapper object around a shop specific invoice source object.
     */
    public function getSource(string $invoiceSourceType, $invoiceSourceOrId): Source
    {
        return $this->getAcumulusContainer()->createSource($invoiceSourceType, $invoiceSourceOrId);
    }

    /**
     * Wrapper method around \Siel\Acumulus\Shop\InvoiceManager::sourceStatusChange().
     *
     * @param int $orderId
     *
     * @return \Siel\Acumulus\Invoice\InvoiceAddResult|null
     *   The result of sending (or not sending) the invoice.
     *
     * @throws \Throwable
     */
    public function sourceStatusChange(int $orderId): ?InvoiceAddResult
    {
        try {
            $source = $this->getSource(Source::Order, $orderId);
            return $this->getAcumulusContainer()->getInvoiceManager()->sourceStatusChange($source);
        } catch (Throwable $e) {
            try {
                $crashReporter = $this->getCrashReporter();
                // We do not know if we are on the admin side, so we should not
                // try to display the message returned by logAndMail().
                $crashReporter->logAndMail($e);
                return null;
            } catch (Throwable $inner) {
                // We do not know if we have informed the user per mail or
                // screen, so assume not, and rethrow the original exception.
                throw $e;
            }
        }
    }
}
