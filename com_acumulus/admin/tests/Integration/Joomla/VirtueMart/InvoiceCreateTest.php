<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Joomla\VirtueMart;

use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Tests\Joomla\Acumulus_Joomla_TestCase;

/**
 * InvoiceCreateTest tests the process of creating an {@see Invoice}.
 *
 * @todo: add a margin scheme invoice.
 */
class InvoiceCreateTest extends Acumulus_Joomla_TestCase
{
    public function InvoiceDataProvider(): array
    {
        $dataPath = __DIR__ . '/Data';
        return [
            'FR company without VAT number, 0% and vat free items' => [$dataPath, Source::Order, 24],
            'FR company without VAT number' => [$dataPath, Source::Order, 25],
            'FR company without VAT number, discount' => [$dataPath, Source::Order, 27],
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
