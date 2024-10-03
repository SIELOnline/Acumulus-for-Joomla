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
 * @noinspection PhpUnused (for JEXEC)
 */

declare(strict_types=1);

const _JEXEC = 1;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\DI\Container as JoomlaContainer;

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

    private function getAdministratorPath(): string
    {
        // Set the admin path, unaware that our plugin may be symlinked.
        $administratorPath = dirname(__DIR__, 2);

        // if our component is symlinked, we need to redefine the administratorPath. Try to
        // find it by looking at the --bootstrap option as passed to phpunit or at the
        // script name passed to PHP CLI
        global $argv;
        if (is_array($argv)) {
            $i = array_search('--bootstrap', $argv, true);
            // if we found --bootstrap, the value is in the next entry.
            if (is_int($i) && $i < count($argv) - 1) {
                $bootstrapFile = $argv[$i + 1];
            } elseif (count($argv) === 1 && str_contains($argv[0], 'administrator')) {
                $bootstrapFile = $argv[0];
            }
            if (isset($bootstrapFile)) {
                $administratorPath = substr($bootstrapFile, 0, strpos($bootstrapFile, 'administrator') + strlen('administrator'));
            }
        }
        return $administratorPath;
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        $container = $this->loadMinimal();

        // Ensure we (and other code) use the nl-NL language.
        /** @var LanguageFactoryInterface $languageFactory */
        $languageFactory = $container->get(LanguageFactoryInterface::class);
        $locale = 'nl-NL';
        $this->getApplication()->set('language', $locale);
        $language = $languageFactory->createLanguage($locale);
        /** @var AdministratorApplication $app */
        $app = $container->get(AdministratorApplication::class);
        $app->loadLanguage($language);

        // This line prevents a "Class 'Joomla\Component\Templates\Administrator\Extension\TemplatesComponent'
        // not found" error on HS5/4 + J5/4.2
        // @todo: try to prevent the error by loading/booting the component.
        JLoader::registerNamespace('Joomla\Component\Templates\Administrator', JPATH_ADMINISTRATOR . '/components/com_templates/src');

        $this->loadCompatBehaviour();
        $this->loadAcumulus();
    }

    /**
     * @throws \Exception
     */
    public function loadMinimal(): JoomlaContainer
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/administrator/index.php';
        $_SERVER['HTTP_USER_AGENT'] = 'Firefox';

        $administratorPath = $this->getAdministratorPath();
        // Load and execute app.php
        if (!defined('JPATH_BASE')) {
            define('JPATH_BASE', $administratorPath);
        }
        include_once __DIR__ . '/app.php';
        return (new AppLoader())->execute($administratorPath);
    }

    /**
     * Load Acumulus
     *
     * @throws \Exception
     */
    private function loadAcumulus(): void
    {
        $this->getApplication()->bootComponent('acumulus');
    }

    private function loadCompatBehaviour(): void
    {
        Joomla\CMS\Plugin\PluginHelper::importPlugin('behaviour', 'compat');
    }

    private function getApplication(): CMSApplicationInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return Factory::getApplication();
    }
}

/** @noinspection PhpUnhandledExceptionInspection */
AcumulusTestsBootstrap::instance()->execute();
