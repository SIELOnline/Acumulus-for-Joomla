<?php

declare(strict_types=1);

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Acumulus plugin to hide menu-items, from the backend Components menu, based on state.
 *
 * @noinspection PhpUnused  Plugins are instantiated dynamically.
 */
class plgSystemAcumulus extends CMSPlugin
{
    protected bool $initialized;
    protected AcumulusModelAcumulus $model;
    /**
     * @var \Joomla\CMS\Application\CMSApplicationInterface|\CMSApplicationInterface|null
     *
     * @noinspection ClassOverridesFieldOfSuperClassInspection J4: CMSPlugin already
     *    defines this property and assigns it in the constructor. So, in J4, No more need
     *    to define it here, nor assign it in the getter.
     */
    protected $app;

    protected function getApp(): CMSApplicationInterface
    {
        if (!isset($this->app)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->app = Factory::getApplication();
        }
        return $this->app;
    }

    /**
     * Initializes the environment for the plugin:
     * - Register autoloader for our own library.
     */
    protected function init(): void
    {
        if (!$this->initialized) {
            $componentPath = JPATH_ADMINISTRATOR . '/components/com_acumulus';
            // Get access to our models and tables.
            /**
             * @noinspection PhpDeprecationInspection : I think, eventually, we
             *   should replace legacy models with J4 PSR4 models.
             */
            BaseDatabaseModel::addIncludePath("$componentPath/models", 'AcumulusModel');
            /**
             * @noinspection PhpDeprecationInspection : I think, eventually, we
             *   should replace legacy table classes with J4 PSR4 table classes.
             */
            Table::addIncludePath("$componentPath/tables");
            $this->initialized = true;
        }
    }

    /**
     * Returns an Acumulus model.
     *
     * @return AcumulusModelAcumulus
     */
    protected function getModel(): AcumulusModelAcumulus
    {
        if (!isset($this->model)) {
            /**
             * @noinspection PhpDeprecationInspection : Get the model through
             *   the MVCFactory instead.
             * @noinspection PhpFieldAssignmentTypeMismatchInspection
             */
            $this->model = BaseDatabaseModel::getInstance('Acumulus', 'AcumulusModel');
        }
        return $this->model;
    }

    public function isAllowed(string $action = 'core.admin', string $assetName = 'com_acumulus'): bool
    {
        return $this->getApp()->getIdentity()->authorise($action, $assetName);
    }

    /**
     * @noinspection PhpUnused  event handler: not called directly.
     */
    public function onPreprocessMenuItems(string $name, array $items = []/*, ?array $params = null, bool $enabled = true*/): void
    {
        if ($name !== 'com_menus.administrator.module') {
            return;
        }
        $this->init();

        $remove = [];
        foreach ($items as $key => $item) {
            $usesNewCode = $this->getModel()->getAcumulusContainer()->getShopCapabilities()->usesNewCode();
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
