<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductDeliveryScanAuthorizationTest extends TestCase
{
    public function test_guest_cannot_mark_delivery_via_scan_endpoint()
    {
        Mail::fake();

        $response = $this->postJson('/admin/product/scan-delivery', array(
            'qr_code' => 'SDHOD-1-aaaaaaaaaaaaaaaaaaaa',
        ));

        $this->assertTrue(in_array($response->status(), array(401, 403, 404, 302), true));
        $this->assertNotEquals(200, $response->status());
        Mail::assertNothingQueued();
    }
}
