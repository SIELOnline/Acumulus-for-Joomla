<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 */

declare(strict_types=1);

namespace Siel\Joomla\Component\Acumulus\Administrator\View\Acumulus;

use Joomla\CMS\MVC\View\JsonView as BaseJsonView;
use Joomla\CMS\Response\JsonResponse;

/**
 * Acumulus JSON view.
 */
class JsonView extends BaseJsonView
{
    use ViewTrait;

    /**
     * @throws \Exception
     */
    public function display($tpl = null): void
    {
        $output = $this->getContent();
        $output = new JsonResponse($output);
        echo $output;
    }

}
