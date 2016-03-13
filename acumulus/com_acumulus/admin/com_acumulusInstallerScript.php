<?php
/**
 * @copyright   Buro RaDer.
 * @license     GPLv3; see license.txt.
 */

use Siel\Acumulus\Helpers\Requirements;

/**
 * Installer file of the Acumulus component.
 */
class com_acumulusInstallerScript
{
    /**
     * Method to install the extension
     * $parent is the class calling this method
     *
     * @param JAdapterInstance $parent
     *
     * @throws \Exception
     */
    public function install($parent)
    {
        $version = (string) $parent->get('manifest')->version;
        JFactory::getApplication()->enqueueMessage("The Acumulus component ($version) has been installed. Please fill in the settings form.", 'message');
        JInstaller::getInstance()->setRedirectUrl('index.php?option=com_acumulus&task=config');
    }

    /**
     * Method to uninstall the extension
     * $parent is the class calling this method
     *
     * @return void
     */
    public function uninstall(/*$parent*/)
    {
        JFactory::getApplication()->enqueueMessage('The Acumulus component has been uninstalled.', 'message');
    }

    /**
     * Method to update the extension
     * $parent is the class calling this method
     *
     * @param JAdapterInstance $parent
     *
     * @throws \Exception
     */
    public function update($parent)
    {
        $version = (string) $parent->get('manifest')->version;
        JFactory::getApplication()->enqueueMessage("The Acumulus component has been updated to version $version.", 'message');
    }

    /**
     * Method to run before an install/update/uninstall method.
     *
     * This method checks:
     * - The Joomla version against the version attribute in the manifest.
     * - If this is not a downgrade of this module.
     * - If VirtueMart is installed.
     * - The Virtuemart against the minVirtueMartVersion element in the manifest.
     * - The dependencies on PHP extensions of this module.
     *
     * @param string $type
     *   The type of change (install, update or discover_install)
     * @param JInstallerAdapter $parent
     *   The object calling this method.
     *
     * @return bool
     */
    public function preflight($type, $parent)
    {
        $version = (string) $parent->get("manifest")->version;
        $joomlaVersion = new JVersion();
        $joomlaVersion = $joomlaVersion->getShortVersion();

        // Check Joomla version
        $minJoomlaVersion = $parent->get("manifest")->attributes()->version;
        if (version_compare($joomlaVersion, '3.1', '<')) {
            JInstaller::getInstance()->abort("The Acumulus component ($version) requires at least Joomla $minJoomlaVersion, found $joomlaVersion.");
            return false;
        }
        if (version_compare($joomlaVersion, $minJoomlaVersion, '<')) {
            JFactory::getApplication()->enqueueMessage("The Acumulus component ($version) has not been tested on Joomla $joomlaVersion. Please report any incompatibilities.", 'message');
            return false;
        }

        // Check downgrade.
        if ($type == 'update') {
            $currentInfo = json_decode($parent->get('extension')->manifest_cache, true);
            $currentRelease = $currentInfo['version'];
            if (version_compare($version, $currentRelease, '<')) {
                JInstaller::getInstance()->abort("The Acumulus component ($currentRelease) cannot be downgraded to $version.");
                return false;
            }
        }

        // Check if VirtueMart is installed.
        jimport('joomla.application.component.controller');
        $shopVersion = $this->getVersion('com_virtuemart');
        if (!empty($shopVersion)) {
            $minVersion = (string) $parent->get("manifest")->minVirtueMartVersion;
            if (version_compare($shopVersion, $minVersion, '<')) {
                JInstaller::getInstance()->abort("The Acumulus component $version requires at least VirtueMart $minVersion, found $shopVersion.");
                return false;
            }
        } else {
            $shopVersion = $this->getVersion('com_hikashop');
            if (!empty($shopVersion)) {
                $minVersion = (string) $parent->get("manifest")->minHikaShopVersion;
                if (version_compare($shopVersion, $minVersion, '<')) {
                    JInstaller::getInstance()->abort("The Acumulus component $version requires at least HikaShop $minVersion, found $shopVersion.");
                    return false;
                }
            } else {
                JInstaller::getInstance()->abort("'The Acumulus component $version requires VirtueMart or HikaShop to be installed and enabled.");
                return false;
            }
        }

        // Check extension requirements.
        // Get access to our classes via the auto loader.
        $componentPath = dirname(__FILE__);
        if (is_dir("$componentPath/libraries")) {
            // Installing directly from administrator/components/com_acumulus:
            // probably via the discovery feature of the extensions manager.
            $libraryPath = "$componentPath/libraries";
        } else /* if (is_dir("$componentPath/admin/libraries")) */ {
            // Installing from the zip.
            $libraryPath = "$componentPath/admin/libraries";
        }
        JLoader::registerNamespace('Siel', $libraryPath);
        $errors = Requirements::check();
        if (!empty($errors)) {
            JInstaller::getInstance()->abort(implode(' ', $errors));
            return false;
        }

        return true;
    }

    /**
     * Method to run after an install/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @param string $type
     * @param object $parent
     */
    public function postflight($type, $parent)
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
    protected function getVersion($component)
    {
        $result = '';
        $db = JFactory::getDbo();
        $db->setQuery(sprintf("SELECT manifest_cache FROM #__extensions WHERE element = '%s' and type = 'component'", $db->escape($component)));
        $manifestCache = $db->loadResult();
        if (!empty($manifestCache)) {
            $componentInfo = json_decode($manifestCache, true);
            $result = $componentInfo['version'];
        }
        return $result;
    }
}
