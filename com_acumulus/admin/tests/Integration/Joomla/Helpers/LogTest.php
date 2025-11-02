<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Joomla\Helpers;

use Joomla\CMS\Factory;
use Siel\Acumulus\Joomla\Helpers\Log;
use Siel\Acumulus\Tests\Joomla\TestCase;

/**
 * LogTest tests whether the log class logs messages to a log file.
 *
 * This test is mainly used to test if the log feature still works in new versions of the
 * shop.
 */
class LogTest extends TestCase
{
    private function getLogFolder(): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return Factory::getApplication()->get('log_path', JPATH_ADMINISTRATOR . '/logs');
    }

    protected function getLogPath(): string
    {
        return $this->getLogFolder() . '/' . Log::LogFile;
    }

    public function testLog(): void
    {
        $this->_testLog();
    }
}
