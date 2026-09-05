<?php

namespace Tests\Unit;

use App\Models\Order;
use Tests\TestCase;

class OrderDeliveryPincodeFilterTest extends TestCase
{
    public function test_parse_single_pincode()
    {
        $this->assertSame(array('500072'), Order::parseDeliveryPincodes('500072'));
    }

    public function test_parse_comma_separated_pincodes_and_trim_spaces()
    {
        $this->assertSame(
            array('500072', '500073', '500074'),
            Order::parseDeliveryPincodes('500072, 500073,500074')
        );
    }

    public function test_parse_ignores_empty_values_and_duplicates()
    {
        $this->assertSame(
            array('110001', '500072'),
            Order::parseDeliveryPincodes('110001,, 500072, 110001, ')
        );
    }

    public function test_parse_allows_international_zipcodes()
    {
        $this->assertSame(
            array('SW1A 1AA', 'K1A-0B1'),
            Order::parseDeliveryPincodes('SW1A 1AA, K1A-0B1')
        );
    }

    public function test_parse_rejects_invalid_tokens()
    {
        $this->assertSame(
            array('500072'),
            Order::parseDeliveryPincodes('500072, <script>, "drop"')
        );
        $this->assertSame(array(), Order::parseDeliveryPincodes('   '));
        $this->assertSame(array(), Order::parseDeliveryPincodes(null));
    }
}
