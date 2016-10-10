<?php
/**
 * @copyright   Buro RaDer.
 * @license     GPLv3; see license.txt.
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
     */
    public function display($cachable = false, $urlparams = array())
    {
        return $this->batch();
    }

    /**
     * Executes the com_acumulus/batch task.
     *
     * @return \JControllerLegacy
     */
    public function batch()
    {
        return $this->executeTask('batch');
    }

    /**
     * Executes the com_acumulus/config task.
     *
     * @return \JControllerLegacy
     */
    public function config()
    {
        return $this->executeTask('config');
    }

    /**
     * Executes the com_acumulus/advanced task.
     *
     * @return \JControllerLegacy
     */
    public function advanced()
    {
        return $this->executeTask('advanced');
    }

    /**
     * Executes the given task.
     *
     * @param string $task
     *
     * @return \JControllerLegacy
     *
     * @throws \Exception
     */
    protected function executeTask($task)
    {
        /** @var AcumulusModelAcumulus $model */
        $form = $this->getModel('Acumulus')->getForm($task);
        $form->process();

        // Show messages.
        foreach ($form->getSuccessMessages() as $message) {
            JFactory::getApplication()->enqueueMessage($message, 'message');
        }
        foreach ($form->getErrorMessages() as $message) {
            JFactory::getApplication()->enqueueMessage($message, 'error');
        }

        // Check for serious errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode('<br />', $errors), 500);
        }

        $this->default_view = $task;
        return parent::display();
    }
}
