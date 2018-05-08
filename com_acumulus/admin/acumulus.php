<?php
/**
 * @author    Buro RaDer, http://www.burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * Main entry point for Joomla to the Acumulus component.
 *
 * This file has side effects, so checking if Joomla has been initialized is in place.
 */

defined('_JEXEC') or die;

// Get access to our classes via the auto loader.
JLoader::registerNamespace('Siel\\Acumulus', __DIR__ . '/lib/siel/acumulus/src', false, false, 'psr4');

// Set some global property.
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-acumulus {background-image: url(./com_acumulus/media/logo-acumulus-16.png);}');

// Get an instance of the controller prefixed by Acumulus.
/** @noinspection PhpUnhandledExceptionInspection */
$controller = JControllerLegacy::getInstance('Acumulus');

// Perform the Request task.
/** @noinspection PhpUnhandledExceptionInspection */
$input = JFactory::getApplication()->input;
/** @noinspection PhpUnhandledExceptionInspection */
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller.
$controller->redirect();