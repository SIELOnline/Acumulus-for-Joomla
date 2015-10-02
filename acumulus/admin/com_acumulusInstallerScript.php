<?php
// No direct access to this file
defined('_JEXEC') or die;

use Siel\Acumulus\Helpers\Requirements;

/**
 * Script file of HelloWorld module
 */
class com_acumulusInstallerScript {

  /**
   * Method to install the extension
   * $parent is the class calling this method
   *
   * @param JAdapterInstance $parent
   *
   * @throws \Exception
   */
  public function install($parent) {
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
  public function uninstall(/*$parent*/) {
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
  public function update($parent) {
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
  public function preflight($type, $parent) {
    $version = (string) $parent->get("manifest")->version;
    $joomlaVersion = (new JVersion())->getShortVersion();

    // Check Joomla version: @todo check what version will fail.
    $minJoomlaVersion = $parent->get("manifest")->attributes()->version;
    if (version_compare($joomlaVersion, '3.1', '<')) {
      JInstaller::getInstance()->abort("The Acumulus component ($version) requires at least Joomla $minJoomlaVersion.");
      return FALSE;
    }
    if (version_compare($joomlaVersion, $minJoomlaVersion, '<')) {
      JFactory::getApplication()->enqueueMessage("The Acumulus component ($version) has not been tested on Joomla $joomlaVersion. Please report any incompatibilities.", 'message');
      return FALSE;
    }

    // Check downgrade.
    if ($type == 'update') {
      $currentInfo = json_decode($parent->get('extension')->manifest_cache, TRUE);
      $currentRelease = $currentInfo['version'];
      if (version_compare($version, $currentRelease, 'lt')) {
        JInstaller::getInstance()->abort("The Acumulus component ($currentRelease) cannot be downgraded to $version.");
        return FALSE;
      }
    }

    // Check if VirtueMart is installed.
    jimport('joomla.application.component.controller');
    if (!JComponentHelper::isEnabled('com_virtuemart')) {
      JInstaller::getInstance()->abort("'The Acumulus component $version requires VirtueMart to be installed and enabled.");
      return FALSE;
    }

    // Check Virtuemart version: @todo: determine which version will fail.
    /** @var JTableExtension $extension */
    $extension = JTable::getInstance('extension');
    $id = $extension->find(array('element' => 'com_virtuemart'));
    $extension->load($id);
    /** @noinspection PhpUndefinedFieldInspection */
    $componentInfo = json_decode($extension->manifest_cache, TRUE);
    $shopVersion = $componentInfo['version'];
    $minVirtueMartVersion = (string) $parent->get("manifest")->minVirtueMartVersion;
    if (version_compare($shopVersion, $minVirtueMartVersion, '<')) {
      JInstaller::getInstance()->abort("The Acumulus component $version requires at least VirtueMart $minVirtueMartVersion.");
      return FALSE;
    }

    // Check extension requirements.
    // Get access to our classes via the auto loader.
    JLoader::registerNamespace('Siel', dirname(__FILE__) . '/admin/libraries');
    $errors = Requirements::check();
    if (!empty($errors)) {
      JInstaller::getInstance()->abort(implode(' ', $errors));
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Method to run after an install/update/uninstall method
   * $parent is the class calling this method
   * $type is the type of change (install, update or discover_install)
   *
   * @param string $type
   * @param object $parent
   */
  public function postflight($type, $parent) {
  }
}
