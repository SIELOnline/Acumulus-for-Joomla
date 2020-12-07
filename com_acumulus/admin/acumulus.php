<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * Main entry point for Joomla to the Acumulus component.
 *
 * This file has side effects, so checking if Joomla has been initialized is in
 * place.
 */

defined('_JEXEC') or die;

// Get an instance of the controller prefixed by Acumulus.
/** @noinspection PhpUnhandledExceptionInspection */
$controller = JControllerLegacy::getInstance('Acumulus');

// Perform the Request task.
/** @noinspection PhpUnhandledExceptionInspection */
$controller->execute(JFactory::getApplication()->input->getCmd('task'));

// Redirect if set by the controller.
$controller->redirect();
