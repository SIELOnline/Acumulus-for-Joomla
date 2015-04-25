<?php
// No direct access to this file
defined('_JEXEC') or die;

use Siel\Acumulus\Helpers\Requirements;

/**
 * Script file of HelloWorld module
 */
class mod_acumulusInstallerScript {

  /**
   * Method to install the extension
   * $parent is the class calling this method
   *
   * @return void
   */
  function install($parent) {
    echo '<p>The Acumulus component has been installed. Please visit the settings form.</p>';
  }

  /**
   * Method to uninstall the extension
   * $parent is the class calling this method
   *
   * @return void
   */
  function uninstall($parent) {
    echo '<p>The Acumulus component has been uninstalled</p>';
  }

  /**
   * Method to update the extension
   * $parent is the class calling this method
   *
   * @return void
   */
  function update($parent) {
    echo '<p>The Acumulus component has been updated to version' . $parent->get('manifest')->version . '.</p>';
  }

  /**
   * Method to run before an install/update/uninstall method
   * $parent is the class calling this method
   * $type is the type of change (install, update or discover_install)
   *
   * @return bool
   */
  function preflight($type, $parent) {
    if (in_array($type, array('install', 'update'))) {
      // Check if VirtueMart is installed.
      jimport('joomla.application.component.controller');
      if (!JComponentHelper::isEnabled('com_virtuemart')) {
        $installer = JInstaller::getInstance();
        $installer->abort('The Acumulus component requires VirtueMart to be installed and enabled.');
        return FALSE;
      }

      // Get access to our classes via the auto loader.
      JLoader::registerNamespace('Siel', dirname(__FILE__) . '/libraries');
      $errors = Requirements::check();
      if (empty($errors)) {
        $installer = JInstaller::getInstance();
        $installer->abort(implode(' ', $errors));
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Method to run after an install/update/uninstall method
   * $parent is the class calling this method
   * $type is the type of change (install, update or discover_install)
   *
   * @return void
   */
  function postflight($type, $parent) {
  }
}
