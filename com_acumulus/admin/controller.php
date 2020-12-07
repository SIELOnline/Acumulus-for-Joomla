<?php
/** @noinspection PhpUnused
 *
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
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
    protected function getAcumulusModel()
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
     * @throws \Exception
     */
    public function display($cachable = false, $urlparams = array())
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
     * @throws \Exception
     */
    public function batch()
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
     * @throws \Exception
     */
    public function config()
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
     * @throws \Exception
     */
    public function advanced()
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
     * @throws \Exception
     */
    public function register()
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
     * @return \JControllerLegacy
     *
     * @throws \Exception
     */
    public function invoice($orderId = null)
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_acumulus'))
        {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }
        if ($orderId !== null) {
            JFactory::getDocument()->addScript(JURI::root(true) . '/administrator/components/com_acumulus/acumulus-ajax.js');
            $this->task = 'invoice';
            $orgView = $this->input->get('view');
            $this->input->set('view', null);

            /** @var \Siel\Acumulus\Shop\InvoiceStatusForm $form */
            $form = $this->getAcumulusModel()->getForm($this->getTask());
            $form->setSource($this->getAcumulusModel()->getAcumulusContainer()->getSource(Source::Order, $orderId));
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
     * @return \JControllerLegacy
     *
     * @throws \Exception
     */
    protected function executeTask()
    {
        $form = $this->getAcumulusModel()->getForm($this->task);
        if ($form->isSubmitted()) {
            JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        }
        $form->process();
        // Force the creation of the fields to get connection error messages
        // shown.
        $form->getFields();

        // Show messages.
        foreach ($form->getMessages() as $message) {
            JFactory::getApplication()->enqueueMessage(
                $message->format(Message::Format_PlainWithSeverity),
                $this->getJoomlaMessageType($message->getSeverity())
            );
        }

        // Check for serious errors.
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
    protected function getJoomlaMessageType($severity)
    {
        switch ($severity) {
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
        $extensionTable = new JtableExtension(JFactory::getDbo());
        $extensionTable->load(array('element' => 'com_acumulus'));
        $manifest_cache = $extensionTable->get('manifest_cache');
        $manifest_cache = json_decode($manifest_cache);
        if (!empty($manifest_cache->version) && $this->getAcumulusModel()->getAcumulusConfig()->upgrade($manifest_cache->version)) {
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
