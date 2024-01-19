<?php
/**
 * @noinspection PhpStaticAsDynamicMethodCallInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Joomla\Unit;

use Siel\Acumulus\Tests\Joomla\Acumulus_Joomla_TestCase;

/**
 * Tests that WooCommerce and Acumulus have been initialized.
 */
class InitTest extends Acumulus_Joomla_TestCase
{
    /**
     * A single test to see if the test framework (including the plugins) has been
     * initialized correctly:
     * 1 We have access to the Container.
     * 2 Joomla and a shop have been initialized.
     */
    public function testInit(): void
    {
        // 1.
        $container = self::getAcumulusContainer();
        $environmentInfo = $container->getEnvironment()->get();
        // 2.
        $this->assertMatchesRegularExpression('|\d+\.\d+\.\d+|', $environmentInfo['shopVersion']);
    }
}
