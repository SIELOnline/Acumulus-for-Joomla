<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Get access to our classes via the auto loader.
JLoader::registerNamespace('Siel', dirname(__FILE__) . '/libraries');

// Set some global property
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-acumulus {background-image: url(./com_acumulus/media/logo-acumulus-16.png);}');

// Load VirtueMart: we need access to VirtueMart models and data.
// Copied from administrator/components/com_virtuemart/virtuemart.php
if (!class_exists('VmConfig')) {
  require_once(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
}
VmConfig::loadConfig();

if (!class_exists('VmController')) {
  require_once(VMPATH_ADMIN . '/helpers/vmcontroller.php');
}
if (!class_exists('VmModel')) {
  require(VMPATH_ADMIN . '/helpers/vmmodel.php');
}
// End of load VirtueMart.

// Get an instance of the controller prefixed by Acumulus
$controller = JControllerLegacy::getInstance('Acumulus');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
