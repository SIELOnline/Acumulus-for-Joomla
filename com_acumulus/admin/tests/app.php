<?php
/**
 * @noinspection PhpMissingStrictTypesDeclarationInspection
 * @noinspection DuplicatedCode Proudly copied (and slightly adapted) from
 *   /administrator/ includes/app.php.
 */

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\DI\Container;

\defined('_JEXEC') or die;

/**
 * AppLoader encapsulates the code we copied from app.php to start Joomla.
 */
class AppLoader
{
    /**
     * @noinspection UntrustedInclusionInspection
     */
    public function execute(string $administratorPath): Container
    {
        if (file_exists("$administratorPath/defines.php")) {
            require_once "$administratorPath/defines.php";
        }

        require_once "$administratorPath/includes/defines.php";

        // Check for presence of vendor dependencies not included in the git repository
        if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_PUBLIC . '/media/vendor')) {
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
            ->alias(\Joomla\Session\Session::class, 'session.web.administrator')
            ->alias(\Joomla\Session\SessionInterface::class, 'session.web.administrator');

        // Instantiate the application.
        $app = $container->get(AdministratorApplication::class);

        // Set the application as global app
        /** @noinspection DisallowWritingIntoStaticPropertiesInspection */
        Factory::$application = $app;

        //$app->execute();
        $this->executeApp($app);

//        $app->getSession()->get('user');

        return $container;
    }

    /**
     * Code copied and adapted from {@see Joomla\CMS\Application::execute()} and
     * {@see AdministratorApplication::execute()}.
     */
    private function executeApp(CMSApplication $app): void
    {
        $app->createExtensionNamespaceMap();

        // Get the language from the user state.
        $app->getUserState('application.lang');
    }
}
