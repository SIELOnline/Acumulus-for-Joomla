<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * @noinspection PhpUnused
 * @noinspection PhpDeprecationInspection
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Table\Table;
use Siel\Acumulus\Config\Config;
use Siel\Acumulus\Helpers\Container;

use const Siel\Acumulus\Version;

/**
 * Installer file of the Acumulus component.
 */
class com_acumulusInstallerScript
{
    private string $newVersion;
    private string $currentVersion;
    private Container $container;

    /**
     * Method to run before an installation/update/uninstall method.
     *
     * This method checks:
     * - The Joomla version against the version attribute in the manifest.
     * - If this is not a downgrade of this module.
     * - If VirtueMart or HikaShop are installed.
     * - The VirtueMart version against the minVirtueMartVersion element in the
     *   manifest.
     * - OR The HikaShop version against the minHikaShopVersion element in the
     *   manifest.
     * - The dependencies on PHP extensions of this module.
     *
     * @param string $type
     *   The type of change (install, update or discover_install).
     * @param \Joomla\CMS\Installer\Adapter\ComponentAdapter $parent
     *   The installer object calling this method.
     *
     * @noinspection PhpDeprecationInspection AdapterInstance:
     *   5.0 Will be removed without replacement.
     */
    public function preflight(string $type, ComponentAdapter $parent): bool
    {
        try {
            $this->newVersion = (string) $parent->getManifest()->version;
            $joomlaVersion = new JVersion();
            $joomlaVersion = $joomlaVersion->getShortVersion();// Check Joomla version
            $minJoomlaVersion = (string) $parent->getManifest()->attributes()->version;
            if (version_compare($joomlaVersion, '3.9', '<')) {
                Installer::getInstance()->abort(
                    "The Acumulus component ($this->newVersion) requires at least Joomla $minJoomlaVersion, found $joomlaVersion."
                );
                return false;
            }
            if (version_compare($joomlaVersion, $minJoomlaVersion, '<')) {
                /** @noinspection PhpUnhandledExceptionInspection */
                JFactory::getApplication()->enqueueMessage(
                    "The Acumulus component ($this->newVersion) has not been tested on Joomla $joomlaVersion. " .
                    'Please report any incompatibilities.',
                    'warning'
                );
                return false;
            }
            if ($type === 'update') {
                /** @var \Joomla\CMS\Table\Extension $extension */
                /** @noinspection PhpDeprecationInspection : Deprecated as of J4 */
                $extension = Table::getInstance('extension');

                $id = $extension->find(['element' => 'com_acumulus', 'type' => 'component']);
                if (!empty($id) && $extension->load($id)) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $componentInfo = json_decode($extension->manifest_cache, true);
                    $this->currentVersion = $componentInfo['version'];
                } else {
                    $this->currentVersion = '1.0';
                }
                // Check downgrade.
                if (version_compare($this->newVersion, $this->currentVersion, '<')) {
                    Installer::getInstance()->abort(
                        "The Acumulus component ($this->currentVersion) cannot be downgraded to $this->newVersion.");
                    return false;
                }
                // Check VM plugin move.
                if (version_compare($this->currentVersion, '7.6.3', '<')
                    && version_compare($this->newVersion, '7.6.3', '>=')
                ) {
                    JFactory::getApplication()->enqueueMessage(
                        'The Acumulus plugin for VirtueMart has been moved from folder "vmcoupon" to "vmextended". ' .
                        'The update did not remove the plugin from the old folder.' .
                        'Please go to "Extensions - Manage" and uninstall the Acumulus plugin at folder "vmcoupon".',
                        'warning'
                    );
                }
            }
            // Check if VirtueMart is installed.
            jimport('joomla.application.component.controller');
            $shopVersion = $this->getVersion('com_virtuemart');
            if (!empty($shopVersion)) {
                $minVersion = (string) $parent->getManifest()->minVirtueMartVersion;
                if (version_compare($shopVersion, $minVersion, '<')) {
                    Installer::getInstance()->abort(
                        "The Acumulus component $this->newVersion requires at least VirtueMart $minVersion, found $shopVersion."
                    );
                    return false;
                }
                $shopNamespace = 'Joomla\\VirtueMart';
            } else {
                // if VM is not installed, check if HikaShop is installed.
                $shopVersion = $this->getVersion('com_hikashop');
                if (!empty($shopVersion)) {
                    $minVersion = (string) $parent->getManifest()->minHikaShopVersion;
                    if (version_compare($shopVersion, $minVersion, '<')) {
                        Installer::getInstance()->abort(
                            "The Acumulus component $this->newVersion requires at least HikaShop $minVersion, found $shopVersion."
                        );
                        return false;
                    }
                } else {
                    Installer::getInstance()->abort(
                        "The Acumulus component $this->newVersion requires VirtueMart or HikaShop to be installed and enabled.");
                    return false;
                }
                $shopNamespace = 'Joomla\\HikaShop';
            }
            // Check extension requirements.
            // Get access to our classes via the auto loader.
            $componentPath = __DIR__;
            if (is_dir("$componentPath/lib")) {
                // Installing directly from administrator/components/com_acumulus:
                // probably via the discovery feature of the extension manager.
                $libraryPath = $componentPath;
            } else /* if (is_dir("$componentPath/admin/lib")) */ {
                // Installing from the zip.
                $libraryPath = "$componentPath/admin";
            }
            /** @noinspection PhpMethodParametersCountMismatchInspection  Parameter is needed in J3. */
            JLoader::registerNamespace('Siel\\Acumulus', $libraryPath . '/lib/siel/acumulus/src', false, false, 'psr4');
            $this->container = new Container($shopNamespace, 'en');
            $errors = $this->container->getRequirements()->check();
            $abortMessage = '';
            foreach ($errors as $key => $message) {
                if (strpos($key, 'warning') !== false) {
                    JFactory::getApplication()->enqueueMessage($message, 'warning');
                } else {
                    $abortMessage .= ' ' . $message;
                }
            }
            if ($abortMessage !== '') {
                Installer::getInstance()->abort($abortMessage);
                return false;
            }
        } catch (Throwable $e) {
            Installer::getInstance()->abort($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Method to install the extension.
     *
     * @param \Joomla\CMS\Installer\Adapter\ComponentAdapter $parent
     *   The installer object calling this method.
     *
     * @throws \Exception
     */
    public function install(ComponentAdapter $parent): void
    {
        try {
            $version = (string) $parent->getManifest()->version;// Set initial config version.
            if (empty($this->container->getConfig()->get(Config::VersionKey))) {
                $values = [Config::VersionKey => Version];
                $this->container->getConfig()->save($values);
            }
            $shopName = $this->getVersion('com_virtuemart') !== '' ? 'VirtueMart' : 'HikaShop';
            JFactory::getApplication()->enqueueMessage(
                "The Acumulus component ($version) has been installed. ".
                "Please fill in the settings form and enable the Acumulus plugin for $shopName.",
                'message'
            );
            JInstaller::getInstance()->setRedirectUrl('index.php?option=com_acumulus&task=config');
        } catch (Throwable $e) {
            Installer::getInstance()->abort($e->getMessage());
        }
    }

    /**
     * Method to uninstall the extension.
     *
     * param \Joomla\CMS\Installer\Installer $parent
     *   The installer object calling this method.
     */
    public function uninstall(/*Installer $parent*/): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        JFactory::getApplication()->enqueueMessage('The Acumulus component has been uninstalled.');
    }

    /**
     * Method to update the extension.
     *
     * param \Joomla\CMS\Installer\Adapter\ComponentAdapter $parent
     *   The installer object calling this method.
     *
     * @throws \Exception
     */
    public function update(/*ComponentAdapter $parent*/): void
    {
        try {
            // The autoloader should have been set by the preflight method.
            // The config class will start updating itself as soon as the
            // configVersion key has been set.
            if (empty($this->container->getConfig()->get(Config::VersionKey))) {
                $values = [Config::VersionKey => $this->currentVersion];
                $this->container->getConfig()->save($values);
            }
            JFactory::getApplication()->enqueueMessage("The Acumulus component has been updated to version $this->newVersion.", 'message');
        } catch (Throwable $e) {
            Installer::getInstance()->abort($e->getMessage());
        }
    }

    /**
     * Method to run after an installation/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     */
    public function postflight(string $type, object $parent): void
    {
    }

    /**
     * Returns the version of a component (if installed and enabled).
     *
     * Note that JComponentHelper::isEnabled shows a warning if the component is
     * not installed, which we don't want.
     *
     * @param string $component
     *   The element/name of the extension.
     *
     * @return string
     *   The version string if the extension is installed and enabled, the empty
     *   string otherwise.
     */
    protected function getVersion(string $component): string
    {
        $result = '';
        $db = JFactory::getDbo();
        $db->setQuery(
            sprintf("SELECT manifest_cache FROM #__extensions WHERE element = '%s' and type = 'component'", $db->escape($component))
        );
        $manifestCache = $db->loadResult();
        if (!empty($manifestCache)) {
            $componentInfo = json_decode($manifestCache, false);
            $result = $componentInfo->version;
        }
        return $result;
    }
}
