<?php
/**
 * @noinspection UntrustedInclusionInspection
 */

declare(strict_types=1);

/**
 * File that sets environment variables for our PHPUnit tests.
 * - WP_TESTS_INSTALLATION={path to WP installation};
 *   (We can use different WP core installs, e.g. to test different versions,
 *   for now we stick to the version our plugin is installed into).
 * - WP_TESTS_CONFIG_FILE_PATH={path and name to wp-tests-config.php}
 *   (includes/bootstrap.php expects this as a constant).
 * - WP_TESTS_DIR={path to the data and includes folders from the WordPress test framework}
 * - WP_TESTS_SKIP_INSTALL=1; {1 = skip install, 0 = reinstall tables}
 */

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
