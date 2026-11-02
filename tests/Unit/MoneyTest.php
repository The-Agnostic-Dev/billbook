<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_formats_naira_with_two_decimals(): void
    {
        $this->assertSame('₦1,234,567.50', Money::format(1234567.5));
        $this->assertSame('₦0.00', Money::format(null));
        $this->assertSame('-₦500.00', Money::format(-500));
    }

    public function test_it_parses_plain_amounts(): void
    {
        $this->assertSame(750.5, Money::parse('750.50'));
        $this->assertSame(5000.0, Money::parse('₦5000'));
        $this->assertSame(1200.0, Money::parse('NGN 1200'));
    }

    public function test_blank_input_is_null(): void
    {
        $this->assertNull(Money::parse(''));
        $this->assertNull(Money::parse(null));
    }
}
