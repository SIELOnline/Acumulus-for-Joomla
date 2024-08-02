<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

declare(strict_types=1);

namespace Siel\Joomla\Component\Acumulus\Administrator\View\Acumulus;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Siel\Joomla\Component\Acumulus\Administrator\Model\AcumulusModel;

/**
 * Acumulus HTML view.
 */
class HtmlView extends BaseHtmlView
{
    use ViewTrait;

    /**
     * @param string|null $tpl
     *
     * @throws \Exception
     */
    public function display($tpl = null): void
    {
        echo $this->getContent();

        /** @var AcumulusModel $acumulusModel */
        $acumulusModel = $this->getModel();
        if ($acumulusModel->getAcumulusForm($this->type)->isFullPage()) {
            $this->addToolBar();
            $this->setDocumentTitle($this->t($this->type . '_form_title'));
        }
    }

    /**
     * Add the page title and toolbar.
     *
     * @throws \Exception
     */
    protected function addToolBar(): void
    {
        // Show Joomla Administrator Main menu.
        Factory::getApplication()->input->set('hidemainmenu', false);

        // Add title and buttons.
        $type = $this->type;
        ToolbarHelper::title($this->t("{$type}_form_header"), 'acumulus');
        ToolbarHelper::save($type, $this->t("button_submit_$type"));
        ToolbarHelper::cancel('cancel', $this->t('button_cancel'));
    }
}
