<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

use Joomla\CMS\Installer\Manifest\PackageManifest;

defined('_JEXEC') or die;

/**
 * Controller of the Acumulus component.
 */
class AcumulusController extends JControllerLegacy
{
    /** @var AcumulusModelAcumulus */
    protected $model;

    /**
     * @param string $name
     * @param string $prefix
     * @param array $config
     *
     * @return AcumulusModelAcumulus
     */
    public function getModel($name = '', $prefix = '', $config = array())
    {
        if ($this->model === null) {
            $this->model = parent::getModel('Acumulus', $prefix, $config + array('name' => $name));
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
        $this->task = 'batch';
        return $this->executeTask();
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
        return $this->executeTask();
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
        return $this->executeTask();
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
        return $this->executeTask();
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
        if (!empty($manifest_cache->version) && $this->getModel('Acumulus')->getAcumulusConfig()->upgrade($manifest_cache->version)) {
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

    /**
     * Executes the given task.
     *
     * @return \JControllerLegacy
     *
     * @throws \Exception
     */
    protected function executeTask()
    {

        /** @var AcumulusModelAcumulus $model */
        $form = $this->getModel('Acumulus')->getForm($this->task);
        if ($form->isSubmitted()) {
            JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        }
        $form->process();
        // Force the creation of the fields to get connection error messages
        // shown.
        $form->getFields();

        // Show messages.
        foreach ($form->getSuccessMessages() as $message) {
            JFactory::getApplication()->enqueueMessage($message, 'message');
        }
        foreach ($form->getWarningMessages() as $message) {
            JFactory::getApplication()->enqueueMessage($message, 'warning');
        }
        foreach ($form->getErrorMessages() as $message) {
            JFactory::getApplication()->enqueueMessage($message, 'error');
        }

        // Check for serious errors.
        $errors = $this->getErrors();
        if (count($errors) > 0) {
            throw new Exception(implode('<br />', $errors), 500);
        }

        $this->default_view = '';
        return parent::display();
    }

    /**
     * @inheritDoc
     */
    public function getView($name = '', $type = '', $prefix = '', $config = array())
    {
        $config['task'] = $this->task;
        return parent::getView($name, $type, $prefix, $config);
    }


}
