<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Joomla\HikaShop;

use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Tests\Joomla\TestCase;

/**
 * InvoiceCreateTest tests the process of creating an {@see Invoice}.
 *
 * @todo: add a margin scheme invoice.
 */
class InvoiceCreateTest extends TestCase
{
    public function InvoiceDataProvider(): array
    {
        return [
            'FR billing (consumer), FR shipping (consumer)' => [Source::Order, 32],
            'NL billing (consumer), FR shipping (company)' => [Source::Order, 34],
            'FR billing (consumer), NL shipping (consumer), discount' => [Source::Order, 35],
            'FR billing (consumer), FR shipping (consumer), discount' => [Source::Order, 36],
        ];
    }

    /**
     * Tests the Creation process, i.e. collecting and completing an
     * {@see \Siel\Acumulus\Data\Invoice}.
     *
     * @dataProvider InvoiceDataProvider
     * @throws \JsonException
     */
    public function testCreate(string $type, int $id, array $excludeFields = []): void
    {
        $this->_testCreate($type, $id, $excludeFields);
    }
}
