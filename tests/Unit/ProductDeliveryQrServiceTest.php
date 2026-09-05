<?php

namespace Tests\Unit;

use App\Services\ProductDeliveryQrService;
use Tests\TestCase;

class ProductDeliveryQrServiceTest extends TestCase
{
    public function test_token_identifies_order_detail_not_catalog_product()
    {
        $tokenA = ProductDeliveryQrService::tokenForOrderDetail(10);
        $tokenB = ProductDeliveryQrService::tokenForOrderDetail(11);

        $this->assertStringStartsWith('SDHOD-10-', $tokenA);
        $this->assertStringStartsWith('SDHOD-11-', $tokenB);
        $this->assertNotEquals($tokenA, $tokenB);
        $this->assertDoesNotMatchRegularExpression('/product/i', $tokenA);
    }

    public function test_tampered_token_does_not_resolve()
    {
        $token = ProductDeliveryQrService::tokenForOrderDetail(5);
        $tampered = preg_replace('/[a-f0-9]{4}$/', 'ffff', $token);

        $this->assertNull(ProductDeliveryQrService::resolve($tampered));
        $this->assertNull(ProductDeliveryQrService::resolve(''));
        $this->assertNull(ProductDeliveryQrService::resolve('not-a-qr'));
    }

    public function test_png_binary_is_a_png_image()
    {
        $token = ProductDeliveryQrService::tokenForOrderDetail(42);
        $png = ProductDeliveryQrService::pngBinary($token, 180);

        $this->assertNotNull($png);
        $this->assertSame("\x89PNG", substr($png, 0, 4));
        $this->assertGreaterThan(100, strlen($png));
    }
}
