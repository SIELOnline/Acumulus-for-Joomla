<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Siel\Joomla\Component\Acumulus\Administrator\Extension\AcumulusComponent;

defined('_JEXEC') or die;

/**
 * Acumulus plugin to hide menu-items, from the backend Components menu, based on state.
 *
 * @noinspection PhpUnused  Plugins are instantiated dynamically.
 */
class plgSystemAcumulus extends CMSPlugin
{
    private function getAcumulusComponent(): AcumulusComponent
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Factory::getApplication()->bootComponent('acumulus');
    }

    public function isAllowed(string $action = 'core.admin', string $assetName = 'com_acumulus'): bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return Factory::getApplication()->getIdentity()->authorise($action, $assetName);
    }

    /**
     * @noinspection PhpUnused  event handler: not called directly.
     */
    public function onPreprocessMenuItems(string $name, array $items = []/*, ?array $params = null, bool $enabled = true*/): void
    {
        if ($name !== 'com_menus.administrator.module') {
            return;
        }

        $remove = [];
        foreach ($items as $key => $item) {
            $usesNewCode = $this->getAcumulusComponent()->getAcumulusModel()->getAcumulusContainer()->getShopCapabilities()->usesNewCode();
            $vars = [];
            parse_str(parse_url($item->link, PHP_URL_QUERY) ?? '', $vars);
            if (!empty($vars['task'])) {
                switch ($vars['task']) {
                    case 'settings':
                    case 'mappings':
                        if (!$this->isAllowed() || !$usesNewCode) {
                            $remove[] = $key;
                        }
                        break;
                    case 'config':
                    case 'advanced':
                        if (!$this->isAllowed() || $usesNewCode) {
                            $remove[] = $key;
                        }
                        break;
                    case 'register':  // @todo: hide if already registered?
                    case 'activate':
                        if (!$this->isAllowed()) {
                            $remove[] = $key;
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        foreach ($remove as $key) {
            unset($items[$key]);
        }
    }
}
