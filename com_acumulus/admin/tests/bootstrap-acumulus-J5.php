<?php
/**
 * Bootstrap file for our integration tests that need a running Joomla + shop instance.
 *
 * Eventually, we should move to the MVCFactory, but that probably only works in J4.
 * Restricting our tests just to J4 is not a problem, but we probably have to restructure
 * "all" our MVC classes (rename, use namespaces, ...) and that would make our component
 * run only on J4, and it is too early for that (sep. 2023) (or we could make 2 versions)
 *
 * Notes
 * - If we run into other problems with setting up a working instance, have a look at
 *   https://github.com/joomla/test-integration/blob/master/bootstrap.php, to see how it
 *   is done there.
 * - We got a "Class 'Joomla\Component\Templates\Administrator\Extension\TemplatesComponent'
 *   not found" error on HS4 + J4.2. The page on
 *   https://joomla.stackexchange.com/questions/32688/4-1-4-2-attempted-to-load-class-templatecomponent-from-namespace-joomla-com
 *   contained the solution: explicitly register that namespace.
 * - Getting the language of the country name right requires to set the language in the
 *   Config. To get it right in our component requires to set it in the Application.
 *
 * @noinspection PhpIllegalPsrClassPathInspection  Bootstrap file is loaded directly.
 * @noinspection DuplicatedCode  A duplicate for J4 exists.
 * @noinspection PhpMultipleClassDeclarationsInspection  A duplicate for J4 exists.
 */

declare(strict_types=1);

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used rather than "const" to not error for PHP 5.2 and lower
 */
const _JEXEC = 1;

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used rather than "const" to not error for PHP 5.2 and lower
 */

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;

/**
 * Class AcumulusTestsBootstrap bootstraps the Acumulus tests.
 *
 * This class is based on how the administrator backend is initialized:
 * - administrator/index.php
 * - administrator/includes/app.php
 *
 * .php,
 * which it includes as last action.
 */
class AcumulusTestsBootstrap
{
    private static AcumulusTestsBootstrap $instance;
    private AcumulusModelAcumulus $model;

    /**
     * Returns the single class instance, creating one if not yet existing.
     */
    public static function instance(): AcumulusTestsBootstrap
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Setup the unit testing environment.
     */
    private function __construct()
    {
    }

    public function getModel(): AcumulusModelAcumulus
    {
        return $this->model;
    }

