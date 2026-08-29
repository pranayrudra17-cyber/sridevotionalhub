<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;

class ProductDeliveryQrService
{
    const TOKEN_PREFIX = 'SDHOD';

    /**
     * Signed identifier for a specific ordered product (order_details row).
     *
     * @param int $orderDetailId
     * @return string
     */
    public static function tokenForOrderDetail($orderDetailId)
    {
        return self::TOKEN_PREFIX . '-' . (int) $orderDetailId . '-' . self::signature((int) $orderDetailId);
    }

    /**
     * @param int $orderDetailId
     * @return string
     */
    protected static function signature($orderDetailId)
    {
        return substr(hash_hmac('sha256', 'order_detail:' . $orderDetailId, (string) config('app.key')), 0, 20);
    }

    /**
     * Resolve scanned QR data to an order and optional single order detail.
     * Existing invoice QRs contain orders.code. Item QRs use a signed token.
     *
     * @param string $qrCode
     * @return array|null
     */
    public static function resolve($qrCode)
    {
        $qrCode = trim(strip_tags((string) $qrCode));
        if ($qrCode === '') {
            return null;
        }

        if (preg_match('/^' . self::TOKEN_PREFIX . '-(\d+)-([a-f0-9]{20})$/i', $qrCode, $matches)) {
            $orderDetailId = (int) $matches[1];
            if (!hash_equals(self::signature($orderDetailId), strtolower($matches[2]))) {
                return null;
            }

            $orderDetail = OrderDetail::find($orderDetailId);
            if ($orderDetail === null || $orderDetail->order === null) {
                return null;
            }

            return array(
                'type' => 'order_detail',
                'order' => $orderDetail->order,
                'order_detail' => $orderDetail,
            );
        }

        $order = Order::where('code', $qrCode)->first();
        if ($order === null) {
            return null;
        }

        return array(
            'type' => 'order',
            'order' => $order,
            'order_detail' => null,
        );
    }
}
