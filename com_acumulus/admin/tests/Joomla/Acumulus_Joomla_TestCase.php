<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection AcumulusTestsBootstrap
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Joomla;

use Joomla\CMS\Factory;
use PHPUnit\Framework\TestCase;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Tests\AcumulusTestUtils;
use Siel\Joomla\Component\Acumulus\Administrator\Extension\AcumulusComponent;

/**
 * Acumulus_Joomla_TestCase contains Joomla specific test functionalities.
 */
class Acumulus_Joomla_TestCase extends TestCase
{
    use AcumulusTestUtils;

    protected static function getAcumulusComponent(): AcumulusComponent
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Factory::getApplication()->bootComponent('acumulus');
    }

    protected static function getAcumulusContainer(): Container
    {
        return static::getAcumulusComponent()->getAcumulusModel()->getAcumulusContainer();
    }
}
