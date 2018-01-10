<?php
/**
 * @author      Buro RaDer (i.o.v. SIEL - Acumulus) http://www.burorader.com/
 * @copyright   SIEL BV https://www.siel.nl/acumulus/
 * @license     GPLv3
 */

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
        return $this->executeTask();
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
        $form->process();

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
        if (count($errors = $this->get('Errors'))) {
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
