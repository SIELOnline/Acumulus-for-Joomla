<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

defined('_JEXEC') or die;

/**
 * Generic Acumulus view.
 */
class AcumulusViewAcumulus extends JViewLegacy
{
    /** @var string */
    protected $action;

    /** @var string */
    protected $task;

    /** @var string */
    protected $saveButton;

    /** @var \Siel\Acumulus\Helpers\Form */
    protected $form;

    public function __construct($config = array())
    {
        $this->task = $config['task'];
        $this->action = "index.php?option=com_acumulus&task={$this->task}";
        /** @noinspection PhpUndefinedFieldInspection */
        $this->_defaultModel = 'Acumulus';
        $this->saveButton = $this->task === 'batch' ? 'button_send' : 'button_save';
        parent::__construct($config);
    }

    /**
     * Override only for improved type checking and auto complete.
     *
     * @param string|null $name
     *
     * @return AcumulusModelAcumulus
     */
    public function getModel($name = null)
    {
        return parent::getModel($name);
    }

    /**
     * Helper method to translate strings.
     *
     * @param string $key
     *  The key to get a translation for.
     *
     * @return string
     *   The translation for the given key or the key itself if no translation
     *   could be found.
     */
    protected function t($key)
    {
        return $this->getModel()->t($key);
    }

    /**
     * Display the Acumulus view
     *
     * @param string $tpl
     *   The name of the template file to parse; automatically searches through
     *   the template paths.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function display($tpl = null)
    {
        // Get the config form.
        $this->form = $this->getModel()->getForm($this->task);
        // Get the fields and their values for display.
        $this->form->addValues();

        // Display the template.
        $this->_addPath('template', __DIR__);
        parent::display($tpl);

        $this->addToolBar();
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     * @throws \Exception
     */
    protected function addToolBar()
    {
        // Show Joomla Administrator Main menu.
        $input = JFactory::getApplication()->input;
        $input->set('hidemainmenu', false);

        // Add title and buttons.
        JToolbarHelper::title($this->t($this->task . '_form_header'), 'acumulus');
        JToolbarHelper::save($this->task, $this->t($this->saveButton));
        JToolbarHelper::cancel('cancel', $this->t('button_cancel'));
    }

    /**
     * Method to set up the document properties.
     */
    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle($this->t($this->task . '_form_title'));
        $document->addStyleDeclaration('.icon-acumulus ' . '{background-image: url(./components/com_acumulus/media/logo-acumulus-16.png);}');
    }
}
