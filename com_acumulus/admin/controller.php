<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Manifest\PackageManifest;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Uri\Uri;
use Siel\Acumulus\Helpers\Message;
use Siel\Acumulus\Helpers\Severity;
use Siel\Acumulus\Invoice\Source;

defined('_JEXEC') or die;

/**
 * Controller of the Acumulus component.
 */
class AcumulusController extends BaseController
{
    /** @var AcumulusModelAcumulus */
    protected $model;

    /**
     * @var \Joomla\CMS\Application\CMSApplication|null
     *   J4 only: CMSApplicationInterface
     */
    protected $app = null;

    /**
     * @return \Joomla\CMS\Application\CMSApplication
     *
     * @throws \Exception
     */
    public function getApp(): CMSApplication
    {
        if ($this->app === null) {
            $this->app = Factory::getApplication();
        }
        return $this->app;
    }

    /**
     * @param string $action
     * @param string $assetName
     *
     * @return void
     *
     * @throws \Exception
     *   When the user is not authorised to perform the demanded action.
     */
    public function checkAuthorisation(string $action = 'core.admin', string $assetName = 'com_acumulus'): void
    {
        if (!$this->getApp()->getIdentity()->authorise($action, $assetName)) {
            throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
        }
    }

    /**
     * @return AcumulusModelAcumulus
     */
    protected function getAcumulusModel(): AcumulusModelAcumulus
    {
        if ($this->model === null) {
            $this->model = $this->getModel('Acumulus', '', array('name' => 'Acumulus'));
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
    public function display($cachable = false, $urlparams = array()): BaseController
    {
        if (empty($this->task)) {
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
            $form = $this->getAcumulusModel()->getForm($this->task);
            if ($orderId !== null) {
                /**
                 * @noinspection PhpDeprecationInspection  method is not
                 *   deprecated, only a variant with a different set of
                 *   parameters.
                 */
                Factory::getDocument()->addScript(URI::root(true) . '/administrator/components/com_acumulus/acumulus-ajax.js');
                $this->input->set('view', null);
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
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
            $this->default_view = '';
            $this->display();
        } catch (Throwable $e) {
            try {
                $crashReporter = $this->getAcumulusModel()->getCrashReporter();
                $message = $crashReporter->logAndMail($e);
                Factory::getApplication()->enqueueMessage($message, 'error');
            } catch (Throwable $inner) {
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
     *
     * @noinspection PhpReturnDocTypeMismatchInspection
     * @return \Joomla\CMS\MVC\View\ViewInterface|\Joomla\CMS\MVC\View\HtmlView
     *   Joomla4: ViewInterface; Joomla3: HtmlView
     */
    public function getView($name = '', $type = '', $prefix = '', $config = array())
    {
        $config['type'] = $this->task;
        $config['isJson'] = $this->input->get('ajax') == 1;
        return parent::getView($name, $type, $prefix, $config);
    }

    /**
     * Executes the com_acumulus/update task and redirects.
     *
     * @throws \Exception
     */
    public function update()
    {
        // J4: $extensionTable = new Extension(Factory::getContainer()->get('DatabaseDriver'));
        /** @noinspection PhpDeprecationInspection */
        $extensionTable = new Extension(Factory::getDbo());
        $extensionTable->load(array('element' => 'com_acumulus'));
        $manifest_cache = $extensionTable->get('manifest_cache');
        $manifest_cache = json_decode($manifest_cache);
        if (!empty($manifest_cache->version) && $this->getAcumulusModel()->getAcumulusConfigUpgrade()->upgrade($manifest_cache->version)) {
            $manifest = new PackageManifest(__DIR__ . '/acumulus.xml');
            $manifest_cache->version = $manifest->version;
            // Reload as the upgrade may have changed the config.
            $extensionTable->load(array('element' => 'com_acumulus'));
            $extensionTable->set('manifest_cache', json_encode($manifest_cache));
            $extensionTable->store();
            $this->setRedirect(Route::_('index.php?option=com_installer&view=manage', false), 'Module upgraded');
        } else {
            $this->setRedirect(Route::_('index.php?option=com_installer&view=manage', false), 'Module not upgraded');
        }
        $this->redirect();
    }
}
