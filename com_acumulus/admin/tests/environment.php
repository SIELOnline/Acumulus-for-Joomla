<?php
/**
 * @noinspection UntrustedInclusionInspection
 */

declare(strict_types=1);

/**
 * File that sets environment/global variables for our PHPUnit tests.
 */

const _JEXEC = 1;

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

// HikaShop assumes we are in a web request and utilises {@see Joomla\CMS\Uri\Uri}.
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}
if (!isset($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = '/administrator/index.php';
}
