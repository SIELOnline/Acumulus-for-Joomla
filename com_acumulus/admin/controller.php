<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * @noinspection PhpUnused
 */

use Joomla\CMS\Installer\Manifest\PackageManifest;
use Siel\Acumulus\Helpers\Message;
use Siel\Acumulus\Helpers\Severity;
use Siel\Acumulus\Invoice\Source;

defined('_JEXEC') or die;

/**
 * Controller of the Acumulus component.
 */
class AcumulusController extends JControllerLegacy
{
    /** @var AcumulusModelAcumulus */
    protected $model;

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
     * @return \JControllerLegacy
     *
     * @throws \Throwable
     */
    public function display($cachable = false, $urlparams = array()): JControllerLegacy
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
     * @return \JControllerLegacy
     *
     * @throws \Throwable
     */
    public function batch(): JControllerLegacy
    {
        if (!JFactory::getUser()->authorise('core.create', 'com_acumulus'))
        {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/config task.
     *
     * @return \JControllerLegacy
     *
     * @throws \Throwable
     */
    public function config(): JControllerLegacy
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_acumulus'))
        {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/advanced task.
     *
     * @return \JControllerLegacy
     *
     * @throws \Throwable
     */
    public function advanced(): JControllerLegacy
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_acumulus'))
        {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/register task.
     *
     * @return \JControllerLegacy
     *
     * @throws \Throwable
     */
    public function register(): JControllerLegacy
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_acumulus'))
        {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->executeTask();
        return $this;
    }

    /**
     * Executes the com_acumulus/invoice task.
     *
     * @param int|null $orderId
     *
     * @return \JControllerLegacy
     *
     * @throws \Throwable
     */
    public function invoice(?int $orderId = null): JControllerLegacy
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_acumulus'))
        {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }
        if ($orderId !== null) {
            $orgView = $this->input->get('view');
        }
        $this->executeTask();
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
     * @return \JControllerLegacy
     *
     * @throws \Throwable
     */
    protected function executeTask(?int $orderId = null): JControllerLegacy
    {
        try {
            if ($orderId !== null) {
                /**
                 * @noinspection PhpDeprecationInspection  method is not deprecated,
                 *   only a variant with a different set of parameters.
                 */
                JFactory::getDocument()->addScript(JURI::root(true) . '/administrator/components/com_acumulus/acumulus-ajax.js');
                $this->task = 'invoice';
                $this->input->set('view', null);

                /** @var \Siel\Acumulus\Shop\InvoiceStatusForm $form */
                $form = $this->getAcumulusModel()->getForm($this->getTask());
                $form->setSource($this->getAcumulusModel()->getSource(Source::Order, $orderId));
            }
            $form = $this->getAcumulusModel()->getForm($this->task);
            if ($form->isSubmitted()) {
                JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
            }
            $form->process();// Force the creation of the fields to get connection error messages
            // shown.
            $form->getFields();// Show messages.
            foreach ($form->getMessages() as $message) {
                JFactory::getApplication()->enqueueMessage(
                    $message->format(Message::Format_PlainWithSeverity),
                    $this->getJoomlaMessageType($message->getSeverity())
                );
            }
        } catch (Throwable $e) {
            try {
                $crashReporter = $this->getAcumulusModel()->getCrashReporter();
                $message = $crashReporter->logAndMail($e);
                JFactory::getApplication()->enqueueMessage($message, 'error');
            } catch (Throwable $inner) {
                // We do not know if we have informed the user per mail or
                // screen, so assume not, and rethrow the original exception.
                throw $e;
            }
        }

        // Check for serious errors.
        /** @noinspection PhpDeprecationInspection  @todo: how to replace this? */
        $errors = $this->getErrors();
        if (count($errors) > 0) {
            throw new Exception(implode('<br />', $errors), 500);
        }

        $this->default_view = '';
        $this->display();
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
    public function getView($name = '', $type = '', $prefix = '', $config = array()): JViewLegacy
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
        $extensionTable = new JtableExtension(JFactory::getDbo());
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
            $this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false), 'Module upgraded');
        } else {
            $this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false), 'Module not upgraded');
        }
        $this->redirect();
    }
}
