<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Joomla\HikaShop;

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
            'FR billing (consumer), FR shipping (consumer)' => [$dataPath, Source::Order, 32],
            'NL billing (consumer), FR shipping (company)' => [$dataPath, Source::Order, 34],
            'FR billing (consumer), NL shipping (consumer), discount' => [$dataPath, Source::Order, 35],
            'FR billing (consumer), FR shipping (consumer), discount' => [$dataPath, Source::Order, 36],
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
