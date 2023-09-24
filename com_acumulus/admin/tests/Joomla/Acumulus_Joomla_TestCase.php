<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Joomla;

use AcumulusTestsBootstrap;
use PHPUnit\Framework\TestCase;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Tests\AcumulusTestUtils;

/**
 * Acumulus_Joomla_TestCase contains Joomla specific test functionalities.
 */
class Acumulus_Joomla_TestCase extends TestCase
{
    use AcumulusTestUtils;

    protected static function getAcumulusContainer(): Container
    {
        return AcumulusTestsBootstrap::instance()->getModel()->getAcumulusContainer();
    }
}
