<?php
/**
 * @copyright   Buro RaDer.
 * @license     GPLv3; see license.txt.
 *
 * Main entry point for Joomla to the Acumulus component.
 * 
 * This file has side effects, so checking if Joomla has been initialized is in place.
 */
defined('_JEXEC') or die('Restricted access');

// Get access to our classes via the auto loader.
JLoader::registerNamespace('Siel', dirname(__FILE__) . '/libraries');

// Set some global property.
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-acumulus {background-image: url(./com_acumulus/media/logo-acumulus-16.png);}');

// Get an instance of the controller prefixed by Acumulus.
$controller = JControllerLegacy::getInstance('Acumulus');

// Perform the Request task.
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller.
$controller->redirect();
