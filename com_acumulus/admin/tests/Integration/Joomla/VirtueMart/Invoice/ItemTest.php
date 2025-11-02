<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Joomla\VirtueMart\Invoice;

use Siel\Acumulus\Joomla\VirtueMart\Invoice\Item;
use Siel\Acumulus\Joomla\VirtueMart\Invoice\Source;
use Siel\Acumulus\Joomla\VirtueMart\Product\Product;
use Siel\Acumulus\Tests\Joomla\TestCase;

/**
 * ItemTest tests the VirtueMart source => items => products chain.
 */
class ItemTest extends TestCase
{
    public function testGetProduct(): void
    {
        $invoiceSource = self::getContainer()->createSource(Source::Order, 24);
        static::assertInstanceOf(Source::class, $invoiceSource);
        $items = $invoiceSource->getItems();
        static::assertCount(3, $items);
        $item = reset($items);
        static::assertInstanceOf(Item::class, $item);
        $product = $item->getProduct();
        static::assertInstanceOf(Product::class, $product);
        $shopProduct = $product->getShopObject();
        static::assertSame('Marine Cap', $shopProduct->product_name);
    }
}
