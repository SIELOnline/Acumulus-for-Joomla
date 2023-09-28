<?php
/**
 * @noinspection PhpStaticAsDynamicMethodCallInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Joomla\HikaShop\Integration;

use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Tests\Joomla\Acumulus_Joomla_TestCase;

use function dirname;

/**
 * InvoiceCreateTest tests the process of creating an {@see Invoice}.
 */
class InvoiceCreateTest extends Acumulus_Joomla_TestCase
{
    public function InvoiceDataProvider(): array
    {
        $dataPath = dirname(__FILE__, 2) . '/Data';
        return [
            'NL billing, FR shipping' => [$dataPath, Source::Order, 34,],
        ];
    }

    /**
     * Tests the Creation process, i.e. collecting and completing an
     * {@see \Siel\Acumulus\Data\Invoice}.
     *
     * @dataProvider InvoiceDataProvider
     * @throws \JsonException
     */
    public function testCreate(string $dataPath, string $type, int $id, array $excludeFields = []): void
    {
        $this->_testCreate($dataPath, $type, $id, $excludeFields);
    }
}
