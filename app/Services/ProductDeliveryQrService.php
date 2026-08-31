<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class ProductDeliveryQrService
{
    const TOKEN_PREFIX = 'SDHOD';
    const WHATSAPP_QR_PREFIX = 'order-whatsapp-qr:';

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

    /**
     * HMAC for the public WhatsApp QR image URL.
     *
     * @param int $orderId
     * @return string
     */
    public static function whatsappQrSignature($orderId)
    {
        return substr(hash_hmac('sha256', self::WHATSAPP_QR_PREFIX . (int) $orderId, (string) config('app.key')), 0, 20);
    }

    /**
     * PNG bytes for a QR payload using the same Bacon encoder as Simple QrCode.
     *
     * @param string $payload
     * @param int $pixelSize
     * @param int $margin
     * @return string
     */
    public static function pngBinary($payload, $pixelSize = 8, $margin = 4)
    {
        $payload = (string) $payload;
        if ($payload === '') {
            throw new \RuntimeException('QR payload is empty.');
        }

        if (!function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('GD library is required to generate QR code PNG images.');
        }

        $qrCode = Encoder::encode($payload, ErrorCorrectionLevel::M());
        $matrix = $qrCode->getMatrix();
        $moduleCount = $matrix->getWidth();
        $size = ($moduleCount + (2 * $margin)) * $pixelSize;

        $image = imagecreatetruecolor($size, $size);
        if ($image === false) {
            throw new \RuntimeException('Unable to allocate QR code image.');
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);

        for ($y = 0; $y < $moduleCount; $y++) {
            for ($x = 0; $x < $moduleCount; $x++) {
                if ($matrix->get($x, $y) !== 1) {
                    continue;
                }

                imagefilledrectangle(
                    $image,
                    ($x + $margin) * $pixelSize,
                    ($y + $margin) * $pixelSize,
                    (($x + $margin + 1) * $pixelSize) - 1,
                    (($y + $margin + 1) * $pixelSize) - 1,
                    $black
                );
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        if ($png === false || $png === '') {
            throw new \RuntimeException('Unable to encode QR code PNG.');
        }

        return $png;
    }
}
