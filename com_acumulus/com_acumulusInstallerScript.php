<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * @noinspection PhpUnused
 *
 * @noinspection PhpDeprecationInspection
 */

defined('_JEXEC') or die;

use Joomla\CMS\Adapter\AdapterInstance;
use Joomla\CMS\Installer\InstallerAdapter;
use Siel\Acumulus\Config\Config;
use Siel\Acumulus\Helpers\Container;

use const Siel\Acumulus\Version;

/**
 * Installer file of the Acumulus component.
 */
class com_acumulusInstallerScript
{
    /** @var string */
    private $newVersion;

    /** @var string */
    private $currentVersion;

    /** @var \Siel\Acumulus\Helpers\Container */
    private $container;

    /**
     * Method to install the extension.
     *
     * @param \Joomla\CMS\Installer\InstallerAdapter $parent
     *   The class calling this method.
     *
     * @throws \Exception
     */
    public function install(InstallerAdapter $parent)
    {
        $version = (string) $parent->getManifest()->version;
        // Set initial config version.
        if (empty($this->container->getConfig()->get(Config::configVersion))) {
            $values = [Config::configVersion => Version];
            $this->container->getConfig()->save($values);
        }

        $shopName = $this->getVersion('com_virtuemart') !== '' ? 'VirtueMart 3' : 'HikaShop';
        JFactory::getApplication()->enqueueMessage("The Acumulus component ($version) has been installed. Please fill in the settings form and enable the Acumulus plugin for $shopName."
        );
        JInstaller::getInstance()->setRedirectUrl('index.php?option=com_acumulus&task=config');
    }

    /**
     * Method to uninstall the extension.
     *
     * param \Joomla\CMS\Installer\InstallerAdapter $parent
     *   The class calling this method.
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function uninstall(/*InstallerAdapter $parent*/)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        JFactory::getApplication()->enqueueMessage('The Acumulus component has been uninstalled.');
    }

    /**
     * Method to update the extension.
     *
     * param \Joomla\CMS\Updater\UpdateAdapter $parent
     *   The class calling this method.
     *
     * @throws \Exception
     */
    public function update(/*UpdateAdapter $parent*/)
    {
        // The autoloader should have been set by the preflight method.
        // The config class will start updating itself as soon as the
        // configVersion key has been set.
        if (empty($this->container->getConfig()->get(Config::configVersion))) {
            $values = [Config::configVersion => $this->currentVersion];
            $this->container->getConfig()->save($values);
        }
        JFactory::getApplication()->enqueueMessage("The Acumulus component has been updated to version $this->newVersion.");
    }

    /**
     * Method to run before an installation/update/uninstall method.
     *
     * This method checks:
     * - The Joomla version against the version attribute in the manifest.
     * - If this is not a downgrade of this module.
     * - If VirtueMart is installed.
     * - The VirtueMart against the minVirtueMartVersion element in the manifest.
     * - The dependencies on PHP extensions of this module.
     *
     * @param string $type
     *   The type of change (install, update or discover_install)
     * @param \Joomla\CMS\Installer\InstallerAdapter|\Joomla\CMS\Updater\UpdateAdapter $parent
     *   The object calling this method.
     *
     * @return bool
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpDeprecationInspection AdapterInstance:
     *   5.0 Will be removed without replacement.
     */
    public function preflight(string $type, AdapterInstance $parent): bool
    {
        $this->newVersion = (string) $parent->getManifest()->version;
        $joomlaVersion = new JVersion();
        $joomlaVersion = $joomlaVersion->getShortVersion();

        // Check Joomla version
        $minJoomlaVersion = $parent->getManifest()->attributes()->version;
        if (version_compare($joomlaVersion, '3.9', '<')) {
            JInstaller::getInstance()->abort("The Acumulus component ($this->newVersion) requires at least Joomla $minJoomlaVersion, found $joomlaVersion.");
            return false;
        }
        if (version_compare($joomlaVersion, $minJoomlaVersion, '<')) {
            /** @noinspection PhpUnhandledExceptionInspection */
            JFactory::getApplication()->enqueueMessage("The Acumulus component ($this->newVersion) has not been tested on Joomla $joomlaVersion. Please report any incompatibilities."
            );
            return false;
        }

        // Check downgrade.
        if ($type === 'update') {
            $currentInfo = json_decode($parent->get('extension')->manifest_cache, true);
            $this->currentVersion = $currentInfo['version'];
            if (version_compare($this->newVersion, $this->currentVersion, '<')) {
                JInstaller::getInstance()->abort("The Acumulus component ($this->currentVersion) cannot be downgraded to $this->newVersion.");
                return false;
            }
        }

        // Check if VirtueMart is installed.
        jimport('joomla.application.component.controller');
        $shopVersion = $this->getVersion('com_virtuemart');
        if (!empty($shopVersion)) {
            $minVersion = (string) $parent->getManifest()->minVirtueMartVersion;
            if (version_compare($shopVersion, $minVersion, '<')) {
                JInstaller::getInstance()->abort("The Acumulus component $this->newVersion requires at least VirtueMart $minVersion, found $shopVersion.");
                return false;
            }
            $shopNamespace = 'Joomla\\VirtueMart';
        } else {
            // if VM is not installed, check if HikaShop is installed.
            $shopVersion = $this->getVersion('com_hikashop');
            if (!empty($shopVersion)) {
                $minVersion = (string) $parent->getManifest()->minHikaShopVersion;
                if (version_compare($shopVersion, $minVersion, '<')) {
                    JInstaller::getInstance()->abort("The Acumulus component $this->newVersion requires at least HikaShop $minVersion, found $shopVersion.");
                    return false;
                }
            } else {
                JInstaller::getInstance()->abort("The Acumulus component $this->newVersion requires VirtueMart or HikaShop to be installed and enabled.");
                return false;
            }
            $shopNamespace = 'Joomla\\HikaShop';
        }

        // Check extension requirements.
        // Get access to our classes via the auto loader.
        $componentPath = dirname(__FILE__);
        if (is_dir("$componentPath/lib")) {
            // Installing directly from administrator/components/com_acumulus:
            // probably via the discovery feature of the extension manager.
            $libraryPath = "$componentPath";
        } else /* if (is_dir("$componentPath/admin/lib")) */ {
            // Installing from the zip.
            $libraryPath = "$componentPath/admin";
        }
        JLoader::registerNamespace('Siel\\Acumulus', $libraryPath . '/lib/siel/acumulus/src', false, false, 'psr4');
        $this->container = new Container($shopNamespace, 'en');
        $errors = $this->container->getRequirements()->check();
        if (!empty($errors)) {
            JInstaller::getInstance()->abort(implode(' ', $errors));
            return false;
        }

        return true;
    }

    /**
     * Method to run after an installation/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @param string $type
     * @param object $parent
     */
    public function postflight(string $type, object $parent)
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
        /** @noinspection SqlNoDataSourceInspection */
        $db->setQuery(sprintf("SELECT manifest_cache FROM #__extensions WHERE element = '%s' and type = 'component'", $db->escape($component)));
        $manifestCache = $db->loadResult();
        if (!empty($manifestCache)) {
            $componentInfo = json_decode($manifestCache, true);
            $result = $componentInfo['version'];
        }
        return $result;
    }
}
