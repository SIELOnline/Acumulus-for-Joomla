<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

declare(strict_types=1);

namespace Siel\Joomla\Component\Acumulus\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;
use RuntimeException;
use Siel\Acumulus\Config\Config;
use Siel\Acumulus\Config\ConfigUpgrade;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Helpers\CrashReporter;
use Siel\Acumulus\Helpers\Form as AcumulusForm;
use Siel\Acumulus\Helpers\FormRenderer;
use Siel\Acumulus\Helpers\Log;
use Siel\Acumulus\Invoice\InvoiceAddResult;
use Siel\Acumulus\Invoice\Source;
use Throwable;

/**
 * Acumulus Model
 */
class AcumulusModel extends AdminModel
{
    protected static Container $acumulusContainer;

    protected string $shopNamespace;
    public bool $isVirtueMart = false;
    public bool $isHikaShop = false;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if ($this->loadVirtueMart()) {
            $this->isVirtueMart = true;
            $this->shopNamespace = 'Joomla\\VirtueMart';
        } elseif ($this->loadHikaShop()) {
            $this->isHikaShop = true;
            $this->shopNamespace = 'Joomla\\HikaShop';
        }
        if (!isset(static::$acumulusContainer)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            static::$acumulusContainer = new Container(
                $this->shopNamespace,
                substr(Factory::getApplication()->getLanguage()->getTag(), 0, 2)
            );
        }
    }

    /**
     * Checks if VirtueMart is installed and enabled and loads its base classes.
     *
     * @return bool
     *   True if VirtueMart is installed and enabled, false otherwise.
     *
     * @noinspection RedundantSuppression  Prevents warnings in VM.
     * @noinspection PhpUndefinedConstantInspection  Prevents warnings in HS.
     * @noinspection PhpUndefinedClassInspection  Prevents warnings in HS.
     * @noinspection PhpIncludeInspection  VMPATH_ADMIN not resolvable
     */
    protected function loadVirtueMart(): bool
    {
        if ($this->isEnabled('com_virtuemart')) {
            // Load VirtueMart: we need access to its models and data.
            // Copied from administrator/components/com_virtuemart/virtuemart.php
            if (!class_exists('VmConfig')) {
                require_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
            }
            \VmConfig::loadConfig();

            if (!class_exists('VmController')) {
                require_once VMPATH_ADMIN . '/helpers/vmcontroller.php';
            }
            if (!class_exists('VmModel')) {
                require_once VMPATH_ADMIN . '/helpers/vmmodel.php';
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
            require_once JPATH_ROOT . '/administrator/components/com_hikashop/helpers/helper.php';
            return true;
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
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery(sprintf("SELECT enabled FROM #__extensions WHERE element = '%s' and type = 'component'", $db->escape($component)));
        $enabled = $db->loadResult();
        return ((int) $enabled) === 1;
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

    public function getAcumulusContainer(): Container
    {
        return static::$acumulusContainer;
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

    public function getAcumulusForm(string $task): AcumulusForm
    {
        return $this->getAcumulusContainer()->getForm($task);
    }

    public function getAcumulusFormRenderer(): FormRenderer
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
            } catch (Throwable) {
                // We do not know if we have informed the user per mail or
                // screen, so assume not, and rethrow the original exception.
                throw $e;
            }
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function getForm($data = [], $loadData = true): Form
    {
        throw new RuntimeException(__METHOD__ . ' is not implemented');
    }
}
