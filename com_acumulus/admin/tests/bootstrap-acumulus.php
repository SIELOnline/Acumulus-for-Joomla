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
 *   {@link https://github.com/joomla/test-integration/blob/master/bootstrap.php}, to see
 *   how it is done there.
 * - With HikaShop we run into an error because the ConsoleApplication does not offer
 *   a getDocument() method. Replace line 1638 of
 *   administrator/components/com_hikashop/helpers/helper.php with:
 *      try {
 *          $document = $app->getDocument();
 *      } catch (Throwable $e) {
 *          $document = null;
 *      }
 *
 * @noinspection PhpIllegalPsrClassPathInspection  Bootstrap file is loaded directly.
 */

declare(strict_types=1);

const _JEXEC = 1;

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used rather than "const" to not error for PHP 5.2 and lower
 */

use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\Session\Session;
use Joomla\Session\SessionInterface;

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
    private LegacyFactory $factory;
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

        // From app.php
        $administratorPath = $this->getAdministratorPath();

        if (!defined('JPATH_BASE')) {
            define('JPATH_BASE', $administratorPath);
        }
        require_once $administratorPath . '/includes/defines.php';

        // Check for presence of vendor dependencies not included in the git repository
        if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_ROOT . '/media/vendor')) {
            echo file_get_contents(JPATH_ROOT . '/templates/system/build_incomplete.html');
            exit;
        }

        require_once JPATH_BASE . '/includes/framework.php';

        // Boot the DI container
        $container = Factory::getContainer();

        /*
         * Alias the session service keys to the web session service as that is the primary session backend for this application
         *
         * In addition to aliasing "common" service keys, we also create aliases for the PHP classes to ensure autowiring objects
         * is supported.  This includes aliases for aliased class names, and the keys for aliased class names should be considered
         * deprecated to be removed when the class name alias is removed as well.
         */
        $container->alias('session.web', 'session.web.administrator')
            ->alias('session', 'session.web.administrator')
            ->alias('JSession', 'session.web.administrator')
            ->alias(\Joomla\CMS\Session\Session::class, 'session.web.administrator')
            ->alias(Session::class, 'session.web.administrator')
            ->alias(SessionInterface::class, 'session.web.administrator');

        // Instantiate the application: assure we use the nl-NL language.
        /** @var Joomla\Registry\Registry $config */
        $config = $container->get('config');
        $config->set('language', 'nl-NL');
        /** @var ConsoleApplication $app */
        $app = $container->get(ConsoleApplication::class);

        // We have to change the name property as hikashop bases its logic in
        // {@see \hikashopOrderClass::loadFullOrder()} on it, more specifically: loading
        // the history is only done in the administrator app.
        // (Code from PHP manual: ReflectionProperty::setValue())
        $reflectionClass = new ReflectionClass($app);
        $reflectionProperty = $reflectionClass->getProperty('name');
        $reflectionProperty->setAccessible(true); // only required prior to PHP 8.1.0
        $reflectionProperty->setValue($app, 'administrator');

        // Set the application as global app
        /** @noinspection DisallowWritingIntoStaticPropertiesInspection */
        Factory::$application = $app;

        // Eventually, we should move to the MVCFactory, but that probably only works in
        // J4. Restricting our tests just to J4 is not a problem, but we probably have to
        // restructure "all" our MVC classes (rename, use namespaces, ...) and that would
        // make our component run only on J4, and it is too early for that (sep. 2023).
        $this->factory = new LegacyFactory();

        // Start the session before the ConsoleApp starts outputting "headers" and we get
        // an error "Failed to start the session because headers have already been sent
        // by ..." from {@see// Joomla\Session\Storage\NativeStorage::start()}
        $app->getSession()->get('user');

        $this->load_acumulus();
    }

    /**
     * Load Acumulus
     *
     * @throws \Exception
     */
    public function load_acumulus(): void
    {
        // Could not get our model to autoload.
        $administratorPath = $this->getAdministratorPath();
        require_once $administratorPath . '/components/com_acumulus/models/acumulus.php';
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->model = $this->factory->createModel('Acumulus', 'AcumulusModel', ['name' => 'Acumulus']);
    }
}

/** @noinspection PhpUnhandledExceptionInspection */
AcumulusTestsBootstrap::instance()->execute();
