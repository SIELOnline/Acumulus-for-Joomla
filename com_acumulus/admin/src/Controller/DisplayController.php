<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

declare(strict_types=1);

namespace Siel\Joomla\Component\Acumulus\Administrator\Controller;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Manifest\PackageManifest;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\View\ViewInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use RuntimeException;
use Siel\Acumulus\Helpers\Message;
use Siel\Acumulus\Helpers\Severity;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Meta;
use Siel\Joomla\Component\Acumulus\Administrator\Model\AcumulusModel;
use Throwable;

/**
 * Controller of the Acumulus component.
 *
 * @noinspection PhpUnused
 */
class DisplayController extends BaseController
{
    protected AcumulusModel $model;

    /**
     * @param array $config
     */
    public function __construct(
        $config = [],
        MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);
        $this->default_view = 'acumulus';
    }

    /**
     * @throws \Exception
     */
    protected function getApp(): CMSApplicationInterface
    {
        return $this->app;
    }

    /**
     * @throws \Exception
     *   When the user is not authorised to perform the demanded action.
     */
    public function checkAuthorisation(string $action = 'core.admin', string $assetName = 'com_acumulus'): void
    {
        if (!$this->getApp()->getIdentity()->authorise($action, $assetName)) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
        }
    }

    /**
     * @return AcumulusModel
     */
    protected function getAcumulusModel(): AcumulusModel
    {
        if (!isset($this->model)) {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->model = $this->getModel('Acumulus', 'Administrator', ['name' => 'Acumulus']);
        }
        return $this->model;
    }

    /**
     * Executes the default task (batch).
     *
     * @param bool $cachable
     * @param array $urlparams
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function display($cachable = false, $urlparams = []): BaseController
    {
        if (empty($this->task) || $this->task === 'display') {
            $this->task = 'batch';
            $this->batch();
        } else {
            parent::display($cachable, $urlparams);
        }
        return $this;
    }

    /**
     * Executes the com_acumulus/batch task.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function batch(): BaseController
    {
        $this->checkAuthorisation('core.create');
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/settings task.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function settings(): BaseController
    {
        $this->checkAuthorisation();
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/mappings task.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function mappings(): BaseController
    {
        $this->checkAuthorisation();
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/config task.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function config(): BaseController
    {
        $this->checkAuthorisation();
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/advanced task.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function advanced(): BaseController
    {
        $this->checkAuthorisation();
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/register task.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function register(): BaseController
    {
        $this->checkAuthorisation();
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/activate task.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function activate(): BaseController
    {
        $this->checkAuthorisation();
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/invoice task.
     *
     * @param int|null $orderId
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    public function invoice(?int $orderId = null): BaseController
    {
        $this->checkAuthorisation();
        if ($orderId !== null) {
            $orgView = $this->input->get('view');
        }
        $this->task = 'invoice';
        $this->executeTask($orderId);
        if ($orderId !== null) {
            $this->input->set('view', $orgView);
        }
        return $this;
    }

    /**
     * Executes the given task.
     *
     * @param int|null $orderId
     *   If the form needs an orderId it can be passed via this parameter.
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     *
     * @throws \Throwable
     */
    protected function executeTask(?int $orderId = null): BaseController
    {
        try {
            $form = $this->getAcumulusModel()->getAcumulusForm($this->task);
            if ($orderId !== null) {
                /**
                 * @noinspection PhpPossiblePolymorphicInvocationInspection  defined in
                 *   {@see \Joomla\CMS\Application\CMSWebApplicationInterface}
                 */
                Factory::getApplication()->getDocument()->addScript(
                    Uri::root(true) . '/administrator/components/com_acumulus/media/acumulus-ajax.js'
                );
                $this->input->set('view', null);
                /** @noinspection PhpPossiblePolymorphicInvocationInspection  $form instanceof {@see InvoiceStatusForm} */
                $form->setSource($this->getAcumulusModel()->getSource(Source::Order, $orderId));
            }
            if ($form->isSubmitted()) {
                Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
            }
            $form->process();
            // Force the creation of the fields to get connection error messages
            // shown.
            $form->getFields();
            // Show messages.
            foreach ($form->getMessages() as $message) {
                Factory::getApplication()->enqueueMessage(
                    $message->format(Message::Format_PlainWithSeverity),
                    $this->getJoomlaMessageType($message->getSeverity())
                );
            }
            // Display form.
            $this->display();
        } catch (Throwable $e) {
            try {
                $crashReporter = $this->getAcumulusModel()->getCrashReporter();
                $message = $crashReporter->logAndMail($e);
                Factory::getApplication()->enqueueMessage($message, 'error');
            } catch (Throwable) {
                // We do not know if we have informed the user per mail or
                // screen, so assume not, and rethrow the original exception.
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Returns the joomla equivalent of the severity.
     *
     * @param int $severity
     *   One of the Severity::... constants.
     *
     * @return string
     *   the Joomla message type equivalent of the severity.
     */
    protected function getJoomlaMessageType(int $severity): string
    {
        switch ($severity) {
            case Severity::Log:
            case Severity::Success:
            default:
                return 'message';
            case Severity::Info:
            case Severity::Notice:
                return 'notice';
            case Severity::Warning:
                return 'warning';
            case Severity::Error:
            case Severity::Exception:
                return 'error';
        }
    }

    /**
     * @inheritDoc
     */
    public function getView($name = '', $type = '', $prefix = '', $config = []): ViewInterface
    {
        $config['type'] = $this->task;
        return parent::getView($name, $type, $prefix, $config);
    }

    /**
     * Executes the com_acumulus/update task and redirects.
     *
     * @throws \Exception
     */
    public function update(): void
    {
        $extensionTable = $this->factory->createTable('Extension', 'Administrator');
        $extensionTable->load(['element' => 'com_acumulus']);
        $manifest_cache = $extensionTable->manifest_cache;
        $manifest_cache = json_decode($manifest_cache, false, 512, JSON_THROW_ON_ERROR);
        if (!empty($manifest_cache->version) && $this->getAcumulusModel()->getAcumulusConfigUpgrade()->upgrade($manifest_cache->version)) {
            $manifest = new PackageManifest(__DIR__ . '/acumulus.xml');
            $manifest_cache->version = $manifest->version;
            // Reload as the upgrade may have changed the config.
            $extensionTable->load(['element' => 'com_acumulus']);
            $extensionTable->manifest_cache = json_encode($manifest_cache, Meta::JsonFlags);
            $extensionTable->store();
            $this->setRedirect(Route::_('index.php?option=com_installer&view=manage', false), 'Module upgraded');
        } else {
            $this->setRedirect(Route::_('index.php?option=com_installer&view=manage', false), 'Module not upgraded');
        }
        $this->redirect();
    }
}
