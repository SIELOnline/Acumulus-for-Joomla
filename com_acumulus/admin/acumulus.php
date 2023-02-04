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

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// Get an instance of the controller prefixed by Acumulus.
/** @noinspection PhpUnhandledExceptionInspection */
$app = Factory::getApplication();
require_once __DIR__ . '/controller.php';
// @todo: controller accepts 4 params in J4? If not remove.
$controller = new AcumulusController([], null, $app, $app->input);

// Perform the requested task.
/** @noinspection PhpUnhandledExceptionInspection */
$controller->execute($app->input->getCmd('task'));

// Redirect if set by the controller.
$controller->redirect();