    private function getAdministratorPath(): string
    {
        // Set the admin path, unaware that our plugin may be symlinked.
        $administratorPath = dirname(__DIR__, 2);

        // if our component is symlinked, we need to redefine the administratorPath. Try to
        // find it by looking at the --bootstrap option as passed to phpunit.
        global $argv;
        if (is_array($argv)) {
            $i = array_search('--bootstrap', $argv, true);
            // if we found --bootstrap, the value is in the next entry.
            if ($i < count($argv) - 1) {
                $bootstrapFile = $argv[$i + 1];
                $administratorPath = substr($bootstrapFile, 0, strpos($bootstrapFile, 'administrator')) . 'administrator';
            }
        }
        return $administratorPath;
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        // HikaShop assumes we are in a web request and utilises {@see Joomla\CMS\Uri\Uri}.
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = 'localhost';
        }
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            $_SERVER['SCRIPT_NAME'] = '/administrator/index.php';
        }
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = 'Firefox';
        }

        $administratorPath = $this->getAdministratorPath();
        // Load and execute app.php
        if (!defined('JPATH_BASE')) {
            define('JPATH_BASE', $administratorPath);
        }
        include_once __DIR__ . '/app.php';
        $container = (new AppLoader())->execute($administratorPath);

        // Ensure we (and other code) use the nl-NL language.
        /** @var Joomla\Registry\Registry $config */
        $config = $container->get('config');
        /** @var LanguageFactoryInterface $languageFactory */
        $languageFactory = $container->get(LanguageFactoryInterface::class);
        $locale = 'nl-NL';
        $config->set('language', $locale);
        $language = $languageFactory->createLanguage($locale);
        /** @var AdministratorApplication $app */
        $app = $container->get(AdministratorApplication::class);
        $app->loadLanguage($language);

        // This line prevents a "Class 'Joomla\Component\Templates\Administrator\Extension\TemplatesComponent'
        // not found" error on HS4 + J4.2
        JLoader::registerNamespace('Joomla\Component\Templates\Administrator', JPATH_ADMINISTRATOR . '/components/com_templates/src');

        $this->loadCompatBehaviour();
        $this->loadAcumulus();
    }

    /**
     * Load Acumulus
     *
     * @throws \Exception
     */
    private function loadAcumulus(): void
    {
        // Could not get our model to autoload.
        $administratorPath = $this->getAdministratorPath();
        require_once $administratorPath . '/components/com_acumulus/models/acumulus.php';
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->model = Factory::getContainer()->buildObject('AcumulusModelAcumulus');
    }

    private function loadCompatBehaviour(): void
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $aliases = [
            'JObject' => 'Joomla\CMS\Object\CMSObject',
            'JTable' => 'Joomla\CMS\Table\Table',
            'JComponentHelper' => 'Joomla\CMS\Component\ComponentHelper',
            'JApplicationHelper' => 'Joomla\CMS\Application\ApplicationHelper',
            'JTableContenttype' => 'Joomla\CMS\Table\ContentType',
            'JFactory' => 'Joomla\CMS\Factory',
            'JInstaller' => 'Joomla\CMS\Installer\Installer',
            'JControllerLegacy' => 'Joomla\CMS\MVC\Controller\BaseController',
            'JViewLegacy' => 'Joomla\CMS\MVC\View\HtmlView',
            'JRoute' => 'Joomla\CMS\Router\Route',
            'JURI' => 'Joomla\CMS\Uri\Uri',
            'JPlugin' => 'Joomla\CMS\Plugin\CMSPlugin',
            'JPluginHelper' => 'Joomla\CMS\Plugin\PluginHelper',
            'JModuleHelper' => 'Joomla\CMS\Helper\ModuleHelper',
            'JRegistry' => 'Joomla\Registry\Registry',
            'JFilterInput' => 'Joomla\CMS\Filter\InputFilter',
            'JFilterOutput' => 'Joomla\CMS\Filter\OutputFilter',
            'JLanguage' => 'Joomla\CMS\Language\Language',
            'JLanguageHelper' => 'Joomla\CMS\Language\LanguageHelper',
            'JLanguageAssociations' => 'Joomla\CMS\Language\Associations',
            'JText' => 'Joomla\CMS\Language\Text',
            'JFile' => 'Joomla\CMS\Filesystem\File',
            'JFolder' => 'Joomla\CMS\Filesystem\Folder',
            'JPath' => 'Joomla\CMS\Filesystem\Path',
            'JMailHelper' => 'Joomla\CMS\Mail\MailHelper',
            'JUserHelper' => 'Joomla\CMS\User\UserHelper',
            'JUser' => 'Joomla\CMS\User\User',
            'JAccess' => 'Joomla\CMS\Access\Access',
            'JSession' => 'Joomla\CMS\Session\Session',
            'JButton' => 'Joomla\CMS\Toolbar\ToolbarButton',
            'JToolbarHelper' => 'Joomla\CMS\Toolbar\ToolbarHelper',
            'JToolbar' => 'Joomla\CMS\Toolbar\Toolbar',
            'JPagination' => 'Joomla\CMS\Pagination\Pagination',
            'JLayoutFile' => 'Joomla\CMS\Layout\FileLayout',
            'JHTML' => 'Joomla\CMS\HTML\HTMLHelper',
            'JHTMLSelect' => 'Joomla\CMS\HTML\Helpers\Select',
            'JEditor' => 'Joomla\CMS\Editor\Editor',
            'JForm' => 'Joomla\CMS\Form\Form',
            'JFormHelper' => 'Joomla\CMS\Form\FormHelper',
            'JFormField' => 'Joomla\CMS\Form\FormField',
            'JUtility' => 'Joomla\CMS\Utility\Utility',
            'JCache' => 'Joomla\CMS\Cache\Cache',
        ];
        foreach ($aliases as $alias => $original) {
            if (!class_exists($alias)) {
                class_alias($original, $alias);
            }
        }
    }
}

/** @noinspection PhpUnhandledExceptionInspection */
AcumulusTestsBootstrap::instance()->execute();
