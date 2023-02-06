<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * @noinspection AutoloadingIssuesInspection
 */

declare(strict_types=1);

use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * Generic Acumulus view.
 */
class AcumulusView extends JViewLegacy
{
    protected string $type;
    protected bool $isJson;

    public function __construct(array $config = [])
    {
        $this->type = $config['type'];
        $this->isJson = $config['isJson'];
        $this->_defaultModel = 'Acumulus';
        parent::__construct($config);
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
    protected function t(string $key): string
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
     *
     * @noinspection PhpDeprecationInspection  Document::addStyleSheet():
     *   The (url, mime, media, attribs) method signature is deprecated, use
     *   (url, options, attributes) instead.
     *  @noinspection PhpMissingParentCallCommonInspection  We do not use a template.
     */
    public function display($tpl = null): void
    {
        if ($this->type === 'cancel') {
            JFactory::getApplication()->redirect(JUri::root(true) . '/administrator/index.php');
        }

        /** @var \AcumulusModelAcumulus $acumulusModel */
        $acumulusModel = $this->getModel();

        // Add styling.
        $document = JFactory::getApplication()->getDocument();
        $document->addStyleSheet(JUri::root(true) . '/administrator/components/com_acumulus/acumulus.css');
        if ($acumulusModel->isVirtueMart) {
            $document->addStyleSheet(JUri::root(true) . '/administrator/components/com_acumulus/acumulus-vm.css');
        }
        if ($acumulusModel->isHikaShop) {
            $document->addStyleSheet(JUri::root(true) . '/administrator/components/com_acumulus/acumulus-hs.css');
        }

        // Get and populate the form.
        $form = $acumulusModel->getForm($this->type);
        $form->addValues();

        $type = $this->type;
        $action = "index.php?option=com_acumulus&task=$type";
        $id = "acumulus-$type";
        $wait = $this->t('wait');
        $token = JSession::getFormToken();

        if ($form->isFullPage()) {
            $wrapperBefore = "<form id='adminForm' action='$action' method='post' class='adminform form-horizontal acumulus-area'>";
            $wrapperAfter = JHtml::_('form.token') . '</form>';
        } else {
            $wrapperBefore = "<div id='$id' class='form-horizontal acumulus-area' "
            . "data-acumulus-url='$action' data-acumulus-token='$token' data-acumulus-wait='$wait' >";
            $wrapperAfter = '</div>';
        }

        $output = '';
        $output .= $wrapperBefore;
        $output .= $acumulusModel->getFormRenderer()->render($form);
        $output .= "<input type='hidden' name='task' value='$this->type'>";
        $output .= $wrapperAfter;

        if ($this->isJson) {
            $output = new JResponseJson($output);
        }
        echo $output;

        if ($form->isFullPage()) {
            $this->addToolBar();
            $this->setDocumentTitle($this->t($this->type . '_form_title'));
        }
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     * @throws \Exception
     */
    protected function addToolBar(): void
    {
        // Show Joomla Administrator Main menu.
        JFactory::getApplication()->input->set('hidemainmenu', false);

        // Add title and buttons.
        $type = $this->type;
        ToolbarHelper::title($this->t("{$type}_form_header"), 'acumulus');
        ToolbarHelper::save($type, $this->t("button_submit_$type"));
        ToolbarHelper::cancel('cancel', $this->t('button_cancel'));
    }
}
