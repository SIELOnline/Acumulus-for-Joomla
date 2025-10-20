<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Joomla\HikaShop\Invoice;

use Siel\Acumulus\Joomla\HikaShop\Invoice\Item;
use Siel\Acumulus\Joomla\HikaShop\Invoice\Source;
use Siel\Acumulus\Joomla\HikaShop\Product\Product;
use Siel\Acumulus\Tests\Joomla\Acumulus_Joomla_TestCase;

/**
 * ItemTest tests the HikaShop source => items => products chain.
 */
class ItemTest extends Acumulus_Joomla_TestCase
{
    public function testGetProduct(): void
    {
        $invoiceSource = self::getContainer()->createSource(Source::Order, 32);
        static::assertInstanceOf(Source::class, $invoiceSource);
        $items = $invoiceSource->getItems();
        static::assertCount(2, $items);
        $item = reset($items);
        static::assertInstanceOf(Item::class, $item);
        $product = $item->getProduct();
        static::assertInstanceOf(Product::class, $product);
        $shopProduct = $product->getShopObject();
        static::assertSame('Orange sneakers', $shopProduct->product_name);
    }
}
