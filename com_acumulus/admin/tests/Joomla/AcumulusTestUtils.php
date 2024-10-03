<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Joomla;

use Joomla\CMS\Factory;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Tests\AcumulusTestUtils as BaseAcumulusTestUtils;
use Siel\Joomla\Component\Acumulus\Administrator\Extension\AcumulusComponent;

use function dirname;

/**
 * AcumulusTestUtils contains Joomla specific test functionalities.
 */
trait AcumulusTestUtils
{
    use BaseAcumulusTestUtils {
        copyLatestTestSources as protected parentCopyLatestTestSources;
    }

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

    protected function getTestsPath(): string
    {
        return dirname(__FILE__, 2);
    }

    public function copyLatestTestSources(): void
    {
        static $hasRun = false;

        if (!$hasRun) {
            $hasRun = true;
            require_once dirname(__FILE__, 2) . '/bootstrap-acumulus.php';
        }
        $this->parentCopyLatestTestSources();
    }
}
