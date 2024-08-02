<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

declare(strict_types=1);

namespace Siel\Joomla\Component\Acumulus\Administrator\View\Acumulus;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Siel\Joomla\Component\Acumulus\Administrator\Model\AcumulusModel;

/**
 * Generic code for an Acumulus View.
 */
trait ViewTrait
{
    protected string $type;

    public function __construct(array $config = [])
    {
        $this->type = $config['type'] ?? 'batch';
        /** @noinspection PhpMultipleClassDeclarationsInspection */
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
     * Returns the content for an Acumulus view.
     *
     * @throws \Exception
     */
    public function getContent(): string
    {
        if ($this->type === 'cancel') {
            Factory::getApplication()->redirect(Uri::root(true) . '/administrator/index.php');
        }

        /** @var AcumulusModel $acumulusModel */
        $acumulusModel = $this->getModel();

        // Add styling.
        $document = Factory::getApplication()->getDocument();
        $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_acumulus/media/acumulus.css');
        if ($acumulusModel->isVirtueMart) {
            $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_acumulus/media/acumulus-vm.css');
        }
        if ($acumulusModel->isHikaShop) {
            $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_acumulus/media/acumulus-hs.css');
        }

        // Get and populate the form.
        $form = $acumulusModel->getAcumulusForm($this->type);
        $form->addValues();

        $type = $this->type;
        $action = "index.php?option=com_acumulus&task=$type";
        $id = "acumulus-$type";
        $wait = $this->t('wait');
        $token = Session::getFormToken();

        if ($form->isFullPage()) {
            $wrapperBefore = "<form id='adminForm' action='$action' method='post' class='adminform form-horizontal acumulus-area'>";
            $wrapperAfter = HTMLHelper::_('form.token') . '</form>';
        } else {
            $wrapperBefore = "<div id='$id' class='form-horizontal acumulus-area' "
                . "data-acumulus-url='$action' data-acumulus-token='$token' data-acumulus-wait='$wait' >";
            $wrapperAfter = '</div>';
        }

        $output = '';
        $output .= $wrapperBefore;
        $output .= $acumulusModel->getAcumulusFormRenderer()->render($form);
        $output .= "<input type='hidden' name='task' value='$this->type'>";
        $output .= $wrapperAfter;

        return $output;
    }
}
