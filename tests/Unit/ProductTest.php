<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function test_available_when_stock_positive_and_not_heavily_damaged(): void
    {
        $product = new Product(['stock_available' => 5, 'condition' => 'good']);

        $this->assertTrue($product->isAvailable());
    }

    public function test_not_available_when_stock_zero(): void
    {
        $product = new Product(['stock_available' => 0, 'condition' => 'good']);

        $this->assertFalse($product->isAvailable());
    }

    public function test_not_available_when_heavily_damaged_even_with_stock(): void
    {
        $product = new Product(['stock_available' => 10, 'condition' => 'heavily_damaged']);

        $this->assertFalse($product->isAvailable());
    }

    public function test_available_when_lightly_damaged_with_stock(): void
    {
        $product = new Product(['stock_available' => 3, 'condition' => 'lightly_damaged']);

        $this->assertTrue($product->isAvailable());
    }
}
