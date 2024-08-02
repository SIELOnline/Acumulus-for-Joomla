<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * Main entry point for Joomla to the Acumulus component.
 */

declare(strict_types=1);

namespace Siel\Joomla\Component\Acumulus\Administrator\Extension;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Psr\Container\ContainerInterface;
use Siel\Joomla\Component\Acumulus\Administrator\Controller\DisplayController;
use Siel\Joomla\Component\Acumulus\Administrator\Model\AcumulusModel;
use Siel\Joomla\Component\Acumulus\Administrator\Table\AcumulusEntryTable;

use function dirname;

/**
 * AcumulusComponent contains tasks and actions to set up the Acumulus component.
 *
 * @noinspection PhpUnused
 */
class AcumulusComponent extends MVCComponent implements BootableExtensionInterface
{
    private AcumulusModel $model;
    private DisplayController $controller;

    /**
     * Boots the extension.
     *
     * This is the function to set up the environment of the extension like registering
     * new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, e.g.
     * registering HTML services.
     */
    public function boot(ContainerInterface $container): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->setMVCFactory($container->get(MVCFactoryInterface::class));
        require_once dirname(__FILE__, 3) . '/vendor/autoload.php';
    }

    /**
     * Returns a (Joomla MVC) Acumulus model.
     */
    public function getAcumulusModel(): AcumulusModel
    {
        if (!isset($this->model)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->model = $this->getMVCFactory()->createModel('Acumulus', 'Administrator');
        }
        return $this->model;
    }

    /**
     * Returns the (Joomla MVC) Acumulus display controller.
     */
    public function getAcumulusController(): DisplayController
    {
        if (!isset($this->controller)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->controller = $this->getMVCFactory()->createController(
                'Display',
                'Administrator',
                [],
                Factory::getApplication(),
                Factory::getApplication()->getInput()
            );
        }
        return $this->controller;
    }

    /**
     * Returns a (Joomla MVC) AcumulusEntry table object.
     *
     * @throws \Exception
     */
    public function getAcumulusEntryTable(): AcumulusEntryTable
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getMVCFactory()->createTable('AcumulusEntry', 'Administrator');
    }
}
